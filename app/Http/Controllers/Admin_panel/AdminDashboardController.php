<?php

namespace App\Http\Controllers\Admin_panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
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
                ]
            ]
        ]);
    }
}
