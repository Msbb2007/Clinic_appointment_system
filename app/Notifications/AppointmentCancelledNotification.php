<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;
    protected $cancelledBy;

    public function __construct(Appointment $appointment, string $cancelledBy = 'admin')
    {
        $this->appointment = $appointment;
        $this->cancelledBy = $cancelledBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    //داده‌هایی که در دیتابیس ذخیره می‌شود//
    public function toArray($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'date'           => $this->appointment->appointment_date,
            'time'           => $this->appointment->appointment_time,
            'message'        => "نوبت شما در تاریخ {$this->appointment->appointment_date} لغو شد.",
            'cancelled_by'   => $this->cancelledBy,
        ];
    }
}
