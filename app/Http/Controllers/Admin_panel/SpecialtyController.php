<?php

namespace App\Http\Controllers\Admin_panel;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    //  لیست تمام تخصص‌ها (همراه با تعداد پزشکانی که این تخصص را دارند)//
    public function index()
    {
        $specialties = Specialty::query()->withCount('doctors')
        ->with(['doctors.user']) // دریافت لیست پزشکان به همراه اطلاعات جدول users //
        ->get();

        return response()->json([
            'success' => true,
            'data'    => $specialties
        ]);
    }

    //  افزودن تخصص جدید //
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:specialties,name',
            'description' => 'nullable|string',
        ]);

        $specialty = Specialty::query()->create([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تخصص جدید با موفقیت ایجاد شد.',
            'data'    => $specialty
        ], 201);
    }

     // ویرایش تخصص//
    public function update(Request $request, Specialty $specialty)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:specialties,name,' . $specialty->id,
            'description' => 'nullable|string',
        ]);

        $specialty->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تخصص با موفقیت بروزرسانی شد.',
            'data'    => $specialty
        ]);
    }

    // حذف تخصص //
    public function destroy(Specialty $specialty)
    {
        // بررسی اینکه آیا پزشکی به این تخصص متصل است یا خیر//
        if ($specialty->doctors()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'امکان حذف این تخصص وجود ندارد زیرا پزشکانی به آن متصل هستند. ابتدا باید تخصص آن پزشکان را تغییر دهید.'
            ], 422);
        }

        try {
            $specialty->delete();

            return response()->json([
                'success' => true,
                'message' => 'تخصص با موفقیت حذف شد.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطایی در حذف تخصص رخ داد.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
