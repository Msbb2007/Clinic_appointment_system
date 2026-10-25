<?php

namespace App\Http\Controllers\Patient_panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // دریافت پروفایل بیمار همراه با اطلاعات کاربر//
        $patient = Patient::with('user')->where('user_id', $user->id)->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'پروفایل بیمار یافت نشد. لطفاً ابتدا پروفایل خود را تکمیل کنید.'
            ], 404);
        }

        $appointmentsQuery = Appointment::query()->where('patient_id', $patient->id);

        // آمار کلی نوبت‌ها//
        $totalAppointments = (clone $appointmentsQuery)->count();
        $pendingAppointmentsCount = (clone $appointmentsQuery)->where('status', 'pending')->count();
        $completedAppointmentsCount = (clone $appointmentsQuery)->where('status', 'completed')->count();

        //پیدا کردن نوبت بعدی (نزدیک‌ترین نوبت فعال پیش‌رو)//
        $today = Carbon::today()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i');

        $nextAppointment = (clone $appointmentsQuery)
            ->with(['doctor.user', 'doctor.specialty'])
            ->where('status', 'pending' )
            ->where(function ($q) use ($today, $currentTime) {
                $q->where('appointment_date', '>', $today)
                    ->orWhere(function ($sub) use ($today, $currentTime) {
                        $sub->where('appointment_date', $today)
                            ->where('start_time', '>=', $currentTime);
                    });
            })
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        //  آخرین نوبت‌های ثبت‌شده (تاریخچه اخیر برای نمایش در جدول یا لیست)//
        $recentAppointments = (clone $appointmentsQuery)
            ->with(['doctor.user', 'doctor.specialty'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get()->
            transform(function ($appointment) {
                   return [
                             'id' => $appointment->id,
                             'doctor_name' => $appointment->doctor?->full_name ?? 'دکتر',
                             'specialty' => $appointment->doctor?->specialty?->name ?? 'متفرقه',
                             'appointment_date' => $appointment->appointment_date,
                             'start_time' => $appointment->start_time,
                             'end_time' => $appointment->end_time,
                             'status' => $appointment->status, // completed, pending, cancelled //
                             'fee' => $appointment->doctor->consultation_fee ?? 0,
                             'created_at' => $appointment->created_at?->toDateTimeString(),
                   ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'patient_info' => [
                    'full_name' => $patient->full_name,
                    'national_id' => $patient->national_id,
                    'phone' => $patient->user->phone ?? null,
                    'email' => $patient->user->email ?? null,
                ],
                'stats' => [
                    'total' => $totalAppointments,
                    'pending' => $pendingAppointmentsCount,
                    'completed' => $completedAppointmentsCount,
                ],
                'next_appointment' => $nextAppointment ? [
                    'id' => $nextAppointment->id,
                    'doctor_name' => $nextAppointment->doctor->full_name ?? 'دکتر',
                    'specialty' => $nextAppointment->doctor->specialty->name ?? 'متفرقه',
                    'date' => $nextAppointment->appointment_date,
                    'start_time' => $nextAppointment->start_time,
                    'status' => $nextAppointment->status,
                ] : null,
                'recent_appointments' => $recentAppointments
            ]
        ]);
    }
}
