<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentCancelledByDoctorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'doctor_name'    => $this->appointment->doctor->user->full_name ?? 'پزشک',
            'date'           => $this->appointment->appointment_date,
            'message'        => 'نوبت شما توسط پزشک لغو گردید.',
            'created_at'     => now(),
        ];
    }
}
