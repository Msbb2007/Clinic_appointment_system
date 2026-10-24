<?php

namespace App\Http\Controllers\Doctor_panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorDashboardController extends Controller
{
    //داشبورد آماری پزشک (آمار امروز و وضعیت‌ها)//
    public function stats(Request $request)
    {
        $doctor = $request->get('doctor');
        $today = now()->toDateString(); //تاریخ امروز (میلادی)//

        //تعداد نوبت‌های امروز//
        $totalAppointmentsToday = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $today)
            ->count();

        // نوبت‌های در انتظار امروز//
        $pendingAppointmentsToday = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $today)
            ->where('status', 'pending')
            ->count();

        //نوبت‌های ویزیت شده امروز//
        $completedAppointmentsToday = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        //نوبت‌های کنسل شده امروز//
        $canceledAppointmentsToday = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $today)
            ->where('status', 'canceled')
            ->count();

        //تعداد قوانین زمان‌بندی ثبت شده توسط پزشک//
        $totalAvailabilitiesRules = DoctorAvailability::query()
            ->where('doctor_id', $doctor->id)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'doctor_name' => $doctor->full_name ?? 'پزشک محترم',
                'today_date' => $today,
                'stats' => [
                    'total_appointments_today' => $totalAppointmentsToday,
                    'pending_appointments_today' => $pendingAppointmentsToday,
                    'completed_appointments_today' => $completedAppointmentsToday,
                    'canceled_appointments_today' => $canceledAppointmentsToday,
                    'total_availabilities_rules' => $totalAvailabilitiesRules,
                ]
            ]
        ]);
    }

   // دریافت اطلاعات پروفایل پزشک //
    public function getProfile(Request $request)
    {
        $doctor = $request->get('doctor');
        $doctor->load('user:id,username,email,password,phone'); // لود کردن اطلاعات یوزر مربوطه//

        return response()->json([
            'massage' => 'success',
            'data' => $doctor
        ]);
    }

    // ویرایش پروفایل پزشکی//
    public function updateProfile(Request $request)
    {
        $doctor = $request->get('doctor');

        $request->validate([
            'username' => ['required', 'string', 'max:255','unique:users,username,'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'],
            'password' => ['required', 'string', 'min:8'],
            'phone'=> ['required', 'string', 'max:255' , 'unique:users,phone,'],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string'],
            'consultation_fee' => ['sometimes', 'numeric', 'min:0'],
        ]);

        // آپدیت اطلاعات کاربر در جدول users اگر ارسال شده باشد//
        if ($request->has('username')) {
            $doctor->user()->update([
                'username' => $request->name
            ]);
        }

        if ($request->has('email')) {
            $doctor->user()->update([
                'email' => $request->email
            ]);
        }
        if ($request->has('password')) {
            $doctor->user()->update([
                'password' => Hash::make($request->password)
            ]);
        }
        if ($request->has('phone')) {
            $doctor->user()->update([
                'phone' => $request->phone
            ]);
        }


        //آپدیت اطلاعات تخصصی پزشک در جدول doctors//
        $doctor->update($request->only([
            'full_name',
            'bio',
            'consultation_fee'
        ]));

        $doctor->load('user:id,username,email,password,phone');

        return response()->json([
            'message' => 'پروفایل پزشکی با موفقیت به‌روزرسانی شد.',
            'data' => $doctor
        ]);
    }
}
