<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/privacy-policy', function () {
    return view('privacy');
});
Route::get('/admin/login',[App\Http\Controllers\AdminController::class, 'login_view'])->name('login');

Route::post('/admin/loginCheck',[App\Http\Controllers\AdminController::class, 'login'])->name('loginCheck');
Route::get('/admin/dashboard',[App\Http\Controllers\AdminController::class, 'index'])->name('admin/dashboard');
