<?php

namespace App\Http\Controllers\Patient_panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\DoctorAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientAppointmentController extends Controller
{
    //ثبت نوبت جدید توسط بیمار//
    public function store(Request $request, DoctorAvailabilityService $availabilityService)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $doctorId = $request->doctor_id;
        $date = $request->appointment_date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;

        $patient = auth()->user()->patient; //بیماری که ثبت نام کرده//

        $doctor = Doctor::query()->findOrFail($doctorId);

        if ($doctor->verification_status=='pending'||$doctor->verification_status=='rejected') {
            return response()->json([
                'success' => false,
                'message' => 'پروفایلی برای این پزشک یافت نشد.'
            ]);
        }

        $schedule = $availabilityService->getDoctorSlots($doctor);

        $daySchedule = collect($schedule)->firstWhere('date', $date);

        if (!$daySchedule) {
            return response()->json([
                'error' => 'unsuccess',
                'message' => 'پزشک در این تاریخ روز کاری یا شیفت فعالی ندارد.',
            ], 422);
        }

        //بررسی وجود دقیق اسلات در لیست اسلات‌های آن روز//
        $slotExists = collect($daySchedule['slots'])->contains(function ($slot) use ($startTime, $endTime) {
            return $slot['start_time'] === $startTime && $slot['end_time'] === $endTime;
        });

        if (!$slotExists) {
            return response()->json([
                'success' => false,
                'message' => 'این ساعت معتبر نیست یا منقضی شده است.'
            ], 422);
        }

        //  بررسی اینکه آیا این اسلات قبلاً رزرو شده است یا خیر//
        $isAlreadyBooked = Appointment::query()->where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('start_time', $startTime)
            ->whereIn('status', ['pending', 'completed']) // نوبت‌های رزرو شده یا در تکمیل شده//
            ->exists();

        if ($isAlreadyBooked) {
            return response()->json([
                'success' => false,
                'message' => 'این تایم قبلاً توسط بیمار دیگری رزرو شده است.'
            ], 409);
        }

        // ثبت نوبت نهایی//
        $appointment = Appointment::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'نوبت شما با موفقیت رزرو شد.',
            'data' => $appointment
        ], 201);
    }

    // مشاهده لیست نوبت‌های بیمار لاگین‌شده//
    public function index(Request $request)
    {
        $patient = auth()->user()->patient;

        $appointments = Appointment::with(['doctor.user', 'doctor.specialty'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(10);

        // سفارشی‌سازی خروجی برای خوانایی بهتر در پنل بیمار//
        $appointments->getCollection()->transform(function ($appointment) {
            return [
                'id' => $appointment->id,
                'doctor_name' => $appointment->doctor?->full_name ?? 'دکتر',
                'specialty' => $appointment->doctor?->specialty?->name ?? 'متفرقه',
                'appointment_date' => $appointment->appointment_date,
                'start_time' => $appointment->start_time,
                'end_time' => $appointment->end_time,
                'status' => $appointment->status, // completed, pending, cancelled //
                'fee' => $appointment->doctor->consultation_fee,
                'created_at' => $appointment->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

   //  لغو نوبت توسط بیمار //
    public function cancel($id)
    {
        $patient = auth()->user()->patient;

        $appointment = Appointment::where('id', $id)
            ->where('patient_id', $patient->id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'نوبت مورد نظر یافت نشد یا متعلق به شما نیست.'
            ], 404);
        }

        if ($appointment->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'این نوبت از قبل لغو شده است.'
            ], 422);
        }

        if ($appointment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'امکان لغو نوبتی که انجام شده (تکمیل شده) وجود ندارد.'
            ], 422);
        }

        // بررسی اینکه آیا زمان نوبت گذشته است یا خیر//
        $appointmentDateTime = Carbon::parse($appointment->appointment_date . ' ' . $appointment->start_time);
        if ($appointmentDateTime->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'امکان لغو نوبت‌های گذشته وجود ندارد.'
            ], 422);
        }

        //تغییر وضعیت به لغو شده//
        $appointment->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'نوبت شما با موفقیت لغو شد و نوبت آن آزاد گردید.'
        ]);
    }
}
