<?php

namespace App\Http\Controllers\Doctor_panel;

use App\Http\Controllers\Controller;
use App\Models\DoctorException;
use Illuminate\Http\Request;

class DoctorExceptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'is_off' => ['required'],
            'start_time' => ['required_if:is_off,false', 'nullable', 'date_format:H:i'],
            'end_time' => ['required_if:is_off,false', 'nullable', 'date_format:H:i', 'after:start_time'],
            'slot_duration_minutes' => ['required','integer', 'min:5'],
        ]);

        $doctor = $request->get('doctor');

        $exception = DoctorException::query()->updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'date' => $request->date,
            ],
            [
                'is_off' => $request->is_off,
                'start_time' => $request->start_time,
                'end_time' =>  $request->end_time,
                'slot_duration_minutes' => $request->is_off ? null : $request->slot_duration_minutes,
            ]
        );

        return response()->json([
            'message' => 'تنظیمات استثنا (تعطیلی/تغییر ساعت) با موفقیت ثبت شد.',
            'data' => $exception
        ]);
    }

    public function index(Request $request)
    {
        $doctor=$request->get('doctor');

        $exceptions = DoctorException::query()->where('doctor_id', $doctor->id)->get();
        return response()->json([
            'data' => $exceptions
        ]);
    }

    public function show(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $exception = DoctorException::query()
            ->where('doctor_id', $doctor->id)
            ->where('id', $id)
            ->first();

        if (! $exception) {
            return response()->json([
                'message' => 'استثنای مورد نظر یافت نشد.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $exception
        ]);
    }

    public function update(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $exception = DoctorException::query()
            ->where('doctor_id', $doctor->id)
            ->where('id', $id)
            ->first();

        if (! $exception) {
            return response()->json([
                'message' => 'استثنای مورد نظر یافت نشد.'
            ], 404);
        }

        $request->validate([
            'date' => ['sometimes', 'date'],
            'is_off' => ['sometimes', 'boolean'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'slot_duration_minutes' => ['nullable', 'integer', 'min:5'],
        ]);

        $isOff = $request->has('is_off') ? $request->is_off : $exception->is_off;

        $exception->update([
            'date' => $request->date ?? $exception->date,
            'is_off' => $isOff,
            'start_time' => $isOff ? null : ($request->start_time ?? $exception->start_time),
            'end_time' => $isOff ? null : ($request->end_time ?? $exception->end_time),
            'slot_duration_minutes' => $isOff ? null : ($request->slot_duration_minutes ?? $exception->slot_duration_minutes),
        ]);

        return response()->json([
            'message' => 'استثنای زمان‌بندی با موفقیت ویرایش شد.',
            'data' => $exception
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $doctor = $request->get('doctor');

        $exception = DoctorException::query()
            ->where('doctor_id', $doctor->id)
            ->where('id', $id)
            ->first();

        if (! $exception) {
            return response()->json([
                'message' => 'استثنای مورد نظر یافت نشد.'
            ], 404);
        }

        $exception->delete();

        return response()->json([
            'message' => 'استثنای زمان‌بندی با موفقیت حذف شد.'
        ]);
    }
}
