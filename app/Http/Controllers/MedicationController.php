<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Services\MqttService;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;
use App\Mail\MissedDoseMail;
use App\Mail\RefillReminderMail;


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

        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'name' => 'required|string|max:255',
            'time_of_intake' => 'required|date_format:H:i',
            'frequency' => 'required|string|max:255',
            'medicine_closet_number' => 'required|integer|min:1',
            'medicine_closet_location' => 'required|integer|min:1',
            'expiration_date' => 'required|date_format:Y-m-d|after_or_equal:' . now()->toDateString(),
            'pill_count' => 'required|integer|min:1',


        ]);
    // ✅ استخدام pill_count الحالي من نفس الخزانة والخلية إن وُجد
    $existingMedication = Medication::where('medicine_closet_number', $validatedData['medicine_closet_number'])
        ->where('medicine_closet_location', $validatedData['medicine_closet_location'])
        ->orderByDesc('id')
        ->first();

    if ($existingMedication) {
        $validatedData['pill_count'] = $existingMedication->pill_count;
    }

        $medication = Medication::create(array_merge($validatedData, [
            'status' => 'not taken'
        ]));
        // ✅ نشر كل مواعيد الأدوية عبر MQTT
        try {
            $mqtt = MqttClientService::getInstance();

            // جلب كل الأدوية التي تحتوي على جرعات متبقية وغير منتهية الصلاحية
            $allMedications = Medication::where('pill_count', '>', 0)
                ->where('expiration_date', '>=', now()->toDateString())
                ->get(['time_of_intake', 'medicine_closet_number', 'medicine_closet_location']);

            // إعادة تنسيق البيانات بشكل أبسط
            $formatted = $allMedications->map(function ($med) {
                return [
                    'closet_number' => $med->medicine_closet_location,
                    'cell_number' => $med->medicine_closet_number,
                    'time' => substr($med->time_of_intake, 0, 5),

                ];
            });

            $mqtt->publish("medication/schedule", json_encode($formatted));
            Log::info("📢 تم نشر جميع مواعيد الأدوية إلى medication/schedule");
        } catch (\Exception $e) {
            Log::error("❌ فشل نشر المواعيد عبر MQTT: " . $e->getMessage());
        }

        return redirect()->route('medications.view')->with('success', 'Medication added successfully!');
    }

public function destroy($id)
{
    $medication = Medication::findOrFail($id);
    $medication->delete();

    return redirect()->route('medications.view')->with('success', 'The medication has been successfully removed.');
}


    public function index()
    {

        $medications = Medication::with('patient')->get(); //  جلب الأدوية مع بيانات المرضى المرتبطين بها
        return view('dashboard.layout.medications.view', compact('medications'));
    }
    public function updatePillCount(Request $request, $id)
    {
        $request->validate([
            'pill_count' => 'required|integer|min:0', // يجب أن يكون رقمًا موجبًا أو صفرًا
        ]);

        // 🔍 البحث عن الدواء المحدد
        $medication = Medication::findOrFail($id);
        $newPillCount = $request->pill_count;

        // 🔍 جلب جميع الأدوية الخاصة بنفس المريض ونفس الجرّار
        $relatedMedications = Medication::where('patient_id', $medication->patient_id)
            ->where('medicine_closet_location', $medication->medicine_closet_location)
            ->where('medicine_closet_number', $medication->medicine_closet_number)
            ->get();

        // 🔄 تحديث كل الجرعات الخاصة بنفس الجرّار ونفس المريض
        foreach ($relatedMedications as $med) {
            $med->pill_count = $newPillCount;
            $med->save();
        }

        return redirect()->back()->with('success', 'Pill count updated successfully for all related medications in the same closet!');
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
        $medications = Medication::whereRaw("TIME_FORMAT(time_of_intake, '%H:%i') = ?", [$currentTime])->where('pill_count', '>', 0)->get();


        if ($medications->isEmpty()) {
            Log::info("⏳ لا يوجد أدوية يجب إرسالها الآن، سيتم البحث عن الجرعة القادمة...");

            // ✅ البحث عن الجرعة التالية
            $nextMedication = Medication::where('time_of_intake', '>', now()->format('H:i'))
                ->orderBy('time_of_intake', 'asc')
                ->first();


            if ($nextMedication) {
                Log::info("⏭️ أقرب موعد جرعة هو عند: {$nextMedication->time_of_intake}، سيتم الفحص مرة أخرى في الدقيقة القادمة.");
            } else {
                Log::info("✅ لا يوجد جرعات قادمة، سيتم إنهاء `runMedicationSystem()` مؤقتًا.");
            }
            return;
        }


        $mqtt = MqttClientService::getInstance();


        $newMedicationSent = false;

        foreach ($medications as $medication) {
            $closetNumber = $medication->medicine_closet_location;
            $cellNumber = $medication->medicine_closet_number;
            $cacheKey = "sent_medication_{$closetNumber}_{$cellNumber}_{$currentTime}";

            if (Cache::has($cacheKey)) {
                Log::info("⏭️ تم تخطي إرسال الدواء ($closetNumber, $cellNumber) لأنه تم إرساله مسبقًا خلال هذه الدقيقة.");
                continue;
            }



            $mqtt->publish("medication/reminder", json_encode([
                "closet_number" => $closetNumber,
                "cell_number" => $cellNumber,
                "time" => substr($medication->time_of_intake, 0, 5)

            ]));


            Log::info("🚀 تم إرسال رقم الخزانة: $closetNumber و رقم الخلية: $cellNumber إلى التوبيك: medication/reminder");

            $mqtt->publish("nao/reminder", 0);
            Log::info("🤖 أُرسلت رسالة التذكير إلى NAO: 🔔 حان وقت تناول الدواء!");

            Cache::put($cacheKey, true, now()->addMinute());

            $newMedicationSent = true;
        }

        if ($newMedicationSent) {
            Log::info("📡 تم إرسال جرعة جديدة ✅ (لا حاجة للاشتراك لأن المستمع يعمل دائمًا).");
        } else {
            Log::info("⏭️ لم يتم إرسال أي جرعات جديدة، لن يتم الاشتراك في `medication/missed`.");
        }
    }
}
