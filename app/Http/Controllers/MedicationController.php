<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;
use App\Models\Patient;
use App\Services\MqttService;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;
use Illuminate\Support\Facades\Mail;

use App\Services\MqttClientService;


class MedicationController extends Controller

{
    public function create()
    {
        $patients = Patient::all(); // جلب المرضى لاختيار أحدهم عند إضافة الدواء
        return view('dashboard.layout.medications.add', compact('patients'));
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id', //
            'name' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'time_of_intake' => 'required|date_format:H:i',
            'medicine_closet_number' => 'required|integer|min:1',
            'medicine_closet_location' => 'required|integer|min:1',
            'expiration_date' => 'required',
            'pill_count' => 'required|integer|min:1',


        ]);


        $medication = Medication::create(array_merge($validatedData, [
            'status' => 'not taken'
        ]));
        Medication::create($validatedData);

        return redirect()->route('medications.view')->with('success', 'Medication added successfully!');
    }
    public function index()
    {
        $medications = Medication::with('patient')->get(); //  جلب الأدوية مع بيانات المرضى المرتبطين بها
        return view('dashboard.layout.medications.view', compact('medications'));
    }


    //    ====================public function sendTimeToDevices($id)=====================================

        public function runMedicationSystem()
    {
        $currentTime = now()->format('H:i:00');
        Log::info("🕒 الوقت الحالي في Laravel: " . $currentTime);

        $medications = Medication::whereRaw("TIME_FORMAT(time_of_intake, '%H:%i:00') = ?", [$currentTime])->get();

        if ($medications->isEmpty()) {
            Log::info("⏳ لا يوجد أدوية يجب إرسالها الآن.");
            return;
        }

        $mqtt = new MqttClientService();
        $mqtt->connect();

        // إرسال رسائل إلى ESP32
            foreach ($medications as $medication) {
                $closetNumber = $medication->medicine_closet_location;
                $cellNumber = $medication->medicine_closet_number;

                // 🛠️  نشر جميع البيانات في نفس التوبيك
                $message = json_encode([

                    "closet_number" => $closetNumber,
                    "cell_number" => $cellNumber,
                ]);

                $mqtt->publish("medication/reminder", $message,0);
                Log::info("🚀 تم إرسال رقم الخزانة: $closetNumber و رقم الخلية: $cellNumber إلى التوبيك: medication/reminder");

                /// ✅ 🤖 إرسال تذكير إلى الروبوت NAO
                $naoMessage = json_encode([
                    "message" => "🔔 حان وقت تناول الدواء!"
                ]);

                $mqtt->publish("nao/reminder", $naoMessage);
                Log::info("🤖 أُرسلت رسالة التذكير إلى NAO: 🔔 حان وقت تناول الدواء!");

             }
        



  // ✅ بعد نشر البيانات، استدعاء `subscribeToMissedDoses()`
  Log::info("📡 استدعاء `subscribeToMissedDoses()` بعد نشر التذكيرات...");
  app(\App\Http\Controllers\MedicationSubscriptionController::class)->subscribeToMissedDoses();
    }




























    // private function listenToMedicationUpdates()
    // {
    //     $server = '192.168.0.116'; // عنوان الـ Broker
    //     $port = 1883;
    //     $clientId = 'laravel_mqtt_listener';

    //     $mqtt = new MqttClient($server, $port, $clientId);
    //     $mqtt->connect();

    //     // ✅ الاشتراك في الموضوع لاستقبال تحديثات الـ IR
    //     $mqtt->subscribe('medication/status', function ($topic, $message) {
    //         Log::info("📩 Received Pill Intake Message: $message");

    //         // 🔍 تحليل البيانات الواردة
    //         $data = json_decode($message, true);

    //         // التحقق من وجود البيانات اللازمة
    //         if (isset($data['status'], $data['closet_id'], $data['cell'])) {
    //             $status = $data['status'];
    //             $closetId = $data['closet_id'];
    //             $cell = $data['cell'];

    //             // 🔍 البحث عن الدواء المرتبط بالخزانة والخلية
    //             $medication = Medication::where('medicine_closet_location', $closetId)
    //                                     ->where('medicine_closet_number', $cell)
    //                                     ->first();

    //             if ($medication) {
    //                 if ($status === 'taken') {
    //                     // ✅ تقليل عدد الحبات عند تناولها
    //                     if ($medication->pill_count > 0) {
    //                         $medication->pill_count -= 1;
    //                         $medication->status = 'taken';
    //                         $medication->save();

    //                         Log::info("✅ تم أخذ الحبة بنجاح! العدد المتبقي: {$medication->pill_count}");
    //                     } else {
    //                         Log::warning("⚠️ لا توجد حبوب متبقية لهذا الدواء: {$medication->name}");
    //                     }
    //                 } elseif ($status === 'missed') {
    //                     // 🚨 تحديث الحالة عند تفويت الدواء
    //                     $medication->status = 'missed';
    //                     $medication->save();

    //                     // 📧 إرسال إشعار لمقدم الرعاية
    //                     $caregiverEmail = $medication->patient->caregiver_email ?? 'default@caregiver.com';
    //                     Mail::raw("⚠️ لم يتم تناول الدواء في الوقت المحدد!", function ($message) use ($caregiverEmail) {
    //                         $message->to($caregiverEmail)->subject("🚨 تنبيه: جرعة دواء فائتة!");
    //                     });

    //                     Log::warning("⚠️ تم تفويت الجرعة: {$medication->name} - تم إخطار مقدم الرعاية.");
    //                 }
    //             } else {
    //                 Log::error("❌ لم يتم العثور على دواء مطابق للخزانة: {$closetId} والخلية: {$cell}");
    //             }
    //         } else {
    //             Log::error("❌ تنسيق الرسالة المستلمة غير صحيح: $message");
    //         }
    //     }, 0);

    //     // 🎯 الاستماع باستمرار لتحديثات المستشعر
    //    $mqtt->loop(1000);

    //     $mqtt->disconnect();
    // }


}
