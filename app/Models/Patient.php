<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age',
        'closet_id',
        'caregiver_email'
    ];

    protected $primaryKey = 'id'; //  إعادة id كمفتاح أساسي
    public $incrementing = true; // جعله تلقائي التزايد
    protected $keyType = 'int'; //  التأكد من أنه عدد صحيح
  // علاقة مع Caregiver
  public function caregiver()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  //علاقه مع ال medication
  public function medication(){
    return $this->hasMany(Medication::class);
  }

  public function activity()
{
    return $this->hasOne(Activity::class);
}
}
