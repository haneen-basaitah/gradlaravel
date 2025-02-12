<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Medication;
use App\Models\Patient;
use App\Services\MqttService;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;

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
            'medicine_closet_number' => 'required|string|max:255',
            'medicine_closet_location' => 'required|string|max:255',
            'expiration_date' => 'required',
        ]);
        //dd($validatedData);

        Medication::create($validatedData);

        return redirect()->route('medications.view')->with('success', 'Medication added successfully!');
    }
    public function index()
    {
        $medications = Medication::with('patient')->get(); //  جلب الأدوية مع بيانات المرضى المرتبطين بها
        return view('dashboard.layout.medications.view', compact('medications'));
    }


//    ====================public function sendTimeToDevices($id)=====================================

public function checkAndSendMedicationReminders()
{
    $currentTime = now()->format('H:i:00'); // الحصول على الوقت الحالي بصيغة متوافقة مع MySQL

    Log::info("🕒 الوقت الحالي في Laravel: " . $currentTime);

    // جلب جميع الأدوية المخزنة في قاعدة البيانات للتحقق من القيم
    $allMedications = Medication::select('id', 'name', 'time_of_intake')->get();
    Log::info("📋 جميع أوقات الأدوية المخزنة: " . json_encode($allMedications));

    // البحث عن الأدوية التي يجب إرسالها الآن
    $medications = Medication::whereRaw("TIME_FORMAT(time_of_intake, '%H:%i:00') = ?", [$currentTime])->get();

    Log::info("📊 عدد الأدوية المطابقة للوقت الحالي: " . $medications->count());

    if ($medications->isEmpty()) {
        Log::info("⏳ لا يوجد أدوية يجب إرسالها الآن.");
        return;
    }

    // إعداد اتصال MQTT
    $server = '10.212.63.66';
    $port = 1883;
    $clientId = 'laravel_mqtt_scheduler';

    $mqtt = new MqttClient($server, $port, $clientId);
    $mqtt->connect();

    foreach ($medications as $medication) {
   
        $message = json_encode(["command" => "open"]);
        $mqtt->publish("esp32/medication", $message, 0);
        Log::info("🚀 أُرسلت إلى ESP32: " . $message);

        // تجهيز الرسالة إلى NAO
        $naoMessage = json_encode([
            "medicine" => $medication->name,
            "time_of_intake" => $medication->time_of_intake,
            "message" => "حان وقت تناول الدواء: " . $medication->name
        ]);
        $mqtt->publish("nao/reminder", $naoMessage, 0);
        Log::info("🤖 أُرسلت إلى NAO: " . $naoMessage);
    }

    $mqtt->disconnect();
}

}


