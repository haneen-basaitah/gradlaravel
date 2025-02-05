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
        Schema::create('closets', function (Blueprint $table) {
            $table->id(); // المفتاح الأساسي
            $table->float('temperature'); // درجة الحرارة
            $table->float('humidity'); // نسبة الرطوبة
            $table->timestamps();
        });

        // إضافة عمود `closet_id` في جدول `medications`
        Schema::table('medications', function (Blueprint $table) {
            $table->unsignedBigInteger('closet_id')->nullable(); // يمكن أن يكون الدواء بدون خزانة في البداية
            $table->foreign('closet_id')->references('id')->on('closets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closets');

        Schema::table('medications', function (Blueprint $table) {
            $table->dropForeign(['closet_id']);
            $table->dropColumn('closet_id');
        });
    }
};
