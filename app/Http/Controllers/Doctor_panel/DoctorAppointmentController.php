<?php

namespace App\Http\Controllers\Doctor_panel;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DoctorAppointmentController extends Controller
{
    //  مشاهده لیست نوبت‌های پزشک (با قابلیت فیلتر بر اساس تاریخ) //
    public function index(Request $request)
    {
        $doctor = $request->get('doctor');

        $query = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->with('patient'); //اطلاعات بیمار//

        //اگر تاریخ خاصی ارسال شده بود، فیلتر کن//
        if ($request->has('date')) {
            $query->where('appointment_date', $request->date);
        }

        $appointments = $query->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'massage' => 'success',
            'data' => $appointments
        ]);
    }

    //  تغییر status نوبت (مثلاً لغو کردن یا انجام شده) //
    public function updateStatus(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $request->validate([
            'status' => ['required', 'in:pending,cancelled,completed']
        ]);

        $appointment = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('id', $id)
            ->first();

        if (! $appointment) {
            return response()->json([
                'message' => 'نوبت مورد نظر یافت نشد.'
            ], 404);
        }

        $appointment->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'وضعیت نوبت با موفقیت به‌روزرسانی شد.',
            'data' => $appointment
        ]);
    }
}
