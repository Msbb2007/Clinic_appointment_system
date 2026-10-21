<?php

use App\Http\Controllers\AuthController;
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
