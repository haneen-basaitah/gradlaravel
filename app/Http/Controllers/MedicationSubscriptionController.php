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
use App\Models\RecentMedication; // أضف هذا في أعلى الملف



class MedicationSubscriptionController extends Controller
{
    public function handleMissedMessage($topic, $message)
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
            $this->updateMedicationCount($closet, $cell, $status,$time);

            // ✅ تخزين الوقت الجديد المرسل
            Cache::put($cacheKey, $time, now()->addHours(2));
            Log::info("✅ تم تحديث الدواء: pill_count محدث | status = $status");
        } else {
            Log::warning("⚠️ الرسالة المستلمة لا تحتوي على جميع الحقول المطلوبة!");
        }
    }


public function updateMedicationCount($closetId, $cellId, $status, $time)
{
    // ✅ كاش لمنع التكرار لنفس الوقت فقط
    $cacheKey = "handled_{$closetId}_{$cellId}";
    $lastHandledTime = Cache::get($cacheKey);
    Log::info("🕒 مقارنة الأوقات - آخر معالجة: $lastHandledTime | الوقت الحالي المستلم: $time");

    if ($lastHandledTime === $time) {
        Log::warning("⚠️ تم تجاهل التحديث لأنه مكرر لنفس الوقت: $time");
        return;
    }

    Cache::put($cacheKey, $time, now()->addHours(2));

    $targetTime = substr($time, 11, 5); // "HH:MM" فقط

    // ✅ تقليل pill_count من كل الجرعات في نفس الخزانة والخلية
    $allInSameCell = Medication::where('medicine_closet_location', $closetId)
        ->where('medicine_closet_number', $cellId)
        ->get();

    foreach ($allInSameCell as $med) {
        if ($status === "taken" && $med->pill_count > 0) {
            $med->pill_count -= 1;
            $med->save();
            Log::info("📦 تم تقليل عدد الحبوب للدواء ID = {$med->id} إلى {$med->pill_count}");
        }
    }

    // ✅ تغيير حالة الجرعة المطابقة للوقت فقط
    $targetMedication = Medication::where('medicine_closet_location', $closetId)
        ->where('medicine_closet_number', $cellId)
        ->whereRaw("TIME_FORMAT(time_of_intake, '%H:%i') = ?", [$targetTime])
        ->first();

    if ($targetMedication) {
        // ✅ تخزين معلومات الجرعة الأخيرة في الكاش لاستخدامها لاحقًا في أنشطة الروبوت
        Cache::put('last_closet_id', $closetId, now()->addMinutes(10));
        Cache::put('last_cell_id', $cellId, now()->addMinutes(10));
        Cache::put('last_handled_time', $time, now()->addMinutes(10));

        $targetMedication->status = $status;
        $targetMedication->save();

        Log::info("✅ تم تعديل حالة الجرعة ID = {$targetMedication->id} عند $targetTime إلى $status");

        if ($status === "missed") {
            $this->sendMissedDoseAlert($targetMedication);
        }

        if ($status === "taken") {
            $mqtt = \App\Services\MqttClientService::getInstance();
            if ($mqtt->isConnected()) {
                $mqtt->publish("nao/start_activity", json_encode(["start_activity" => true]), 1, false);
                Log::info("🚀 تم إرسال إشارة بدء التمرين إلى NAO بعد تناول الدواء.");
            }
        }

        if ($targetMedication->pill_count == 3) {
            $this->sendRefillReminder($targetMedication);
        }

        if (app(\App\Http\Controllers\MedicationController::class)->hasUpcomingMedications()) {
            Log::info("📅 يوجد جرعات قادمة، سيتم تشغيل runMedicationSystem()...");
            app(\App\Http\Controllers\MedicationController::class)->runMedicationSystem();
        }
    } else {
        Log::error("❌ لم يتم العثور على جرعة مطابقة للتوقيت: $targetTime في الخزانة $closetId والخلية $cellId");
    }
}



public function sendRefillReminder($medication)
{
    $patient = Patient::find($medication->patient_id);
    Log::info("🧪 بدأ التحقق من بريد مقدم الرعاية");

    $email = null;

    // جرّب العلاقة
    if ($patient && $patient->caregiver && $patient->caregiver->email) {
        $email = $patient->caregiver->email;
        Log::info("✅ تم جلب الإيميل من العلاقة: " . $email);
    }
    // إذا العلاقة لا تعمل، استخدم caregiver_email
    elseif ($patient && $patient->caregiver_email) {
        $email = $patient->caregiver_email;
        Log::info("✅ تم جلب الإيميل من الحقل المباشر: " . $email);
    }

    if ($email) {
        Mail::to($email)->send(new RefillReminderMail($medication));
        Log::info("📧 تم إرسال الإيميل إلى: " . $email);
    } else {
   Log::error("🔴 [تم التعديل فعليًا] لم يتم العثور على بريد Caregiver لهذا المريض.");
    }
}





public function sendMissedDoseAlert($medication)
{
    $patient = Patient::find($medication->patient_id);
    Log::info("🧪 بدأ التحقق من بريد مقدم الرعاية");

    $email = null;

    // جرّب العلاقة
    if ($patient && $patient->caregiver && $patient->caregiver->email) {
        $email = $patient->caregiver->email;
        Log::info("✅ تم جلب الإيميل من العلاقة: " . $email);
    }
    // إذا العلاقة لا تعمل، استخدم caregiver_email
    elseif ($patient && $patient->caregiver_email) {
        $email = $patient->caregiver_email;
        Log::info("✅ تم جلب الإيميل من الحقل المباشر: " . $email);
    }

    if ($email) {
 Mail::to($email)->send(new MissedDoseMail($medication));
        Log::info("📧 تم إرسال الإيميل إلى: " . $email);
    } else {
Log::error("🔴 [تم التعديل فعليًا] لم يتم العثور على بريد Caregiver لهذا المريض.");
    }
}




}


