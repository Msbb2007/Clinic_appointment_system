<?php

namespace App\Http\Controllers\Admin_panel;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorVerificationController extends Controller
{
    //لیست پزشکان برای ادمین (با امکان فیلتر وضعیت مثل pending یا approved)//
    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'specialty']);

        // فیلتر بر اساس وضعیت (pending, approved, rejected)//
        if ($request->has('status')) {
            $query->where('verification_status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $doctors = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $doctors
        ]);
    }

    //تایید یا رد کردن پزشک توسط ادمین//
    public function updateStatus(Request $request, Doctor $doctor)
    {
        $request->validate([
            'verification_status' => 'required|in:approved,rejected,pending',
        ]);

        $doctor->update([
            'verification_status' => $request->verification_status
        ]);

        $statusMessages = [
            'approved' => 'پزشک با موفقیت تایید شد.',
            'rejected' => 'پزشک رد صلاحیت شد.',
            'pending'  => 'وضعیت پزشک به حالت در انتظار تغییر یافت.'
        ];

        return response()->json([
            'success' => true,
            'message' => $statusMessages[$request->verification_status],
            'data'    => $doctor->load('user', 'specialty')
        ]);
    }
}
