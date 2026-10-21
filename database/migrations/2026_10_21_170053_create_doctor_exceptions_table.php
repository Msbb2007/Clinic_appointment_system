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
        Schema::create('doctor_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->date('date'); // تاریخ مشخص استثنا (مثل 2026-10-25)
            $table->boolean('is_off')->default(false); // آیا این روز کلاً تعطیل است؟
            $table->time('start_time')->nullable(); // ساعت شروع جدید (اگر ساعت تغییر کرده باشد)
            $table->time('end_time')->nullable(); // ساعت پایان جدید
            $table->timestamps();

            // هر پزشک در هر روز فقط یک رکورد استثنا می‌تواند داشته باشد //
            $table->unique(['doctor_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_exceptions');
    }
};
