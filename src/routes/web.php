<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController as UserLoginController;
use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use App\Http\Controllers\User\RequestController as UserRequestController;

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;

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

Route::get('/login', function () {
    return view('user.login');
})->middleware('guest')->name('login');
Route::post('/login', [UserLoginController::class, 'login'])->middleware('guest');

Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// Route::get('/admin/attendances', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'register']);

// -------------------------------
// 一般ユーザー：ログイン後の画面
// -------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/clock-in', function () {
        return view('user.clock_in');
    })->name('user.clock_in');
    Route::get('/attendance', [UserAttendanceController::class, 'show'])->name('attendance');
    Route::post('/attendance', [UserAttendanceController::class, 'store']);
    
    Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [UserAttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/detail/{id}', [UserAttendanceController::class, 'requestCorrection'])->name('requestCorrection');
    Route::get('/stamp_correction_request/list', [UserRequestController::class, 'index'])->name('user.requests');
});

// -------------------------------
// 管理者：ログイン（ゲストのみ）
// -------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
});

// -------------------------------
// 管理者：ログアウト（認証後）
// -------------------------------
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// -------------------------------
// 管理者：認証・管理者のみアクセス可能
// -------------------------------
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendances/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::post('/admin/attendance/{id}/correction', [AdminAttendanceController::class, 'requestCorrection'])->name('admin.attendance.requestCorrection');

    // 他にも必要ならここに追加可能
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/requests', [AdminRequestController::class, 'index'])->name('requests.index');
});