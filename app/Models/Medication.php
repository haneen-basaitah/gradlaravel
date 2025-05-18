<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medication extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id',
        'name',
        'time_of_intake',
        'frequency',
        'medicine_closet_number',
        'medicine_closet_location',
        'expiration_date',
        'pill_count',  // ✅ إضافة عدد الحبات
        'status',      // ✅ إضافة حالة الدواء

    ];
    protected $primaryKey = 'id';
    public function patient()
    {
        return $this->belongsTo(Patient::class); //  العلاقة مع المريض
    }

    public function closet()
{
    return $this->belongsTo(Closet::class);
}


}
