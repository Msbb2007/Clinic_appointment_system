<?php

namespace App\Http\Controllers\Doctor_panel;

use App\Http\Controllers\Controller;
use App\Models\DoctorAvailability;
use Illuminate\Http\Request;

class DoctorAvailabilityController extends Controller
{
    public function store(DoctorAvailability $request)
    {

        // صحت سنجی در middleware //
        $doctor = $request->get('doctor');

        if (! $doctor) {
            return response()->json([
                'message' => 'پروفایل پزشکی برای این کاربر یافت نشد.'
            ], 404);
        }

        $availability = DoctorAvailability::query()->create([
            'doctor_id' => $doctor->id,
            'rrule' => $request->rrule,
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
}
