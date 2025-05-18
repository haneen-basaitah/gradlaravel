<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caregiver extends Model
{


    protected $fillable = [
        'email', // الحقل الخاص بالبريد الإلكتروني
        'name',  // اسم مقدم الرعاية
        
    ];
 // علاقة مع المرضى
 public function patients()
 {
     return $this->hasMany(Patient::class, 'caregiver_email', 'email');
 }

}
