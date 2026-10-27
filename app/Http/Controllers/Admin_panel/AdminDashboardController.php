<?php

namespace App\Http\Controllers\Admin_panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $today = Carbon::today()->toDateString();

        // آمار کلی سیستم
        $totalUsers = User::query()->count();
        $totalPatients = Patient::query()->count();
        $totalDoctors = Doctor::query()->count();

        // تفکیک پزشکان بر اساس وضعیت تایید//
        $pendingDoctorsCount = Doctor::query()->where('verification_status', 'pending')->count();
        $approvedDoctorsCount = Doctor::query()->where('verification_status', 'approved')->count();
        $rejectedDoctorsCount = Doctor::query()->where('verification_status', 'rejected')->count();

        //آمار نوبت‌ها//
        $totalAppointments = Appointment::query()->count();
        $completedAppointments = Appointment::query()->where('status', 'completed')->count();
        $activeAppointments = Appointment::query()->where('status', 'pending')->count();
        $cancelledAppointments = Appointment::query()->where('status', 'cancelled')->count();

        //  آمار و روندهای ماه جاری (Monthly Trends)//
        $newUsersThisMonth = User::query()->where('created_at', '>=', $startOfMonth)->count();
        $appointmentsThisMonth = Appointment::query()->where('created_at', '>=', $startOfMonth)->count();

        //  محبوب‌ترین تخصص‌ها (۵ تخصص برتر بر اساس تعداد نوبت‌ها)//
        $topSpecialties = Specialty::query()->withCount('doctors')
            ->orderBy('doctors_count', 'desc')
            ->take(5)
            ->get(['id', 'name']);

        //  فعال‌ترین پزشکان (۵ پزشک برتر بر اساس تعداد نوبت‌های تکمیل‌شده)//
        $topDoctors = Doctor::with(['user:email,phone', 'specialty:id,name'])
            ->withCount(['appointments' => function ($q) {
                $q->whereIN('status', ['completed','pending']);
            }])
            ->orderBy('appointments_count', 'desc')
            ->take(5)
            ->get();

        //   نوبت‌های ثبت‌شده امروز در سیستم (فعالیت‌های زنده)//
        $recentAppointments = Appointment::with([
            'patient.user:email,phone',
            'doctor.user:email,phone',
            'doctor.specialty:id,name'
        ])
            ->whereDate('appointment_date', $today)
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'counts' => [
                    'total_users' => $totalUsers,
                    'total_patients' => $totalPatients,
                    'total_doctors' => $totalDoctors,
                    'pending_doctors' => $pendingDoctorsCount,
                    'approved_doctors' => $approvedDoctorsCount,
                    'rejected_doctors' => $rejectedDoctorsCount,
                    'total_appointments' => $totalAppointments,
                    'active_appointments' => $activeAppointments,
                    'completed_appointments' => $completedAppointments,
                    'cancelled_appointments' => $cancelledAppointments,
                ],
                'monthly_insights' => [
                    'new_users_this_month'    => $newUsersThisMonth,
                    'appointments_this_month' => $appointmentsThisMonth,
                ],
                'analytics' => [
                    'top_specialties'     => $topSpecialties,
                    'top_doctors'         => $topDoctors,
                    'recent_appointments' => $recentAppointments,
                ]
            ]
        ]);
    }
}
