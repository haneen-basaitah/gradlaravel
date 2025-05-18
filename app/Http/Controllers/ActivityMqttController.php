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

class ActivityMqttController extends Controller
{
public function index()
{
    /** @var \App\Models\User $user */
    $user = auth()->user(); // المستخدم الحالي (مقدم الرعاية)

    $activities = Activity::whereHas('patient', function ($query) use ($user) {
            $query->where('user_id', $user->id);
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
        Log::info("📩 [nao/activity_end] $message");

        $data = json_decode($message, true);
        $text = is_array($data) ? array_values($data)[0] : $data;

        $closetId = Cache::get('last_closet_id');
        $cellId = Cache::get('last_cell_id');

        if ($closetId && $cellId) {
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
                        'color_activity_level' => $text,
                        // أو color_activity_level حسب الدالة
                    ]
                );

                Log::info("✅ تم تسجيل مستوى التمرين اللوني للمريض ID = {$med->patient_id}");
            } else {
                Log::warning("⚠️ لم يتم العثور على دواء للخزانة $closetId والخلية $cellId.");
            }

        } else {
            Log::error("⚠️ لم يتم العثور على معلومات الخزانة في الكاش.");
        }
    }






}
