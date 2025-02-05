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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->unique(); // علاقة 1..1 مع المريض
            $table->time('color_activity_level'); // وقت نشاط الروبوت
            $table->time('cognitive_question_answer'); // وقت نشاط الألوان
            $table->timestamps();

            // المفتاح الأجنبي
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
