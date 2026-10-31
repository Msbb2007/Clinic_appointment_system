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
        Schema::table('appointments', function (Blueprint $table) {
            // ایجاد Unique Index ترکیبی برای جلوگیری از تداخل نوبت‌ها//
            $table->unique(['doctor_id', 'appointment_date', 'start_time'], 'unique_doctor_time_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('unique_doctor_time_slot');
        });
    }
};
