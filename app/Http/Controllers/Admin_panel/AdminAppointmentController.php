<?php

namespace App\Http\Controllers\Admin_panel;

use App\Events\AppointmentWasCancelled;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAppointmentController extends Controller
{
    //لیست تمام نوبت‌های سیستم همراه با فیلترها//
    public function index(Request $request)
    {
        $query = Appointment::with(['patient.user', 'doctor.user', 'doctor.specialty']);

        // فیلتر بر اساس وضعیت نوبت (pending, completed, cancelled)//
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        //فیلتر بر اساس پزشک خاص//
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // فیلتر بر اساس بیمار خاص//
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // فیلتر بر اساس تاریخ خاص//
        if ($request->has('date')) {
            $query->where('appointment_date', $request->date);
        }

        // مرتب‌سازی بر اساس جدیدترین نوبت‌ها//
        $query->latest();

        $perPage = $request->get('per_page', 15);
        $appointments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $appointments
        ]);
    }

    //  لغو نوبت توسط ادمین //
    public function cancelAppointment(Request $request, Appointment $appointment)
    {
        // اگر نوبت قبلاً تکمیل یا لغو شده باشد
        if (in_array($appointment->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'این نوبت قبلاً تکمیل یا لغو شده است و قابل تغییر نیست.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $appointment->update([
                'status' => 'cancelled'
            ]);

            //فراخوانی ایونت برای اجرای پیامک//
            event(new AppointmentWasCancelled($appointment, 'admin'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'نوبت مورد نظر توسط مدیریت لغو شد و اعلان‌ها ارسال گردید.',
                'data'    => $appointment->load(['patient.user', 'doctor.user'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی رخ داد.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
