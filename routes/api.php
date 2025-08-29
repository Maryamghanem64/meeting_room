<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AttachmentController,
    FeatureController,
    ForgetPasswordController,
    RoleController,
    RoomController,
    UserController,
    MeetingController,
    MeetingAttendeeController,
    MeetingMinuteController
};
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChangePasswordController;


// Authentication Routes (Public)
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

// Password Reset Routes (Public)
Route::post('/forgot-password', [ForgetPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword']);

    // Profile route for authenticated user
    Route::get('/user/profile', [UserController::class, 'profile']);

    // Admin Only
    Route::middleware('role:Admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus']); // User status update

        // Role management endpoints
        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);
    });

    // Admin + Employee
    Route::middleware('role:Admin,Employee')->group(function () {
        Route::apiResource('meetings', MeetingController::class);
        Route::apiResource('meetingAttendees', MeetingAttendeeController::class);
        Route::apiResource('meetingMinutes', MeetingMinuteController::class);
        Route::apiResource('attachments', AttachmentController::class);
    });

    // Admin + Employee + Guest
    Route::middleware('role:Admin,Employee,Guest')->group(function () {
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('features', FeatureController::class);
    });
});
