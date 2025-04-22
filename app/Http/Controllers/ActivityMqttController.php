<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache; // تأكد من إضافة هذا في أعلى الملف

use Illuminate\Support\Facades\Log;
use App\Models\Activity;
use App\Models\Patient;
use App\Models\Medication;
use Illuminate\Http\Request;
use App\Services\MqttClientService;

class ActivityMqttController extends Controller
{
    public function index()
    {
        $activities = Activity::with('patient')->latest()->get();
        return view('dashboard.layout.activities.index', compact('activities'));
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
                Activity::create([
                    'patient_id' => $med->patient_id,
                    'cognitive_question_answer' => $data['message'],
                ]);
                Log::info("✅ تم تسجيل إجابة التمرين المعرفي للمريض ID = {$med->patient_id}");
            } else {
                Log::warning("⚠️ لم يتم العثور على دواء للخزانة $closetId والخلية $cellId.");
            }

            // 🧹 حذف الرسالة retained من البروكر
            $mqtt = MqttClientService::getInstance();
            $mqtt->publish("nao/answer_report", '', true);
            Log::info("🧹 تم مسح الرسالة retained من topic: nao/answer_report");
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
                Activity::create([
                    'patient_id' => $med->patient_id,
                    'color_activity_level' => $text,
                ]);
                Log::info("✅ تم تسجيل مستوى التمرين اللوني للمريض ID = {$med->patient_id}");
            } else {
                Log::warning("⚠️ لم يتم العثور على دواء للخزانة $closetId والخلية $cellId.");
            }

            // 🧹 حذف الرسالة retained من البروكر
            $mqtt = MqttClientService::getInstance();
            $mqtt->publish("nao/activity_end", '', true);
            Log::info("🧹 تم مسح الرسالة retained من topic: nao/activity_end");
        } else {
            Log::error("⚠️ لم يتم العثور على معلومات الخزانة في الكاش.");
        }
    }



}
