<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// چونQueueable هست باید اون صف رو با  'php artisan queue:work' توی ترمینال اجرا کنیم //
class NotificationController extends Controller
{
    //دریافت لیست تمام اعلان‌های کاربر (چه بیمار چه پزشک)//
    public function index(Request $request)
    {
        $user = $request->user(); // کاربری که لاگین کرده (بیمار یا پزشک) //

        //دریافت اعلان‌ها//
        $notifications = $user->unreadNotifications()->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $notifications
        ]);
    }

    //  علامت‌گذاری یک اعلان به عنوان "خوانده شده" //
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'اعلان مورد نظر یافت نشد.'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'اعلان به عنوان خوانده شده علامت‌گذاری شد.'
        ]);
    }

    //علامت‌گذاری همه اعلان‌ها به عنوان خوانده شده//
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تمام اعلان‌ها به عنوان خوانده شده علامت‌گذاری شدند.'
        ]);
    }
}
