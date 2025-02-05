<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Patient;

class ActivityController extends Controller
{
    public function create()
    {
        $patients = Patient::doesntHave('activity')->get(); // عرض فقط المرضى الذين ليس لديهم نشاط مسجل
        return view('dashboard.layout.activities.add', compact('patients'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id|unique:activities,patient_id',
            'color_activity_level' => 'required|date_format:H:i',
            'cognitive_question_answer' => 'required|date_format:H:i',
        ]);

        Activity::create($validatedData);

        return redirect()->route('activities.view')->with('success', 'Activity added successfully!');
    }

    public function index()
    {
        $activities = Activity::with('patient')->get();
        return view('dashboard.layout.activities.view', compact('activities'));
    }
}
