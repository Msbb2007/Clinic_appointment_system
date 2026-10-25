<?php

namespace App\Http\Controllers\Doctor_panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorAvailabilityRequest;
use App\Models\DoctorAvailability;
use App\Services\DoctorAvailabilityService;
use Illuminate\Http\Request;

class DoctorAvailabilityController extends Controller
{
    public function store(DoctorAvailabilityRequest $request)
    {

        // صحت سنجی در middleware //
        $doctor = $request->get('doctor');

        if (! $doctor) {
            return response()->json([
                'message' => 'پروفایل پزشکی برای این کاربر یافت نشد.'
            ], 404);
        }

        // بررسی تکراری بودن قانون//
        $exists = DoctorAvailability::query()
            ->where('doctor_id', $doctor->id)
            ->where('rrule', $request->rrule)
            ->where('start_date', $request->start_date)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'این قانون زمان‌بندی با همین مشخصات و تاریخ شروع قبلاً ثبت شده است.'
            ], 422);
        }

        $availability = DoctorAvailability::query()->create([
            'doctor_id' => $doctor->id,
            'rrule' => $request->rrule,//FREQ=WEEKLY;BYDAY=SA,MO//باید به این فرم دربیاید
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'slot_duration_minutes' => $request->slot_duration_minutes,
        ]);

        return response()->json([
            'message' => 'زمان‌بندی کاری با موفقیت ثبت شد.',
            'data' => $availability
        ], 201);
    }

    public function index(Request $request)
    {
        $doctor=$request->get('doctor');

        $availabilities = DoctorAvailability::query()->where('doctor_id', $doctor->id)->get();

        return response()->json([
            'data' => $availabilities
        ]);
    }

    //مشاهده یک rrule خاص//
    public function show(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $availability = DoctorAvailability::query()
            ->where('doctor_id', $doctor->id)
            ->where('id', $id)
            ->first();

        if (! $availability) {
            return response()->json([
                'message' => 'قانون زمان‌بندی مورد نظر یافت نشد.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $availability
        ]);
    }

    //  ویرایش قانون خاص (Update)//
    public function update(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $availability = DoctorAvailability::query()
            ->where('doctor_id', $doctor->id)
            ->where('id', $id)
            ->first();

        if (! $availability) {
            return response()->json([
                'message' => 'قانون زمان‌بندی مورد نظر یافت نشد.'
            ], 404);
        }

        $availability->update([
            'rrule' => $request->rrule ?? $availability->rrule,
            'start_date' => $request->start_date ?? $availability->start_date,
            'end_date' => $request->end_date ?? $availability->end_date,
            'start_time' => $request->start_time ?? $availability->start_time,
            'end_time' => $request->end_time ?? $availability->end_time,
            'slot_duration_minutes' => $request->slot_duration_minutes ?? $availability->slot_duration_minutes,
        ]);

        return response()->json([
            'message' => 'زمان‌بندی کاری با موفقیت ویرایش شد.',
            'data' => $availability
        ]);
    }

    //  حذف قانون (Destroy) //
    public function destroy(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $availability = DoctorAvailability::query()
            ->where('doctor_id', $doctor->id)
            ->where('id', $id)
            ->first();

        if (! $availability) {
            return response()->json([
                'message' => 'قانون زمان‌بندی مورد نظر یافت نشد.'
            ], 404);
        }

        $availability->delete();

        return response()->json([
            'message' => 'زمان‌بندی کاری با موفقیت حذف شد.'
        ]);
    }

    public function slots(Request $request, DoctorAvailabilityService $service)
    {
        // مستقیماً پزشک را از ریکوئستی که میدل‌ور آماده کرده می‌گیریم//
        $doctor = $request->get('doctor');

        $schedule = $service->getDoctorSlots($doctor);

        if ($request->has('status')) {
            $statusFilter = $request->status; // مثلاً 'booked'

            foreach ($schedule as &$day) {
                $day['slots'] = array_values(array_filter($day['slots'], function ($slot) use ($statusFilter) {
                    return $slot['status'] === $statusFilter;
                }));
            }
            unset($day);

            // حذف روزهایی که هیچ اسلاتی با وضعیت درخواست‌شده ندارند//
            $schedule = array_values(array_filter($schedule, fn($day) => !empty($day['slots'])));
        }

        return response()->json([
            'doctor' => $doctor->full_name,
            'schedule' => $schedule
        ]);
    }
}
