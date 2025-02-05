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

            Schema::create('patients', function (Blueprint $table) {
                $table->id(); // المفتاح الأساسي
                $table->string('name'); // اسم المريض
                $table->integer('age'); // عمر المريض
                $table->text('medical_condition')->nullable(); // الحالة الصحية
                $table->text('notes')->nullable(); // ملاحظات إضافية
                $table->string('caregiver_email'); // المفتاح الأجنبي لجدول caregivers
                $table->timestamps();

                // تعريف المفتاح الأجنبي
                $table->foreign('caregiver_email')->references('email')->on('caregivers')->onDelete('cascade');
            });





    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
