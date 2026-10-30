<?php

namespace App\Listeners;

use App\Events\DoctorWasVerified;
use App\Mail\DoctorVerifiedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDoctorVerifiedEmail implements ShouldQueue
{
    public function handle(DoctorWasVerified $event)
    {
        $doctor = $event->doctor;

        // بررسی وجود کاربر و ایمیل معتبر//
        if ($doctor->user && $doctor->user->email) {
            Mail::to($doctor->user->email)->send(new DoctorVerifiedMail($doctor));
        }
    }
}
