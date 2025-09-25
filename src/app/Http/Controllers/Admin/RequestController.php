<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;


class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = StampCorrectionRequest::with(['user', 'attendance'])
            ->when($status === 'pending', fn ($q) => $q->where('status', 'pending'))
            ->when($status === 'approved', fn ($q) => $q->where('status', 'approved'))
            ->orderByDesc('created_at')
            ->get();

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

        $totalBreakSeconds = 0;
        foreach ($request->correctionBreakLogs as $break) {
            if ($break->start_time && $break->end_time) {
                $start = Carbon::parse($break->start_time);
                $end = Carbon::parse($break->end_time);
                $totalBreakSeconds += $end->diffInSeconds($start);
            }
        }

        $request->requested_break_time = $totalBreakSeconds;

        return view('admin.request_approval', compact('request'));
    }


    public function approve(Request $request, $id)
    {
        $correctionRequest = StampCorrectionRequest::with('attendance')->findOrFail($id);

        $attendance = $correctionRequest->attendance;
        $attendance->clock_in = $correctionRequest->clock_in;
        $attendance->clock_out = $correctionRequest->clock_out;
        $attendance->status = 'approved';
        $attendance->save();
        $attendance->breakLogs()->delete(); 
        foreach ($correctionRequest->correctionBreakLogs as $correctionBreak) {
            if ($correctionBreak->start_time && $correctionBreak->end_time) {
                $attendance->breakLogs()->create([
                    'start_time' => $correctionBreak->start_time,
                    'end_time' => $correctionBreak->end_time,
                ]);
            }
        }


        // 申請ステータスを更新
        $correctionRequest->status = 'approved';
        $correctionRequest->approved_at = now();
        $correctionRequest->save();

        return redirect()->route('requests.show', ['id' => $correctionRequest->id])
            ->with('success', '修正申請を承認しました。');
    }


}
