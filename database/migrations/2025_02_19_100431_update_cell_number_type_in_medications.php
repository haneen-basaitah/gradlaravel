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
            $table->integer('cell_number')->change(); // ✅ تغيير نوع الحقل إلى رقم

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->string('cell_number')->change(); // 🔄 إعادة الحقل إلى نص (إذا أردت التراجع)

        });
    }
};
