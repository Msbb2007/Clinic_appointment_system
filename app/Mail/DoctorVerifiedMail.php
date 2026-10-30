<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DoctorVerifiedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $doctor;

    public function __construct(Doctor $doctor)
    {
        $this->doctor = $doctor;
    }

    public function build()
    {
        return $this->subject('حساب کاربری شما توسط ادمین بررسی شد ');
        //ساختن قالب ویو و فرستادن بقیه اصلاعات//
    }
}
