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
        Schema::table('medications', function (Blueprint $table) {
            $table->integer('pill_count')->default(0); // عدد الحبات المتاحة
            $table->string('status')->default('not taken'); // حالة الدواء
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
        $table->dropColumn('pill_count');
        $table->dropColumn('status');
        });
    }
};
