<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use \App\Http\Controllers\MeetingController;
use \App\Http\Controllers\MeetingAttendeeController;

use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//users tables
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('users',UserController::class);
//rooms table
Route::get('/rooms', function (Request $request) {
    return $request->user();
    })->middleware('auth:sanctum');
    Route::apiResource('rooms',RoomController::class);
//roles table
Route::get('/roles', function (Request $request) {
    return $request->user();
    })->middleware('auth:sanctum');
    Route::apiResource('roles',RoleController::class);
//features table
Route::get('/features', function (Request $request) {
    return $request->user();
    })->middleware('auth:sanctum');
   Route::apiResource('features',FeatureController::class);
   //meetings table
   Route::get('/meetings', function (Request $request) {
    return $request->user();
    })->middleware('auth:sanctum');
    Route::apiResource('meetings',MeetingController::class);
//meetingAttendees table
Route::get('/meetingAttendees', function (Request $request) {
    return $request->user();
    })->middleware('auth:sanctum');
    Route::apiResource('meetingAttendees',MeetingAttendeeController::class);

//meetingMinutes table
Route::get('/meetingMinutes', function (Request $request) {
    return $request->user();
    })->middleware('auth:sanctum');
    Route::apiResource('meetingMinutes',MeetingController::class);
    //attachments table
    Route::get('/attachments', function (Request $request) {
        return $request->user();
        })->middleware('auth:sanctum');
        Route::apiResource('attachments',AttachmentController::class);






