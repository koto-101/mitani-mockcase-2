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

        if (in_array($status, ['clock_out', 'approved'])) {
            $status = 'done';
        }

        // ステータス表示用ラベル
        $statusLabel = match ($status) {
            'clock_in' => '出勤中',
            'break_in' => '休憩中',
            'done'     => '退勤済',
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
                    $attendance->status = 'clock_in';
                }
                break;

            case 'break_in':
                // 新しい休憩開始ログを作成
                $attendance->breakLogs()->create([
                    'start_time' => now(),
                ]);
                $attendance->status = 'break_in';
                break;

            case 'break_out':
                // 未終了の最新の休憩ログを取得して終了時刻を記録
                $lastBreak = $attendance->breakLogs()->whereNull('end_time')->latest()->first();
                if ($lastBreak) {
                    $lastBreak->update(['end_time' => now()]);
                }
                $attendance->status = 'clock_in';
                break;


            case 'clock_out':
                if (!$attendance->clock_out) {
                    $attendance->clock_out = now();
                    $attendance->status = 'clock_out';
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

    public function detail($id)
    {
        $attendance = Attendance::with('breakLogs')->findOrFail($id);

        // 本人の勤怠であることを確認
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $correctionRequest = StampCorrectionRequest::with('correctionBreakLogs')
            ->where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->first();

        $hasPendingRequest = !is_null($correctionRequest);

        if ($correctionRequest) {
            if ($correctionRequest->clock_in) {
                $attendance->clock_in = $correctionRequest->clock_in;
            }

            if ($correctionRequest->clock_out) {
                $attendance->clock_out = $correctionRequest->clock_out;
            }

            // 休憩時間も置き換える（必要な場合）
            $breakLogs = $correctionRequest->correctionBreakLogs;
        } else {
            $breakLogs = $attendance->breakLogs;
        }

        return view('user.attendance_show', [
            'attendance' => $attendance,
            'hasPendingRequest' => $hasPendingRequest, 
            'breakLogs' => $breakLogs,
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
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'この勤怠にはすでに申請済みです。');
        }

        DB::transaction(function () use ($request, $attendance) {
            $correction = StampCorrectionRequest::create([
                'user_id'       => auth()->id(),
                'attendance_id' => $attendance->id,
                'status'        => 'pending',
                'reason' => $request->input('reason'),
                'clock_in'      => $request->input('clock_in'),
                'clock_out'     => $request->input('clock_out'),
                'requested_at'  => now(),
            ]);

                $attendance->status = 'pending';
                $attendance->save();


            foreach ($request->input('breaks', []) as $break) {
                if (empty($break['start']) && empty($break['end'])) {
                    continue;
                }

                // どちらかが入っていれば null を代入
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                CorrectionBreakLog::create([
                    'stamp_correction_request_id' => $correction->id,
                    'start_time' => $start,
                    'end_time'   => $end,
                ]);
            }
        });

        return redirect()->route('attendance.detail', $attendance->id)
                        ->with('success', '修正申請を送信しました。');
    }


}
