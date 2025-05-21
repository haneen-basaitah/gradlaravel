<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache; // تأكد من إضافة هذا في أعلى الملف

use Illuminate\Support\Facades\Log;
use App\Models\Activity;
use App\Models\Patient;
use App\Models\Medication;
use Illuminate\Http\Request;
use App\Services\MqttClientService;
use Illuminate\Support\Facades\Auth;
use App\Models\RecentMedication;


class ActivityMqttController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user(); // المستخدم الحالي (مقدم الرعاية)

        $activities = Activity::whereHas('patient', function ($query) use ($user) {
            $query->where('caregiver_email', $user->email);
        })
            ->with('patient')
            ->latest()
            ->get();


        return view('dashboard.layout.activities.view', compact('activities'));
    }



    public function handleAnswerReport($message)
    {
        Log::info("📩 answer_report received: " . $message);

        $data = json_decode($message, true);

        $closetId = Cache::get('last_closet_id');
        $cellId = Cache::get('last_cell_id');

        if (isset($data['message']) && $closetId && $cellId) {
            $med = Medication::where('medicine_closet_location', $closetId)
                ->where('medicine_closet_number', $cellId)
                ->latest()
                ->first();

            if ($med) {
                Activity::updateOrCreate(
                    [
                        'patient_id' => $med->patient_id,
                        'medication_id' => $med->id,
                        'medication_time' => now()->format('Y-m-d H:i'), // مقصوص فقط حتى الدقيقة
                    ],
                    [
                        'cognitive_question_answer' => $data['message'], // أو color_activity_level حسب الدالة
                    ]
                );

                Log::info("✅ تم تسجيل إجابة التمرين المعرفي للمريض ID = {$med->patient_id}");
            } else {
                Log::warning("⚠️ لم يتم العثور على دواء للخزانة $closetId والخلية $cellId.");
            }
        } else {
            Log::error("⚠️ البيانات غير مكتملة أو غير صالحة في answer_report.");
        }
    }


public function handleActivityEnd($message)
{
    Log::info("📩 [nao/activity_end] الرسالة الأصلية: " . $message);

    $data = json_decode($message, true);
    $text = is_array($data) ? array_values($data)[0] : $data;

    $closetId = Cache::get('last_closet_id');
    $cellId = Cache::get('last_cell_id');

    Log::info("🧠 دخلنا فعلياً إلى handleActivityEnd");
    Log::info("📩 [nao/activity_end] closetId: $closetId, cellId: $cellId");

    if ($closetId && $cellId) {
        $med = Medication::where('medicine_closet_location', $closetId)
                         ->where('medicine_closet_number', $cellId)
                         ->orderBy('updated_at', 'desc')
                         ->first();

        if ($med) {
            Log::info("🧾 سيتم إنشاء/تحديث Activity للمريض {$med->patient_id} ودواء ID = {$med->id} في الوقت {$med->updated_at}");

            // 🔍 طباعة القيم قبل التخزين
            Log::info("🧾 بيانات النشاط قبل التخزين:", [
                'patient_id' => $med->patient_id,
                'medication_id' => $med->id,
                'medication_time' => $med->updated_at->format('Y-m-d H:i'),
                'color_activity_level' => $text,
            ]);

            try {
                $activity = Activity::updateOrCreate(
                    [
                        'patient_id' => $med->patient_id,
                        'medication_id' => $med->id,
                        'medication_time' => $med->updated_at->format('Y-m-d H:i'),
                    ],
                    [
                        'color_activity_level' => $text,
                    ]
                );

                Log::info("✅ ✅ ✅ تم حفظ النشاط: " . json_encode($activity->toArray()));
            } catch (\Exception $e) {
                Log::error("❌ خطأ أثناء إنشاء/تحديث النشاط: " . $e->getMessage());
            }

        } else {
            Log::warning("⚠️ لم يتم العثور على جرعة حديثة في ($closetId, $cellId)");
        }
    } else {
        Log::error("⚠️ لم يتم العثور على معلومات الخزانة في الكاش.");
    }
}

}
