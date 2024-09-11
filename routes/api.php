<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\KarmController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\AdminController;


Route::post('/signup', [UserController::class, 'signup']);
Route::post('/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
Route::post('/sendOTP', [UserController::class, 'sendOTP']);
Route::post('/updatePassword', [UserController::class, 'updatePassword']);
Route::middleware('auth:sanctum')->post('/changePassword', [UserController::class, 'changePassword']);
Route::middleware('auth:sanctum')->post('/editProfile', [UserController::class, 'editProfile']);

Route::middleware('auth:sanctum')->get('/contacts/get', [ContactsController::class, 'getContacts']);
Route::middleware('auth:sanctum')->get('/contacts/search/{phoneNumber}', [ContactsController::class, 'searchByPhoneNumber']);
Route::middleware('auth:sanctum')->post('/contacts/sendRequest', [ContactsController::class, 'sendRequest']);
Route::middleware('auth:sanctum')->get('/contacts/getPendingRequest', [ContactsController::class, 'getPendingRequest']);
Route::middleware('auth:sanctum')->delete('/contacts/remove/{userId}', [ContactsController::class, 'removeContact']);
Route::middleware('auth:sanctum')->put('/contacts/accept/{id}', [ContactsController::class, 'acceptContact']);
Route::middleware('auth:sanctum')->post('/contacts/get/recommended', [ContactsController::class, 'recommendedContacts']);

Route::middleware('auth:sanctum')->post('/karm/add', [KarmController::class, 'AddKarm']);
Route::middleware('auth:sanctum')->get('/karm/requests', [KarmController::class, 'getPendingKarmsForAuthUser']);
Route::middleware('auth:sanctum')->get('/karm/reject/{id}', [KarmController::class, 'rejectKarm']);
Route::middleware('auth:sanctum')->get('/karm/accept/{id}', [KarmController::class, 'acceptKarm']);
Route::middleware('auth:sanctum')->post('/karm/update/{id}', [KarmController::class, 'updateKarm']);
Route::middleware('auth:sanctum')->get('/karm/get/{id}', [KarmController::class, 'getKarmById']);
Route::middleware('auth:sanctum')->post('/karm/getData', [KarmController::class, 'getKarmData']);
Route::middleware('auth:sanctum')->post('/user/update-fcm-token', [UserController::class, 'updateFcmToken']);
Route::middleware('auth:sanctum')->get('/send-notification', [PushNotificationController::class, 'sendPushNotification']);
Route::post('/send-notification', [PushNotificationController::class, 'sendNotification']);

// admin routes
Route::post('/register', [AdminController::class, 'register']);
