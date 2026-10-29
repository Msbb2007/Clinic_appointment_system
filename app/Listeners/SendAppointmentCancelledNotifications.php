<?php

namespace App\Listeners;

use App\Events\AppointmentWasCancelled;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAppointmentCancelledNotifications
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentWasCancelled $event): void
    {
        $appointment = $event->appointment;

        // ارسال نوتیفیکیشن به بیمار//
        if ($appointment->patient && $appointment->patient->user) {
            $appointment->patient->user->notify(
                new AppointmentCancelledNotification($appointment, $event->cancelledBy)
            );
        }

        //ارسال نوتیفیکیشن به پزشک//
        if ($appointment->doctor && $appointment->doctor->user) {
            $appointment->doctor->user->notify(
                new AppointmentCancelledNotification($appointment, $event->cancelledBy)
            );
        }
    }
}
