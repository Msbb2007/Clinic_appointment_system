<?php

namespace App\Listeners;

use App\Events\AppointmentCancelledByDoctor;
use App\Notifications\AppointmentCancelledByDoctorNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPatientCancellationNotice implements ShouldQueue
{
    public function handle(AppointmentCancelledByDoctor $event)
    {
        $appointment = $event->appointment;

        if ($appointment->patient && $appointment->patient->user) {
            $user = $appointment->patient->user;

            $user->notify(new AppointmentCancelledByDoctorNotification($appointment));
        }
    }
}
