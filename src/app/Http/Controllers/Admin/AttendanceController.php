<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceCorrectionRequest;
use App\Models\StampCorrectionRequest as CorrectionRequest;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->get();

        return view('admin.attendance_index', [
            'attendances' => $attendances,
            'currentDate' => $date,
            'prevDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakLogs')->findOrFail($id);

        // 修正申請が承認待ちかどうかを確認
        $correctionRequest = $attendance->stampcorrectionRequests()
            ->latest()
            ->first(); // または ->where('status', 'pending')->first();

        return view('admin.attendance_show', [
            'attendance' => $attendance,
            'correctionRequest' => $correctionRequest,
        ]);
    }
    public function requestCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        CorrectionRequest::create([
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'requested_by_admin' => true,
            'reason' => $request->input('reason'),
            'status' => 'pending',
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'requested_at' => now(),
        ]);

        return redirect()
            ->route('admin.attendance.show', $id)
            ->with('success', '修正申請を送信しました。');
    }

    public function staff($id, Request $request)
    {
        // 対象ユーザーを取得
        $user = User::findOrFail($id);

        // 表示対象の月を取得（クエリパラメータから、なければ今月）
        $month = $request->input('month');
        $currentMonth = $month ? Carbon::parse($month) : Carbon::now()->startOfMonth();

        // 前月・翌月
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        // 月初と月末の日付
        $startDate = $currentMonth->copy()->startOfMonth()->toDateString();
        $endDate = $currentMonth->copy()->endOfMonth()->toDateString();

        // 勤怠データをその月分取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        return view('admin.staff_attendance', [
            'user' => $user,
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
}
