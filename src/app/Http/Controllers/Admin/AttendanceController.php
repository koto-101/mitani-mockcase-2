<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceCorrectionRequest;
use App\Models\AttendanceCorrectionRequest as CorrectionRequest;
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

        // 修正申請が承認待ちかどうかを確認（必要なら）
        $hasPendingRequest = $attendance->stampcorrectionRequests()
            ->where('status', 'pending')
            ->exists();

        return view('admin.attendance_show', [
            'attendance' => $attendance,
            'hasPendingRequest' => $hasPendingRequest,
        ]);
    }

    public function requestCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 勤怠修正申請の登録（モデル：AttendanceCorrectionRequestを想定）
        CorrectionRequest::create([
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'requested_by_admin' => true,
            'reason' => $request->input('reason'),
            'status' => 'pending', // 未承認状態
        ]);

        return redirect()
            ->route('admin.attendance.show', $id)
            ->with('success', '修正申請を送信しました。');
    }
}
