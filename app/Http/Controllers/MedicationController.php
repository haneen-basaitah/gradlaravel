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
    $currentTime = now()->format('H:i'); // ✅ تجاهل الثواني تمامًا
    Log::info("🕒 الوقت الحالي في Laravel (بدون ثواني): " . $currentTime);

    // ✅ جلب الجرعات المجدولة الآن
    $medications = Medication::whereRaw("TIME_FORMAT(time_of_intake, '%H:%i') = ?", [$currentTime])->get();

    if ($medications->isEmpty()) {
        Log::info("⏳ لا يوجد أدوية يجب إرسالها الآن، سيتم البحث عن الجرعة القادمة...");

        // ✅ البحث عن الجرعة التالية
        $nextMedication = Medication::where('time_of_intake', '>', now()->format('H:i'))
            ->orderBy('time_of_intake', 'asc')
            ->first();

        if ($nextMedication) {
            $waitTime = max(0, strtotime($nextMedication->time_of_intake) - strtotime(now()->format('H:i')));
            Log::info("⏭️ سيتم جدولة `runMedicationSystem()` بعد $waitTime ثانية عند الساعة {$nextMedication->time_of_intake}.");

            // ✅ جدولة `MedicationSystemJob`
            if (!Cache::has('next_medication_job')) {
                dispatch(new \App\Jobs\MedicationSystemJob())->delay(now()->addSeconds($waitTime));
                Cache::put('next_medication_job', true, now()->addMinutes(10));
            }
        } else {
            Log::info("✅ لا يوجد جرعات قادمة، سيتم إنهاء `runMedicationSystem()` مؤقتًا.");
        }
        return;
    }

    $mqtt = new MqttClientService();
    $mqtt->connect();

    $newMedicationSent = false; // ✅ متغير لتتبع ما إذا كان قد تم إرسال جرعة جديدة

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

        $newMedicationSent = true; // ✅ تأكيد أنه تم إرسال جرعة جديدة
    }

    // ✅ **الاشتراك في `medication/missed` فقط إذا تم إرسال جرعة جديدة**
    if ($newMedicationSent) {
        Log::info("📡 تم إرسال جرعة جديدة، سيتم الاشتراك في `medication/missed`...");
        app(\App\Http\Controllers\MedicationSubscriptionController::class)->subscribeToMissedDoses();
    } else {
        Log::info("⏭️ لم يتم إرسال أي جرعات جديدة، لن يتم الاشتراك في `medication/missed`.");
    }
}




/**
 * ✅ وظيفة تنتظر رد `missed/taken` خلال مدة محددة.
 */
private function waitForResponse($closetNumber, $cellNumber, $timeout)
{
    $startTime = microtime(true);
    while (microtime(true) - $startTime < $timeout) {
        // ✅ التحقق من وجود رد في الكاش (يتم وضعه عند استقبال MQTT)
        if (Cache::has("medication_response_{$closetNumber}_{$cellNumber}")) {
            return true;
        }
        usleep(200000); // 🔄 تحسين الأداء باستخدام `usleep(200ms)` بدلاً من `sleep(1)`
    }
    return false;
}

/**
 * ✅ تحديث حالة الجرعة في قاعدة البيانات عند فشل الاستقبال
 */
private function updateMedicationStatus($closetNumber, $cellNumber, $status)
{
    $medication = Medication::where('medicine_closet_location', $closetNumber)
                            ->where('medicine_closet_number', $cellNumber)
                            ->first();

    if ($medication) {
        $medication->status = $status;
        $medication->save();
        Log::info("✅ تم تحديث حالة الجرعة إلى `$status` للدواء في الخزانة: $closetNumber والخلية: $cellNumber.");
    } else {
        Log::error("❌ لم يتم العثور على الدواء لتحديث حالته.");
    }
}

/**
 * ✅ جدولة `runMedicationSystem()` بناءً على الموعد التالي
 */
private function scheduleNextRun($delay = null)
{
    if ($delay === null) {
        $nextMedication = Medication::where('time_of_intake', '>', now()->format('H:i:00'))
            ->orderBy('time_of_intake', 'asc')
            ->first();

        if (!$nextMedication) {
            Log::info("✅ لا يوجد جرعات جديدة، لن يتم جدولة `runMedicationSystem()`.");
            return;
        }

        $delay = max(0, strtotime($nextMedication->time_of_intake) - strtotime(now()->format('H:i:00')));
    }

    Log::info("📅 سيتم جدولة `MedicationSystemJob` بعد $delay ثانية.");

    // ✅ التأكد من عدم جدولة نفس المهمة مرتين
    if (!Cache::has('next_medication_job')) {
        dispatch(new \App\Jobs\MedicationSystemJob())->delay(now()->addSeconds($delay));
        Cache::put('next_medication_job', true, now()->addMinutes(10));
    } else {
        Log::info("⏳ الوظيفة مجدولة بالفعل، لن يتم إعادة الجدولة.");
    }
}



}
