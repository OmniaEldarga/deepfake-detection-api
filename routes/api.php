<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;


// (Public Routes)
Route::post('/register', [UserController::class, 'register']);
Route::post('verify-otp-and-register',[UserController::class, 'verifyOtpAndRegister']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/send-otp', [UserController::class, 'sendotp']);
Route::post('/verify-otp', [UserController::class, 'verifyOtp']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);

//  (Authenticated Routes)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/upload', [UploadController::class, 'upload']);
    Route::get('/files/history', [UploadController::class, 'history']);
    Route::delete('/files/{file}', [UploadController::class, 'destroy']);
});

















/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
 return $request->user();
});

Route::post('/register',[UserController::class,'Register']);
Route::post('/login',[UserController::class,'Login']);
Route::middleware('auth:sanctum')->post('/logout',[UserController::class,'Logout']);
Route::post('/password/forgot', [UserController::class, 'forgotPassword']);
Route::post('/verify-email', [EmailVerificationController::class, 'verify']);

*/
