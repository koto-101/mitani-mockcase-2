<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\User;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending'); // デフォルトは承認待ち

        $requests = StampCorrectionRequest::with('user')
            ->when($status === 'pending', fn ($q) => $q->where('status', 'pending'))
            ->when($status === 'approved', fn ($q) => $q->where('status', 'approved'))
            ->orderByDesc('created_at')
            ->get();

        // 状態ラベル（例: 承認待ち／承認済）を整形
        $requests->each(function ($request) {
            $request->status_label = match ($request->status) {
                'pending' => '承認待ち',
                'approved' => '承認済',
                default => 'その他',
            };
        });

        return view('admin.request_index', [
            'requests' => $requests,
            'currentStatus' => $status,
        ]);
    }

    public function show($id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance', 'correctionBreakLogs'])->findOrFail($id);

        // 休憩時間合計（秒）を計算
        $totalBreakSeconds = 0;
        foreach ($request->correctionBreakLogs as $break) {
            if ($break->start_time && $break->end_time) {
                $start = strtotime($break->start_time);
                $end = strtotime($break->end_time);
                $totalBreakSeconds += max(0, $end - $start);
            }
        }

        // Blade に渡すため、モデルに動的プロパティとして追加
        $request->requested_break_time = $totalBreakSeconds;

        return view('admin.request_approval', compact('request'));
    }

    public function approve(Request $request, $id)
    {
        $correctionRequest = StampCorrectionRequest::with('attendance')->findOrFail($id);

        // 勤怠情報を更新
        $attendance = $correctionRequest->attendance;
        $attendance->clock_in = $correctionRequest->clock_in;
        $attendance->clock_out = $correctionRequest->clock_out;
        $attendance->save();
        $attendance->breakLogs()->delete(); 

        foreach ($correctionRequest->correctionBreakLogs as $correctionBreak) {
            $attendance->breakLogs()->create([
                'start_time' => $correctionBreak->start_time,
                'end_time' => $correctionBreak->end_time,
            ]);
        }

        // 申請ステータスを更新
        $correctionRequest->status = 'approved';
        $correctionRequest->approved_at = now();
        $correctionRequest->save();

        return redirect()->route('requests.index')->with('success', '修正申請を承認しました。');
    }


}
