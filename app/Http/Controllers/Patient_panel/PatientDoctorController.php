<?php

namespace App\Http\Controllers\Patient_panel;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\DoctorAvailabilityService;
use Illuminate\Http\Request;

class PatientDoctorController extends Controller
{
    //لیست تمام پزشکان برای بیماران (همراه با جستجوی بر اساس تخصص یا نام)//
    public function index(Request $request)
    {
        $query = Doctor::query()
            ->with(['user', 'specialty'])
            ->where('verification_status', 'approved');

        // فیلتر بر اساس نام پزشک //
        if ($request->has('name')) {
            $name = $request->name;
            $query->whereHas('user', function ($q) use ($name) {
                $q->where('full_name', 'like', '%' . $name . '%');
            });
        }

        // فیلتر بر اساس تخصص//
        if ($request->has('specialty')) {
            $specialtyFilter = $request->specialty;

            $query->whereHas('specialty', function ($q) use ($specialtyFilter) {
                $q->where('name', 'like', '%' . $specialtyFilter . '%');
            });
        }


        // تعداد آیتم‌ها در هر صفحه (پیش‌فرض ۱۰ تا، قابل تنظیم با پارامتر per_page)//
        $perPage = $request->get('per_page', 10);
        $doctors = $query->paginate($perPage);

        //سفارشی‌سازی ساختار خروجی داده‌ها همراه با اطلاعات paginate//
        $doctors->getCollection()->transform(function ($doctor) {
            return [
                'id' => $doctor->id,
                'full_name' => $doctor->full_name ,
                'specialty' => $doctor->specialty->name,
                'email' => $doctor->user->email?? null,
                'phone' => $doctor->user->phone?? null,
                'bio' => $doctor->bio,
                'consultation_fee' => $doctor->consultation_fee,
            ];
        });

        return response()->json([
            'message' => 'success',
            'data' => $doctors
        ]);
    }

    //  مشاهده اسلات‌های خالی یک پزشک خاص توسط بیمار//
    public function slots(Doctor $doctor, DoctorAvailabilityService $service, Request $request)
    {
        // محاسبه اسلات‌های خالی پزشک با استفاده از سرویس//
        if ($doctor->verification_status=='pending'||$doctor->verification_status=='rejected') {
            return response()->json([
                'success' => false,
                'message' => 'پروفایلی برای این پزشک یافت نشد.'
            ]);
        }

        $schedule = $service->getDoctorSlots($doctor);

        //اگر کاربر تاریخ خاصی را فیلتر کرده بود//
        if ($request->has('date')) {
            $filterDate = $request->date;

            //فقط آرایه مربوط به همان تاریخ را پیدا می کند//
            $schedule = array_values(array_filter($schedule, function ($item) use ($filterDate) {
                return $item['date'] === $filterDate;
            }));
        }

        //اگر فیلتر تاریخ بازه ای بود//
        if ($request->has(['start_date', 'end_date'])) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $schedule = array_values(array_filter($schedule, function ($item) use ($startDate, $endDate) {
                return $item['date'] >= $startDate && $item['date'] <= $endDate;
            }));
        }

        //  فیلتر بر اساس وضعیت اسلات (مثلاً فقط available یا فقط booked)//
        if ($request->has('status')) {
            $statusFilter = $request->status;

            foreach ($schedule as &$day) {
                $day['slots'] = array_values(array_filter($day['slots'], function ($slot) use ($statusFilter) {
                    return $slot['status'] === $statusFilter;
                }));
            }
            unset($day);

           // حذف روزهایی که بعد از فیلتر شدن اسلات، هیچ اسلاتی در آن‌ها باقی نمانده است //
            $schedule = array_values(array_filter($schedule, fn($day) => !empty($day['slots'])));
        }

        return response()->json([
            'success' => true,
            'doctor' => [
                'id' => $doctor->id,
                'full_name' => $doctor->full_name ?? 'دکتر',
                'specialty' => $doctor->specialty->name,
                'bio' => $doctor->bio,
                'phone' => $doctor->user->phone ?? null,
                'email' => $doctor->user->email?? null,
                'consultation_fee' => $doctor->consultation_fee,
            ],
            'schedule' => $schedule
        ]);
    }
}
