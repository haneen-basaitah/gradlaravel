<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RefillReminderMail;
use App\Services\MqttClientService;
use App\Jobs\MedicationSystemJob;

class MedicationSubscriptionController extends Controller
{
    public function subscribeToMissedDoses()
    {
        $mqtt = new MqttClientService();
        $mqtt->connect();

        if ($mqtt->isConnected()) {
            Log::info("📡 الاشتراك في `medication/missed`");

            $mqtt->subscribe("medication/missed", function ($receivedTopic, $message) {
                Log::info("📩 رسالة مستقبلة من MQTT: $message");
                $data = json_decode($message, true);

                if (isset($data["status"], $data["closet_id"], $data["cell_id"])) {
                    $status = $data["status"];
                    $closetId = $data["closet_id"];
                    $cellId = $data["cell_id"];

                    Log::info("✅ حالة الجرعة المستلمة: $status | 🏠 رقم الخزانة: $closetId | 📦 رقم الخلية: $cellId");

                    // ✅ تحديث حالة الدواء في قاعدة البيانات
                    $this->updateMedicationCount($closetId, $cellId, $status);

                    // ✅ البحث عن الجرعة التالية وجدولتها في وقتها
                    Log::info("🔄 سيتم البحث عن الجرعة التالية وتشغيل `runMedicationSystem()` عند موعدها.");
                    app(\App\Http\Controllers\MedicationController::class)->runMedicationSystem();
                }
            });
            $mqtt->loop(30);
        }
    }

    private function updateMedicationCount($closetId, $cellId, $status)
    {
        // 🔍 البحث عن جميع الأدوية بنفس closet_number و medicine_closet_location ونفس المريض
        $medications = Medication::where('medicine_closet_location', $closetId)
                                ->where('medicine_closet_number', $cellId)
                                ->get();

        if ($medications->isNotEmpty()) {
            // ✅ جلب المريض المرتبط بالدواء
            $patientId = $medications->first()->patient_id;

            // 🔍 البحث عن جميع الجرعات التي تخص نفس المريض ونفس الدواء والخزانة والجرار
            $relatedMedications = Medication::where('patient_id', $patientId)
                                            ->where('medicine_closet_location', $closetId)
                                            ->where('medicine_closet_number', $cellId)
                                            ->get();

            foreach ($relatedMedications as $medication) {
                if ($status === "taken") {
                    if ($medication->pill_count > 0) {
                        $medication->pill_count -= 1;
                    } else {
                        Log::warning("⚠️ لا يمكن تقليل عدد الحبوب لأن العدد بالفعل صفر!");
                    }
                }

                // ✅ تحديث حالة الجرعة لكل الأدوية المشابهة
                $medication->status = $status;

                // ✅ تأكيد الحفظ في قاعدة البيانات
                if ($medication->save()) {
                    Log::info("✅ تم تحديث الدواء: pill_count = " . $medication->pill_count . ", status = $status");
                } else {
                    Log::error("❌ فشل في حفظ التعديل في قاعدة البيانات!");
                }
            }

            // 📨 إرسال إشعار عندما يصبح العدد 3 لأي من الأدوية المرتبطة
            if ($relatedMedications->first()->pill_count == 3) {
                $this->sendRefillReminder($relatedMedications->first());
            }

        } else {
            Log::error("🔴 لم يتم العثور على أي دواء في قاعدة البيانات للخزانة:  الجرار: $cellId.");
        }
    }

    private function sendRefillReminder($medication)
    {
        $caregiver = User::where('role', 'caregiver')->first(); // البحث عن الـ Caregiver
        if ($caregiver) {
            Mail::to($caregiver->email)->send(new RefillReminderMail($medication));
            Log::info("📧 تم إرسال إشعار إعادة التعبئة إلى: " . $caregiver->email);
        } else {
            Log::error("🔴 لم يتم العثور على الـ Caregiver في قاعدة البيانات.");
        }
    }
}
