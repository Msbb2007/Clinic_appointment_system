<?php

namespace App\Http\Controllers\Admin_panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function storeDoctor(DoctorRegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::query()->create([
                'username' => $request->username,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password),
                'role'      => 'doctor',
                'is_active' => true,
            ]);

            $doctor = Doctor::query()->create([
                'user_id'             => $user->id,
                'full_name'           => $request->full_name,
                'bio'                 => $request->bio,
                'medical_license_number' => $request->medical_license_number,
                'consultation_fee'    => $request->consultation_fee,
                'specialty_id'        => $request->specialty_id,
                'verification_status' => 'approved',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'پزشک جدید با موفقیت توسط ادمین ایجاد شد.',
                'data'    => $doctor->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی در ایجاد کاربر رخ داد.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function storeUser(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::query()->create([
                'username' => $request->username,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password),
                'role'      => 'doctor',
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'کاربر جدید با موفقیت توسط ادمین ایجاد شد.',
                'data'    => $user
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی در ایجاد کاربر رخ داد.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // لیست تمام کاربران با قابلیت فیلتر بر اساس نقش (role) یا جستجو//
    public function index(Request $request)
    {
        $query = User::query();

        // فیلتر بر اساس نقش //
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

       //جستجو بر اساس نام کامل یا ایمیل یا شماره تماس //
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $perPage = $request->get('per_page', 10);
        $users = $query->with(['patient', 'doctor.specialty'])->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $users
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'امکان حذف حساب خودتان وجود ندارد.'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'کاربر با موفقیت حذف (آرشیو) شد.'
        ]);
    }

}
