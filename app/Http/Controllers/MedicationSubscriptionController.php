<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RefillReminderMail;
use App\Mail\MissedDoseMail;
use App\Services\MqttClientService;
use App\Jobs\MedicationSystemJob;
use Illuminate\Support\Facades\Cache;
use App\Models\Patient;


class MedicationSubscriptionController extends Controller
{
    public function subscribeToMissedDoses()
    {
        $mqtt = new MqttClientService();
        $mqtt->connect();

        if ($mqtt->isConnected()) {
            Log::info("📡 الاشتراك في `medication/missed` بدأ...");

            $messageReceived = false; // متغير للتحقق من استقبال الرد

            $mqtt->subscribe("medication/missed", function ($receivedTopic, $message) use ($mqtt, &$messageReceived) {
                Log::info("📩 رسالة مستقبلة من MQTT: $message");
                $data = json_decode($message, true);

                if (isset($data["status"], $data["closet_id"], $data["cell_id"])) {
                    $status = $data["status"];
                    $closetId = $data["closet_id"];
                    $cellId = $data["cell_id"];

                    Log::info("✅ تم استقبال حالة الجرعة: $status | 🏠 رقم الخزانة: $closetId | 📦 رقم الخلية: $cellId");

                    // ✅ تحديث قاعدة البيانات
                    $this->updateMedicationCount($closetId, $cellId, $status);

                    // ✅ تسجيل أنه تم استقبال رسالة
                    $messageReceived = true;
                }
            });

            Log::info("🔄 بدء `loop()` للاستماع للرسائل...");

            // ✅ استمرار الاستماع حتى استقبال رسالة جديدة
            while (!$messageReceived) {
                $mqtt->loop(1); // ✅ الاستماع بتحديثات قصيرة
            }

            // ✅ عند استقبال رسالة، يتم إنهاء الاشتراك
            Log::info("✅ تم استقبال رد، سيتم إيقاف `loop()`.");
            $mqtt->stopListening();
        } else {
            Log::error("🔴 فشل الاتصال بـ MQTT، سيتم إعادة المحاولة بعد 5 ثوانٍ...");
            sleep(5);
            $this->subscribeToMissedDoses();
        }
    }


    private function updateMedicationCount($closetId, $cellId, $status)
    {
        // 🔍 البحث عن جميع الأدوية بنفس `closet_number` و `medicine_closet_location`
        $medications = Medication::where('medicine_closet_location', $closetId)
                                ->where('medicine_closet_number', $cellId)
                                ->get();

        if ($medications->isNotEmpty()) {
            // ✅ جلب `patient_id` من أول دواء مرتبط
            $patientId = $medications->first()->patient_id;

            // 🔍 البحث عن جميع الجرعات المتعلقة بنفس المريض والخزانة والخلية
            $relatedMedications = Medication::where('patient_id', $patientId)
                                            ->where('medicine_closet_location', $closetId)
                                            ->where('medicine_closet_number', $cellId)
                                            ->get();

            foreach ($relatedMedications as $medication) {
                if ($status === "taken") {
                    if ($medication->pill_count > 0) {
                        $medication->pill_count -= 1;
                    } else {

                        Log::warning("⚠️ لا يمكن تقليل عدد الحبوب لأن العدد بالفعل صفر! [خزانة: $closetId | خلية: $cellId | دواء: {$medication->name}]");

                        // ✅ إذا لم يتم إعادة تعبئة الدواء، لا يتم إرسال الجرعة مرة أخرى
                        if ($medication->pill_count == 0) {
                            Log::error("🔴 لا يمكن إرسال الجرعة لأن الدواء نفد ولم يتم إعادة تعبئته! [دواء: {$medication->name}]");
                        }

                    }
                }

                // ✅ تحديث حالة الجرعة لكل الأدوية المشابهة
                $medication->status = $status;

                // ✅ تأكيد الحفظ في قاعدة البيانات
                if ($medication->save()) {
                    Log::info("✅ تم تحديث الدواء: pill_count = " . $medication->pill_count . ", status = $status");
                    if ($status === "missed") {
                        Log::warning("⚠️ الجرعة لم تُؤخذ في وقتها! إرسال إشعار إلى Caregiver...");

                        $this->sendMissedDoseAlert($medication);
                    }
                    // ✅ بعد تحديث الجرعة، تحقق من وجود جرعات قادمة
                    if (app(\App\Http\Controllers\MedicationController::class)->hasUpcomingMedications()) {
                        Log::info("📅 يوجد جرعات قادمة، سيتم تشغيل runMedicationSystem()...");
                        app(\App\Http\Controllers\MedicationController::class)->runMedicationSystem();
                    } else {
                        Log::info("✅ لا يوجد جرعات جديدة، سيتم إنهاء الاستماع.");
                    }
                } else {
                    Log::error("❌ فشل في حفظ تحديث الدواء في قاعدة البيانات! [خزانة: $closetId | خلية: $cellId | دواء: {$medication->name}]");
                }
            }

            // 📨 إرسال إشعار عندما يصبح عدد الحبوب **لأي جرعة** في الجرعات المرتبطة يساوي 3
            if ($relatedMedications->first()->pill_count == 3) {
                $this->sendRefillReminder($relatedMedications->first());
            }

        } else {
            Log::error("🔴 لم يتم العثور على أي دواء في قاعدة البيانات للخزانة: $closetId والخلية: $cellId.");
        }
    }


    private function sendRefillReminder($medication)
    {
        $patient = Patient::find($medication->patient_id);

        if ($patient && $patient->caregiver_email) {
            Log::info("📧 سيتم إرسال إشعار Missed Dose إلى: " . $patient->caregiver_email);

            // ✅ إرسال الإيميل
            Mail::to($patient->caregiver_email)->send(new RefillReminderMail($medication));

            Log::info("✅ تم إرسال الإيميل إلى: " . $patient->caregiver_email);
        } else {
            Log::error("🔴 لم يتم العثور على بريد Caregiver لهذا المريض.");
        }

    }
    private function sendMissedDoseAlert($medication)
    {
        // 🔍 جلب المريض المرتبط بهذه الجرعة
        $patient = Patient::find($medication->patient_id);

        if ($patient && $patient->caregiver_email) {
            Log::info("📧 سيتم إرسال إشعار Missed Dose إلى: " . $patient->caregiver_email);

            // ✅ إرسال الإيميل
            Mail::to($patient->caregiver_email)->send(new MissedDoseMail($medication));

            Log::info("✅ تم إرسال الإيميل إلى: " . $patient->caregiver_email);
        } else {
            Log::error("🔴 لم يتم العثور على بريد Caregiver لهذا المريض.");
        }
    }

}


