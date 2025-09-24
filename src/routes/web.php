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

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

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

Route::get('/email/verify', function () {
    return view('user.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '確認メールを再送信しました！');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


Route::get('/login', function () {
    return view('user.login');
})->middleware('guest')->name('login');
Route::post('/login', [UserLoginController::class, 'login'])->middleware('guest');

Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout');


Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'register']);

// -------------------------------
// 一般ユーザー：ログイン後の画面
// -------------------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/clock-in', function () {
        return view('user.clock_in');
    })->name('user.clock_in');
    Route::get('/attendance', [UserAttendanceController::class, 'show'])->name('attendance');
    Route::post('/attendance', [UserAttendanceController::class, 'store']);
    
    Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [UserAttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [UserAttendanceController::class, 'requestCorrection'])->name('requestCorrection');
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
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::post('/admin/attendance/{id}/correction', [AdminAttendanceController::class, 'requestCorrection'])->name('admin.attendance.requestCorrection');

    // 他にも必要ならここに追加可能
    Route::get('/admin/staff/list', [UserController::class, 'index'])->name('admin.staff.index');
    Route::get('/admin/requests', [AdminRequestController::class, 'index'])->name('requests.index');
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])->name('admin.attendance.staff');
    Route::get('/admin/users/{user}/attendance/export', [AdminAttendanceController::class, 'export'])->name('admin.attendance.export');
    Route::get('/admin/requests/{id}', [AdminRequestController::class, 'show'])->name('requests.show');
    Route::post('/admin/requests/{id}', [AdminRequestController::class, 'approve'])->name('requests.approve');
});