<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin_panel\AdminAppointmentController;
use App\Http\Controllers\Admin_panel\AdminDashboardController;
use App\Http\Controllers\Admin_panel\AdminUserController;
use App\Http\Controllers\Admin_panel\DoctorVerificationController;
use App\Http\Controllers\Admin_panel\SpecialtyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Doctor_panel\DoctorAppointmentController;
use App\Http\Controllers\Doctor_panel\DoctorAvailabilityController;
use App\Http\Controllers\Doctor_panel\DoctorDashboardController;
use App\Http\Controllers\Doctor_panel\DoctorExceptionController;
use App\Http\Controllers\Patient_panel\PatientDashboardController;
use App\Http\Controllers\Patient_panel\PatientDoctorController;
use App\Http\Controllers\Patient_panel\PatientProfileController;
use App\Http\Controllers\Patient_panel\PatientAppointmentController;
use App\Http\Middleware\CheckAdminUserRole;
use App\Http\Middleware\CheckDoctorProfile;
use App\Http\Middleware\CheckPatientUserRole;
use Illuminate\Support\Facades\Route;

// مسیرهای عمومی (بدون نیاز به توکن) به اصطلاح همونhomepage //
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/doctor_register', [AuthController::class, 'doctorRegister']);
Route::get('doctors', [PatientDoctorController::class, 'index']);
Route::get('doctors/{doctor}/slots', [PatientDoctorController::class, 'slots']);

// مسیرهای نیازمند احراز هویت (با توکن Sanctum) //
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

//مسیر های پنل پزشک//
Route::middleware(['auth:sanctum', CheckDoctorProfile::class])->prefix('doctor')->group(function () {
   // ثبت و مشاهده قوانین هفتگی //
    Route::get('/availabilities', [DoctorAvailabilityController::class, 'index']);
    Route::post('/availabilities', [DoctorAvailabilityController::class, 'store']);

    // جزئیات، ویرایش و حذف بر اساس ID قانون//
    Route::get('/availabilities/{id}', [DoctorAvailabilityController::class, 'show']);
    Route::put('/availabilities/{id}', [DoctorAvailabilityController::class, 'update']);
    Route::delete('/availabilities/{id}', [DoctorAvailabilityController::class, 'destroy']);

    // ثبت و مشاهده استثنا (مرخصی یا تغییر ساعت)//
    Route::post('/exceptions', [DoctorExceptionController::class, 'store']);
    Route::get('/exceptions', [DoctorExceptionController::class, 'index']);

    // جزئیات، ویرایش و حذف بر اساس ID استثنا//
    Route::get('/exceptions/{id}', [DoctorExceptionController::class, 'show']);
    Route::put('/exceptions/{id}', [DoctorExceptionController::class, 'update']);
    Route::delete('/exceptions/{id}', [DoctorExceptionController::class, 'destroy']);

    //مشاهده و مدیریت نوبت ها//
    Route::get('/appointments', [DoctorAppointmentController::class, 'index']);
    Route::put('/appointments/{id}/status', [DoctorAppointmentController::class, 'updateStatus']);

    //داشبورد آماری//
    Route::get('/dashboard/stats', [DoctorDashboardController::class, 'stats']);

    //مدیریت پروفایل//
    Route::get('/profile', [DoctorDashboardController::class, 'getProfile']);
    Route::put('/profile', [DoctorDashboardController::class, 'updateProfile']);

    // دریافت اسلات‌های ۴ هفته آینده برای دیدن خود پزشک //
    Route::get('/slots', [DoctorAvailabilityController::class, 'slots']);

    //خروج از حساب کاربر//
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/account/delete', [AccountController::class, 'deleteAccount']);
});

//مسیر های پنل کاربر عادی//
Route::middleware(['auth:sanctum',CheckPatientUserRole::class])->prefix('patient')->group(function () {

    // مسیر داشبورد بیمار//
    Route::get('/dashboard', [PatientDashboardController::class, 'index']);

    //مشاهده لیست دکترها و اسلات‌ها برای رزرو//
    Route::get('/doctors', [PatientDoctorController::class, 'index']);
    Route::get('/doctors/{doctor}/slots', [PatientDoctorController::class, 'slots']);

    //مشاهده و ویرایش پروفایل//
    Route::post('/profile', [PatientProfileController::class, 'storeOrUpdate']);
    Route::get('/profile', [PatientProfileController::class, 'show']);

    //  روت‌هایی که حتماً نیازمند این هستند که کاربر پروفایل بیمار داشته باشد (محافظت با Gate)//
    Route::middleware('can:is-patient')->group(function () {
        // مدیریت و ثبت نوبت‌ها//
        Route::post('/appointments', [PatientAppointmentController::class, 'store']);
        Route::get('/appointments', [PatientAppointmentController::class, 'index']);
        Route::delete('/appointments/{id}', [PatientAppointmentController::class, 'cancel']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/account/delete', [AccountController::class, 'deleteAccount']);
});

//مسیرهای پنل admin//
Route::middleware(['auth:sanctum',CheckAdminUserRole::class])->prefix('admin')->group(function () {
    //داشبورد و آمار کلی//
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // مدیریت و تایید پزشکان//
    Route::get('/doctors', [DoctorVerificationController::class, 'index']);
    Route::patch('/doctors/{doctor}/verification', [DoctorVerificationController::class, 'updateStatus']);

    //  مدیریت تخصص‌ها //
    Route::get('/specialties', [SpecialtyController::class, 'index']);
    Route::post('/specialties', [SpecialtyController::class, 'store']);
    Route::put('/specialties/{specialty}', [SpecialtyController::class, 'update']);
    Route::delete('/specialties/{specialty}', [SpecialtyController::class, 'destroy']);

    // مدیریت کاربران
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

    // مدیریت نوبت‌های کل سیستم
    Route::get('/appointments', [AdminAppointmentController::class, 'index']);
    Route::patch('/appointments/{appointment}/cancel', [AdminAppointmentController::class, 'cancelAppointment']);
});
