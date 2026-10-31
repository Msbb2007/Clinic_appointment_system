<?php

namespace App\Http\Controllers\Doctor_panel;

use App\Events\AppointmentCancelledByDoctor;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function cancelAppointmentByDoctor(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $appointment = Appointment::query()->where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        // بررسی وضعیت نوبت//
        if (in_array($appointment->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'این نوبت قابل لغو کردن نیست (قبلاً انجام شده یا لغو گردیده است).'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // تغییر وضعیت نوبت به لغو شده//
            $appointment->update([
                'status' => 'cancelled'
            ]);



            //ارسال نوتیفیکیشن به بیمار//
            event(new AppointmentCancelledByDoctor($appointment));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'نوبت با موفقیت توسط پزشک لغو شد.',
                'data'    => $appointment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی در لغو نوبت رخ داد.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function softDeleteAppointment(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $appointment = Appointment::query()->where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {

            $appointment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'نوبت به صورت softdelete از سیستم حذف شد.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی در حذف نوبت رخ داد.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function forceDeleteAppointment(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $appointment = Appointment::query()->where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            //ارسال نوتیفیکیشن به بیمار//
            event(new AppointmentCancelledByDoctor($appointment));

            $appointment->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'نوبت به صورت کامل و دائم از سیستم حذف شد.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی در حذف نوبت رخ داد.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
