<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id',
        'medication_id',
        'medication_time', // ✅ أضف هذا
        'color_activity_level',
        'cognitive_question_answer',
    ];

    // العلاقة مع المريض (1..1)
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
