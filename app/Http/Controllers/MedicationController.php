<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Medication;
use App\Models\Patient;
use App\Services\MqttService;
use Illuminate\Support\Facades\Log;
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
            'time_of_intake' => 'required|string|max:255',
            'medicine_closet_number' => 'required|string|max:255',
            'medicine_closet_location' => 'required|string|max:255',
            'expiration_date' => 'required|date',
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


  /**
     * إرسال تذكير بالدواء إلى الروبوت عبر MQTT عند حلول موعد الجرعة.
     */
    public function sendMedicationReminder($id)
    {
        $medication = Medication::findOrFail($id);
        $mqttService = new MqttService();

        $message = json_encode([
            "medicine" => $medication->name,
            "patient" => $medication->patient->name,
            "message" => "حان وقت تناول الدواء: " . $medication->name . " للمريض " . $medication->patient->name
        ]);
        Log::info("🚀 Sending Medication Reminder: " . $message);

        $mqttService->sendMessage("nao/reminder", $message);

        return response()->json(["message" => "Medication reminder sent to NAO"]);
    }

    /**
     * التحقق من مواعيد الأدوية وإرسال إشعار عند حلول موعدها.
     */
    public function checkAndSendMedicationReminders()
    {
        $medications = Medication::whereTime('time_of_intake', '<=', now())->get();

        foreach ($medications as $medication) {
            $this->sendMedicationReminder($medication->id);
        }
    }

}
