<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\User\AttendanceStampRequest;
use App\Http\Requests\User\AttendanceCorrectionRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\DB;
use App\Models\CorrectionBreakLog;

class AttendanceController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->latest()
            ->first();

        // ステータスが null の場合は off（勤務外）
        $status = $attendance->status ?? 'off';

        // ステータス表示用ラベル
        $statusLabel = match ($status) {
            'clock_in' => '出勤中',
            'break_in' => '休憩中',
            'clock_out' => '退勤済',
            default => '勤務外',
        };

        return view('user.clock_in', compact('attendance', 'status', 'statusLabel'));
    }

    public function store(AttendanceStampRequest $request)
    {
        $user = auth()->user();
        $action = $request->input('action');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->date = now()->toDateString();
        }

        // アクションごとの処理
        switch ($action) {
            case 'clock_in':
                if (!$attendance->clock_in) {
                    $attendance->clock_in = now();
                    $attendance->status = 'clock_in'; // 出勤中
                }
                break;

            case 'break_in':
                // 新しい休憩開始ログを作成
                $attendance->breakLogs()->create([
                    'start_time' => now(),
                ]);
                $attendance->status = 'break_in'; // ステータス更新
                break;

            case 'break_out':
                // 未終了の最新の休憩ログを取得して終了時刻を記録
                $lastBreak = $attendance->breakLogs()->whereNull('end_time')->latest()->first();
                if ($lastBreak) {
                    $lastBreak->update(['end_time' => now()]);
                }
                $attendance->status = 'clock_in'; // ステータス更新
                break;


            case 'clock_out':
                if (!$attendance->clock_out) {
                    $attendance->clock_out = now();
                    $attendance->status = 'clock_out'; // 退勤済
                }
                break;
        }

        $attendance->save();

        return redirect()->route('attendance');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // 表示対象の月を取得（未指定時は今月）
        $currentMonth = Carbon::parse($request->input('month', now()->format('Y-m')) . '-01');

        $start = $currentMonth->copy()->firstOfMonth();
        $end = $currentMonth->copy()->lastOfMonth();

        // 当月分の勤怠を取得
        $attendances = Attendance::with('breakLogs')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        return view('user.attendance_index', [
            'currentMonth' => $currentMonth,
            'prevMonth'    => $currentMonth->copy()->subMonth(),
            'nextMonth'    => $currentMonth->copy()->addMonth(),
            'attendances'  => $attendances,
        ]);
    }

    // 日別詳細画面
    public function detail($id)
    {
        $attendance = Attendance::with('breakLogs')->findOrFail($id);

        // 本人の勤怠であることを確認（セキュリティ目的）
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $hasPendingRequest = \App\Models\StampCorrectionRequest::where('attendance_id', $attendance->id)
        ->where('user_id', auth()->id())
        ->where('status', 'pending')
        ->exists();

        return view('user.attendance_show', [
            'attendance' => $attendance,
            'hasPendingRequest' => $hasPendingRequest, 
        ]);
    }

    public function requestCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $existing = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return back()->with('error', 'この勤怠にはすでに申請済みです。');
        }

        DB::transaction(function () use ($request, $attendance) {
            $correction = StampCorrectionRequest::create([
                'user_id'       => auth()->id(),
                'attendance_id' => $attendance->id,
                'status'        => 'pending',
                'reason'        => '修正申請',
                'clock_in'      => $request->input('clock_in'),
                'clock_out'     => $request->input('clock_out'),
                'requested_at'  => now(),
            ]);

            foreach ($request->input('breaks', []) as $break) {
                CorrectionBreakLog::create([
                    'stamp_correction_request_id' => $correction->id,
                    'start_time' => $break['start'] ?? null,
                    'end_time'   => $break['end'] ?? null,
                ]);
            }
        });

        return redirect()->route('attendance.detail', $attendance->id)
                        ->with('success', '修正申請を送信しました。');
    }


}
