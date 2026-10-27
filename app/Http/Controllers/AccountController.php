<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // بررسی اینکه آیا کاربر نوبت فعال (در انتظار یا تایید شده) دارد یا خیر//
        $hasActiveAppointments = false;

        if ($user->role === 'patient' && $user->patient) {
            $hasActiveAppointments = Appointment::query()->where('patient_id', $user->patient->id)
                ->whereIn('status', 'pending')
                ->exists();
        } elseif ($user->role === 'doctor' && $user->doctor) {
            $hasActiveAppointments = Appointment::query()->where('doctor_id', $user->doctor->id)
                ->whereIn('status', 'pending')
                ->exists();
        }

        if ($hasActiveAppointments) {
            return response()->json([
                'success' => false,
                'message' => 'شما دارای نوبت‌های فعال هستید. ابتدا باید آن‌ها را لغو کنید و سپس اقدام به حذف حساب کاربری نمایید.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            //حذف تمام توکن‌های ورود کاربر (خروج اجباری از همه دستگاه‌ها)//
            $user->tokens()->delete();

            //اگر پروفایل پزشک یا بیمار هم دارد، آن‌ها را هم می‌توان Soft Delete کرد//
            if ($user->role === 'patient' && $user->patient) {
                $user->patient->delete();
            } elseif ($user->role === 'doctor' && $user->doctor) {
                // اگر پزشک است، اسلات‌ها یا تنظیماتش هم بسته می‌شود//
                $user->doctor->delete();
            }

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'حساب کاربری شما با موفقیت حذف شد.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'خطایی در حذف حساب کاربری رخ داد.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
