<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PatientController extends Controller
{
    // عرض صفحة إضافة مريض جديد
    public function create()
    {
        return view('dashboard.layout.patients.add');
    }

    public function index()
    {
        // التحقق من أن المستخدم مسجل الدخول
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }
        $caregiverEmail = Auth::user()->email;

        // $medications = Medication::whereHas('patient', function ($query) use ($caregiverEmail) {
        //     $query->where('caregiver_email', $caregiverEmail);
        // })->whereDate('expiration_date', now()->addDays(10)->toDateString())->get();

        // foreach ($medications as $medication) {
        //     Mail::to($caregiverEmail)->send(new \App\Mail\MedicationExpiryWarning($medication));
        // }
        // جلب البريد الإلكتروني الخاص بمقدم الرعاية الحالي

        // استرجاع المرضى المرتبطين بهذا البريد الإلكتروني
        $patients = Patient::where('caregiver_email', $caregiverEmail)->get();

        // عرض المرضى في صفحة view.blade.php
        return view('dashboard.layout.patients.view', compact('patients'));
    }

    public function store(Request $request)
    {
        // ✅ التحقق من صحة البيانات بما يشمل closet_id
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'closet_id' => 'required|integer', // ✅ أضفنا هذا السطر
        ]);

        // ✅ إضافة البريد الإلكتروني الخاص بـ Caregiver
        $validatedData['caregiver_email'] = auth()->user()->email;

        // ✅ إنشاء سجل جديد في جدول patients
        Patient::create($validatedData);

        // ✅ إعادة التوجيه إلى صفحة العرض مع رسالة نجاح
        return redirect()->route('patients.view')->with('success', 'Elderly added successfully!');
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id); // البحث باستخدام id
        $patient->delete();

        return redirect()->route('patients.view')->with('success', 'Patient deleted successfully!');
    }


    public function edit($id)
{

    $patient =Patient::findOrFail($id);
    return view('dashboard.layout.patients.edit', compact('patient'));
}

public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'age' => 'required|integer|min:0',
         'closet_id' => 'required|integer|min:1', // إذا كنت تريد التأكد من وجود رقم خزانة

    ]);

    $patient = Patient::findOrFail($id); // البحث باستخدام id
    $patient->update($validatedData);

    return redirect()->route('patients.view')->with('success', 'Patient updated successfully!');
}


}
