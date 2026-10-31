<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\AppointmentLog;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        // وقتی یک نوبت جدید ساخته می‌شود، اولین لاگ را ثبت می‌کنیم//
        AppointmentLog::query()->create([
            'appointment_id' => $appointment->id,
            'old_status'     => null,
            'new_status'     => $appointment->status,
            'description'    => "نوبت جدید با وضعیت {$appointment->status} ثبت شد.",
        ]);
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        //بررسی می‌کنیم آیا وضعیت (status) تغییر کرده است یا خیر//
        if ($appointment->isDirty('status')) {
            $oldStatus = $appointment->getOriginal('status');
            $newStatus = $appointment->status;

            // ثبت خودکار لاگ در جدول تاریخچه//
            AppointmentLog::query()->create([
                'appointment_id' => $appointment->id,
                'old_status'     => $oldStatus,
                'new_status'     => $newStatus,
                'description'    => "وضعیت نوبت از {$oldStatus} به {$newStatus} تغییر یافت.",
            ]);
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        // وقتی نوبت به صورت Soft Delete حذف می‌شود//
        AppointmentLog::query()->create([
            'appointment_id' => $appointment->id,
            'old_status'     => $appointment->status,
            'new_status'     => 'deleted',
            'description'    => "نوبت حذف (لغو موقت) گردید.",
        ]);
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        //
    }
}
