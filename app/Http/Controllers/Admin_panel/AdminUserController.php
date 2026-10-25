<?php

namespace App\Http\Controllers\Admin_panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
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

}
