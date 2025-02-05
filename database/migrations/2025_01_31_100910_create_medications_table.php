<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id(); // مفتاح أساسي تلقائي
            $table->unsignedBigInteger('patient_id'); // ربط بالأطباء باستخدام ID
            $table->string('name'); // اسم الدواء
            $table->string('dosage'); // الجرعة
            $table->string('frequency'); // عدد الجرعات
            $table->string('time_of_intake'); // وقت الاستخدام
            $table->string('medicine_closet_number'); // رقم الخزانة
            $table->string('medicine_closet_location'); // موقع الدواء في الخزانة
            $table->date('expiration_date'); // تاريخ الانتهاء
            $table->timestamps();

            // المفتاح الأجنبي يربط `patient_id` بجدول `patients`
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
