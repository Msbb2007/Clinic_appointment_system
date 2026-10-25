<?php

namespace App\Http\Controllers\Patient_panel;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PatientProfileController extends Controller
{
    //ثبت یا تکمیل مشخصات بیمار برای اولین بار//
    /**
     * @throws \Throwable
     */
    public function storeOrUpdate(Request $request)
    {
        $user = auth()->user();

        $patient = Patient::query()->where('user_id', $user->id)->first();

        $request->validate([
            'username'    => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'password'    => 'nullable|min:8',
            'national_id' => 'sometimes|required|string|size:10',
            'full_name'   => 'sometimes|required|string|max:255',
            'phone'       => 'sometimes|required|string|max:255',
            'email'       => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        DB::transaction(function () use ($request, $user, &$patient) {

            $user->update([
                'username' => $request->input('username', $user->username),
                'email'    => $request->input('email', $user->email),
                'phone'    => $request->input('phone', $user->phone),
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            $patient = Patient::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name'   => $request->input('full_name', $patient->full_name ?? $patient->full_name),
                    'national_id' => $request->input('national_id', $patient->national_id ?? $patient->national_id),
                ]
            );
        });

        $patient->load('user');

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
