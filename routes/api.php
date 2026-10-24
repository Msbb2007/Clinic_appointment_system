<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Doctor_panel\DoctorAppointmentController;
use App\Http\Controllers\Doctor_panel\DoctorAvailabilityController;
use App\Http\Controllers\Doctor_panel\DoctorDashboardController;
use App\Http\Controllers\DoctorExceptionController;
use App\Http\Middleware\CheckDoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// مسیرهای عمومی (بدون نیاز به توکن) //
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/doctor_register', [AuthController::class, 'doctorRegister']);

// مسیرهای نیازمند احراز هویت (با توکن Sanctum) //
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

//مسیر های پنل پزشک//
Route::middleware(['auth:sanctum', CheckDoctorProfile::class])->group(function () {
   // ثبت و مشاهده قوانین هفتگی //
    Route::get('/doctor/availabilities', [DoctorAvailabilityController::class, 'index']);
    Route::post('/doctor/availabilities', [DoctorAvailabilityController::class, 'store']);

    // جزئیات، ویرایش و حذف بر اساس ID قانون//
    Route::get('/doctor/availabilities/{id}', [DoctorAvailabilityController::class, 'show']);
    Route::put('/doctor/availabilities/{id}', [DoctorAvailabilityController::class, 'update']);
    Route::delete('/doctor/availabilities/{id}', [DoctorAvailabilityController::class, 'destroy']);

    // ثبت و مشاهده استثنا (مرخصی یا تغییر ساعت)//
    Route::post('/doctor/exceptions', [DoctorExceptionController::class, 'store']);
    Route::get('/doctor/exceptions', [DoctorExceptionController::class, 'index']);

    // جزئیات، ویرایش و حذف بر اساس ID استثنا//
    Route::get('/doctor/exceptions/{id}', [DoctorExceptionController::class, 'show']);
    Route::put('/doctor/exceptions/{id}', [DoctorExceptionController::class, 'update']);
    Route::delete('/doctor/exceptions/{id}', [DoctorExceptionController::class, 'destroy']);

    //مشاهده و مدیریت نوبت ها//
    Route::get('/doctor/appointments', [DoctorAppointmentController::class, 'index']);
    Route::put('/doctor/appointments/{id}/status', [DoctorAppointmentController::class, 'updateStatus']);

    //داشبورد آماری//
    Route::get('/doctor/dashboard/stats', [DoctorDashboardController::class, 'stats']);

    //مدیریت پروفایل//
    Route::get('/doctor/profile', [DoctorDashboardController::class, 'getProfile']);
    Route::put('/doctor/profile', [DoctorDashboardController::class, 'updateProfile']);

    // دریافت اسلات‌های ۴ هفته آینده برای دیدن خود پزشک //
    Route::get('/doctor/slots', [DoctorAvailabilityController::class, 'slots']);
});
