<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Mail\AppointmentReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $appointment;

    public $tries = 3;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function handle()
    {
        if ($this->appointment->patient && $this->appointment->patient->user) {
            $user = $this->appointment->patient->user;

            if ($user->email) {
                Mail::to($user->email)->send(new AppointmentReminderMail($this->appointment));
            }
        }
    }
}
