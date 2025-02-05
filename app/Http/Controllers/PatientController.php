<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Patient;

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
        // جلب البريد الإلكتروني الخاص بمقدم الرعاية الحالي
        $caregiverEmail = auth()->user()->email;

        // استرجاع المرضى المرتبطين بهذا البريد الإلكتروني
        $patients = Patient::where('caregiver_email', $caregiverEmail)->get();

        // عرض المرضى في صفحة view.blade.php
        return view('dashboard.layout.patients.view', compact('patients'));
    }

    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'medical_condition' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // إضافة البريد الإلكتروني الخاص بـ Caregiver
        $validatedData['caregiver_email'] = auth()->user()->email;

        // إنشاء سجل جديد في جدول patients
        Patient::create($validatedData);

        // إعادة التوجيه إلى صفحة العرض مع رسالة نجاح
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

    $patient = Patient::findOrFail($id); //  البحث باستخدام id
    return view('dashboard.layout.patients.edit', compact('patient'));
}

public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'age' => 'required|integer|min:0',
        'medical_condition' => 'nullable|string',
        'notes' => 'nullable|string',
        'caregiver_email' => 'required|exists:caregivers,email',

    ]);

    $patient = Patient::findOrFail($id); // البحث باستخدام id
    $patient->update($validatedData);

    return redirect()->route('patients.view')->with('success', 'Patient updated successfully!');
}


}
