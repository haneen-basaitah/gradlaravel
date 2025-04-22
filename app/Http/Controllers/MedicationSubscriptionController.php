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
{public function handleMissedMessage($topic, $message)
    {
        Log::info("📩 [MQTT] ($topic): $message");

        $data = json_decode($message, true);

        if (isset($data["status"], $data["closet_id"], $data["cell_id"], $data["time"])) {
            $status = $data["status"];
            $closet = $data["closet_id"];
            $cell   = $data["cell_id"];
            $time   = $data["time"];  // مثال: "2025-04-22 11:32"

            Log::info("🟢 الحالة المستلمة: $status | الخزانة: $closet | الخلية: $cell | الموعد: $time");

            // ✅ مفتاح كاش ثابت
            $cacheKey = "handled_{$closet}_{$cell}";

            $lastHandledTime = Cache::get($cacheKey);
            Log::info("🕒 مقارنة الأوقات - آخر معالجة: $lastHandledTime | الوقت الحالي المستلم: $time");


            // ✅ قارن الوقت المرسل وليس now()
            if ($lastHandledTime === $time) {
                Log::warning("⚠️ تم تجاهل التحديث لأنه مكرر لنفس الوقت: ($closet, $cell) عند $time");
                return;
            }

            // ✅ تم التحقق، تحديث الحالة
            $this->updateMedicationCount($closet, $cell, $status);

            // ✅ تخزين الوقت الجديد المرسل
            Cache::put($cacheKey, $time, now()->addHours(2));
            Log::info("✅ تم تحديث الدواء: pill_count محدث | status = $status");
        } else {
            Log::warning("⚠️ الرسالة المستلمة لا تحتوي على جميع الحقول المطلوبة!");
        }
    }






    public function updateMedicationCount($closetId, $cellId, $status)
    {
        // ✅ كاش لمنع التحديث المتكرر خلال نفس الدقيقة
        $cacheKey = "updated_{$closetId}_{$cellId}_" . now()->format('H:i');
        if (Cache::has($cacheKey)) {
            Log::warning("⚠️ تم تجاهل التحديث لأنه مكرر خلال نفس الدقيقة: ($closetId, $cellId)");
            return;
        }
        Cache::put($cacheKey, true, now()->addMinutes(1));

        $medications = Medication::where('medicine_closet_location', $closetId)
            ->where('medicine_closet_number', $cellId)
            ->get();

        if ($medications->isNotEmpty()) {
            $patientId = $medications->first()->patient_id;

            $relatedMedications = Medication::where('patient_id', $patientId)
                ->where('medicine_closet_location', $closetId)
                ->where('medicine_closet_number', $cellId)
                ->get();

            foreach ($relatedMedications as $medication) {
                if ($status === "taken") {
                    Cache::put('last_closet_id', $closetId, now()->addMinutes(10));
                    Cache::put('last_cell_id', $cellId, now()->addMinutes(10));

                    // تقليل عدد الحبوب
                    if ($medication->pill_count > 0) {
                        $medication->pill_count -= 1;
                    }
                }

                $medication->status = $status;

                if ($medication->save()) {
                    Log::info("✅ تم تحديث الدواء: pill_count = {$medication->pill_count}, status = $status");

                    if ($status === "missed") {
                        Log::warning("⚠️ الجرعة لم تُؤخذ في وقتها! إرسال إشعار إلى Caregiver...");
                        $this->sendMissedDoseAlert($medication);
                    }

                    if ($status === "taken") {
                        // ✅ إرسال start_activity إلى NAO بعد تناول الجرعة
                        $mqtt = \App\Services\MqttClientService::getInstance();
                        if ($mqtt->isConnected()) {
                            $mqtt->publish("nao/start_activity", json_encode(["start_activity" => true]), 1, false);
                            Log::info("🚀 تم إرسال إشارة بدء التمرين إلى NAO بعد تناول الدواء.");
                        }
                    }

                    // ✅ تشغيل النظام مجددًا
                    if (app(\App\Http\Controllers\MedicationController::class)->hasUpcomingMedications()) {
                        Log::info("📅 يوجد جرعات قادمة، سيتم تشغيل runMedicationSystem()...");
                        app(\App\Http\Controllers\MedicationController::class)->runMedicationSystem();
                    }
                } else {
                    Log::error("❌ فشل في حفظ تحديث الدواء في قاعدة البيانات!");
                }
            }

            if ($relatedMedications->first()->pill_count == 3) {
                $this->sendRefillReminder($relatedMedications->first());
            }
        } else {
            Log::error("🔴 لم يتم العثور على أي دواء في قاعدة البيانات للخزانة: $closetId والخلية: $cellId.");
        }
    }


    public function sendRefillReminder($medication)
    {
        $patient = Patient::find($medication->patient_id);

        if ($patient && $patient->caregiver_email) {
            Log::info("📧 سيتم إرسال إشعار Refill Reminder إلى: " . $patient->caregiver_email);
            Mail::to($patient->caregiver_email)->send(new RefillReminderMail($medication));
            Log::info("✅ تم إرسال الإيميل إلى: " . $patient->caregiver_email);
        } else {
            Log::error("🔴 لم يتم العثور على بريد Caregiver لهذا المريض.");
        }
    }

    public function sendMissedDoseAlert($medication)
    {
        $patient = Patient::find($medication->patient_id);

        if ($patient && $patient->caregiver_email) {
            Log::info("📧 سيتم إرسال إشعار Missed Dose إلى: " . $patient->caregiver_email);
            Mail::to($patient->caregiver_email)->send(new MissedDoseMail($medication));
            Log::info("✅ تم إرسال الإيميل إلى: " . $patient->caregiver_email);
        } else {
            Log::error("🔴 لم يتم العثور على بريد Caregiver لهذا المريض.");
        }
    }

}


