<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AttachmentController,
    FeatureController,
    RoleController,
    RoomController,
    UserController,
    MeetingController,
    MeetingAttendeeController,
    MeetingMinuteController
};

// Authentication Routes (Public)
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);

    // Admin Only Routes
    Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::apiResource('users', UserController::class);
});


    // Admin + Employee
    Route::middleware('role:Admin,Employee')->group(function () {
        Route::apiResource('meetings', MeetingController::class);
        Route::apiResource('meetingAttendees', MeetingAttendeeController::class);
        Route::apiResource('meetingMinutes', MeetingMinuteController::class);
        Route::apiResource('attachments', AttachmentController::class);
    });

    // All Authenticated Users (Admin, Employee, Guest)
    Route::middleware('role:Admin,Employee,Guest')->group(function () {
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('features', FeatureController::class);
    });
});
