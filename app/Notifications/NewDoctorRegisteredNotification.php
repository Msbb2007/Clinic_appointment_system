<?php

namespace App\Notifications;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewDoctorRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $doctor;

    public function __construct(Doctor $doctor)
    {
        $this->doctor = $doctor;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        // اطلاعاتی که ادمین برای بررسی نیاز دارد
        return [
            'doctor_id'   => $this->doctor->id,
            'doctor_name' => $this->doctor->full_name ?? 'پزشک جدید',
            'message'     => "پزشک جدیدی با نام {$this->doctor->full_name} ثبت‌نام کرده و نیازمند تایید است.",
            'created_at'  => now(),
        ];
    }
}
