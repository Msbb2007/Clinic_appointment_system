<?php

use App\Jobs\SendAppointmentReminderJob;
use App\Models\Appointment;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// زمان‌بند برای ارسال یادآوری نوبت‌های امروز//
Schedule::call(function () {
    $today = now()->toDateString();

    // گرفتن تمام نوبت‌های تایید شده‌ی روز جاری//
    $appointments = Appointment::with(['patient.user', 'doctor.user'])
        ->whereDate('appointment_date', $today)
        ->where('status', 'pending') // فقط نوبت‌های فعال//
        ->get();

    foreach ($appointments as $appointment) {
        // ارسال هر کدام به صف (Queue) با صف  notifications//
        SendAppointmentReminderJob::dispatch($appointment)->onQueue('notifications');
    }
})->dailyAt('07:00'); // هر روز ساعت 7 صبح به طور خودکار اجرا شود//
