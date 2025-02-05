<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Closet extends Model
{
    use HasFactory;

    protected $fillable = [
        'temperature',
        'humidity'
    ];

    // العلاقة مع الأدوية (1 إلى *)
    public function medications()
    {
        return $this->hasMany(Medication::class);
    }
}
