<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Medication;
use App\Models\Patient;
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
}
