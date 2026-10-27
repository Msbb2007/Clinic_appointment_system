<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ثبت‌نام کاربر جدید (بیمار)//
    public function register(RegisterRequest $request)
    {
        $user = User::query()->create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'patient',
        ]);

        $token = $user->createToken('clinic_token')->plainTextToken;

        return response()->json([
            'message' => 'ثبت‌ نام با موفقیت انجام شد.',
            'data' => $user,
            'token' => $token
        ], 201);
    }

    // ثبت‌نام پزشک جدید//
    public function doctorRegister(DoctorRegisterRequest $request){
        $user = User::query()->create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'doctor',
        ]);

        $doctor = Doctor::query()->create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'medical_license_number' => $request->medical_license_number,
            'bio' => $request->bio,
            'specialty_id' => $request->specialty_id,
            'consultation_fee' => $request->consultation_fee,
            'verification_status' => 'pending',
        ]);


        $token = $doctor->createToken('clinic_token')->plainTextToken;

        return response()->json([
            'message' => 'ثبت‌ نام پزشک انجام شد.منتظر تایید اطلاعات توسط ادمین باشید',
            'data' => $doctor,
            'token' => $token
        ],201);
    }

    // ورود به سیستم//
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required',],
            'password' => ['required'],
        ]);

        $user = User::query()->where('username', $request->username)->first();

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حساب کاربری شما توسط مدیر سیستم غیرفعال (مسدود) شده است.'
            ], 403);
        }

        if ($user->role == 'doctor') {

            $doctor = $user->doctor()->first();

            if (! $doctor) {
                throw ValidationException::withMessages([
                    'username' => ['پروفایل پزشک یافت نشد.'],
                ]);
            }

            if ($doctor->verification_status == 'pending') {
                throw ValidationException::withMessages([
                    'verification_status' => ['اطلاعات شما هنوز توسط ادمین بررسی نشده است']
                ]);
            }

            if ($doctor->verification_status == 'rejected') {
                throw ValidationException::withMessages([
                    'verification_status' => ['اطلاعات شما توسط ادمین بررسی شده و رد شده است']
                ]);
            }
        }

        $token = $user->createToken('clinic_token')->plainTextToken;

        return response()->json([
            'message' => 'ورود موفقیت‌آمیز بود.',
            'user' => $user,
            'token' => $token
        ]);
    }

    // خروج از سیستم (باطل کردن توکن) //
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'با موفقیت از سیستم خارج شدید.'
        ]);
    }
}
