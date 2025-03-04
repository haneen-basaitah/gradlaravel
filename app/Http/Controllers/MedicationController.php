<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;
use App\Models\Patient;
use App\Services\MqttService;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;

use Illuminate\Support\Facades\Cache;

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
/**
 * ✅ **دالة لفحص وجود مواعيد أدوية جديدة**
 */
public function hasUpcomingMedications()
{
    $currentTime = now();
    return \App\Models\Medication::where('time_of_intake', '>=', $currentTime->format('H:i'))->exists();
}

public function runMedicationSystem()
{
    while (true) { // ✅ حلقة مستمرة حتى انتهاء جميع الجرعات
        $currentTime = now()->format('H:i');
        Log::info("🕒 الوقت الحالي في Laravel: " . $currentTime);

        $medications = Medication::whereRaw("TIME_FORMAT(time_of_intake, '%H:%i') = ?", [$currentTime])->get();

        if ($medications->isEmpty()) {
            Log::info("⏳ لا يوجد أدوية يجب إرسالها الآن، سيتم البحث عن الجرعة القادمة...");

            // ✅ البحث عن الجرعة التالية
            $nextMedication = Medication::where('time_of_intake', '>', now()->format('H:i'))
                ->orderBy('time_of_intake', 'asc')
                ->first();

            if ($nextMedication) {
                $waitTime = strtotime($nextMedication->time_of_intake) - strtotime(now()->format('H:i'));
                Log::info("⏭️ سيتم انتظار $waitTime ثانية حتى موعد الجرعة التالية: {$nextMedication->name} في {$nextMedication->time_of_intake}");

                sleep($waitTime); // ⏳ الانتظار حتى يحين موعد الجرعة التالية
                continue; // 🔄 إعادة تشغيل `runMedicationSystem()` تلقائيًا عند انتهاء الانتظار
            } else {
                Log::info("✅ لا يوجد جرعات قادمة، سيتم إنهاء `runMedicationSystem()` مؤقتًا.");
                return;
            }
        }

        $mqtt = new MqttClientService();
        $mqtt->connect();

        foreach ($medications as $medication) {
            $closetNumber = $medication->medicine_closet_location;
            $cellNumber = $medication->medicine_closet_number;
            $cacheKey = "sent_medication_{$closetNumber}_{$cellNumber}_{$currentTime}";

            if (Cache::has($cacheKey)) {
                Log::info("⏭️ تم تخطي إرسال الدواء ($closetNumber, $cellNumber) لأنه تم إرساله مسبقًا خلال هذه الدقيقة.");
                continue;
            }

            // ✅ إرسال الجرعة عبر MQTT
            $mqtt->publish("medication/reminder", json_encode([
                "closet_number" => $closetNumber,
                "cell_number" => $cellNumber,
            ]));

            Log::info("🚀 تم إرسال رقم الخزانة: $closetNumber و رقم الخلية: $cellNumber إلى التوبيك: medication/reminder");

            Cache::put($cacheKey, true, now()->addMinute());
        }

        // ✅ الاشتراك في `missed` عند الحاجة
        if ($this->hasUpcomingMedications()) {
            Log::info("📡 هناك جرعات قادمة، سيتم الاشتراك في `medication/missed`...");
            app(\App\Http\Controllers\MedicationSubscriptionController::class)->subscribeToMissedDoses();
        } else {
            Log::info("✅ لا يوجد جرعات جديدة تحتاج للاشتراك في `missed` الآن.");
            return; // ⛔ إنهاء `runMedicationSystem()` إذا لم يكن هناك جرعات جديدة
        }
    }
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
