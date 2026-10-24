<?php

namespace App\Http\Controllers\Patient_panel;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientProfileController extends Controller
{
    //ثبت یا تکمیل مشخصات بیمار برای اولین بار//
    public function storeOrUpdate(Request $request)
    {
        $user = auth()->user();

        $patientId = $user->patient ? $user->patient->id : null;

        $request->validate([
            'national_id' => 'required|string|size:10|',
            'full_name' => 'required|string|max:255',
        ]);

        $patient = Patient::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'national_id' => $request->national_id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'پروفایل بیمار با موفقیت ثبت شد.',
            'data' => $patient
        ]);
    }

    //دریافت اطلاعات پروفایل بیمار جاری//
    public function show()
    {
        $patient = auth()->user()->patient()->with('user')->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'پروفایل بیمار برای این حساب کاربری یافت نشد.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }
}
