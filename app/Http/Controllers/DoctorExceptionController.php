<?php

namespace App\Http\Controllers;

use App\Models\DoctorException;
use Illuminate\Http\Request;

class DoctorExceptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'is_off' => ['required', 'boolean'],
            'start_time' => ['required_if:is_off,false', 'nullable', 'date_format:H:i'],
            'end_time' => ['required_if:is_off,false', 'nullable', 'date_format:H:i', 'after:start_time'],
        ]);

        $doctor = $request->get('doctor');

        $exception = DoctorException::query()->updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'date' => $request->date,
            ],
            [
                'is_off' => $request->is_off,
                'start_time' => $request->is_off ? null : $request->start_time,
                'end_time' => $request->is_off ? null : $request->end_time,
            ]
        );

        return response()->json([
            'message' => 'تنظیمات استثنا (تعطیلی/تغییر ساعت) با موفقیت ثبت شد.',
            'data' => $exception
        ]);
    }
}
