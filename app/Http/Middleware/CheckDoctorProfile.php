<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckDoctorProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'doctor') {
            return response()->json(['message' => 'دسترسی غیرمجاز.این بخش مربوط به پزشک است'], 403);
        }

        $doctor = $user->doctor;

        if (! $doctor) {
            return response()->json(['message' => 'پروفایل پزشکی برای این کاربر یافت نشد.'], 404);
        }

        if ($doctor->verification_status == 'pending') {
            return response()->json(['message' => 'اطلاعات پزشک هنوز تایید نشده است.دسترسی غیرمجاز!'], 403);
        }

        if ($doctor->verification_status == 'rejected') {
            return response()->json(['message' => 'اطلاعات پزشک ریجکت شده است.دسترسی غیرمجاز!'], 403);
        }

        //--قرار دادن پزشک در ریکوئست برای دسترسی راحت‌تر در کنترلر--//
        $request->merge(['doctor' => $doctor]);

        return $next($request);
    }
}
