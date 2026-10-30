<?php

namespace App\Listeners;

use App\Events\DoctorWasRegistered;
use App\Models\User;
use App\Notifications\NewDoctorRegisteredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewDoctorNotificationToAdmins implements ShouldQueue
{
    public function handle(DoctorWasRegistered $event)
    {
        $admins = User::query()->where('role', 'admin')->get();

        // ارسال نوتیفیکیشن به همه ادمین‌ها//
        foreach ($admins as $admin) {
            $admin->notify(new NewDoctorRegisteredNotification($event->doctor));
        }
    }
}
