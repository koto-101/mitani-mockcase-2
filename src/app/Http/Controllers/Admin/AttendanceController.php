<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceCorrectionRequest;
use App\Models\StampCorrectionRequest as CorrectionRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            ->where('status', 'pending')
            ->latest()
            ->first();

        $correctionBreakLogs = $correctionRequest ? $correctionRequest->correctionBreakLogs : collect();

        return view('admin.attendance_show', [
            'attendance' => $attendance,
            'correctionRequest' => $correctionRequest,
            'correctionBreakLogs' => $correctionBreakLogs,
        ]);
    }
    
    public function requestCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 出勤・退勤の更新
        $attendance->clock_in = $request->input('clock_in');
        $attendance->clock_out = $request->input('clock_out');
        $attendance->save();

        // 既存の休憩ログを削除
        $attendance->breakLogs()->delete();

        // 新しい休憩ログを登録
        $breaks = $request->input('breaks', []);
        foreach ($breaks as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $start = \Carbon\Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $break['start']);
                $end = \Carbon\Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $break['end']);


                $attendance->breakLogs()->create([
                    'start_time' => $start,
                    'end_time' => $end,
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.show', $id)
            ->with('success', '勤怠情報を修正しました。');
    }

    public function staff($id, Request $request)
    {
        $user = User::findOrFail($id);
        $month = $request->input('month');
        $currentMonth = $month ? Carbon::parse($month) : Carbon::now()->startOfMonth();
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $currentMonth->copy()->endOfMonth();
        $attendanceMap = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->format('Y-m-d');
            });

        $attendanceList = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->toDateString();
            $attendanceList->push([
                'date' => $dateStr,
                'attendance' => $attendanceMap->get($dateStr)
            ]);
        }

        return view('admin.staff_attendance', [
            'user'           => $user,
            'attendanceList' => $attendanceList,
            'currentMonth'   => $currentMonth,
            'prevMonth'      => $prevMonth,
            'nextMonth'      => $nextMonth,
        ]);
    }

    public function export($userId, Request $request)
    {
        $user = User::findOrFail($userId);

        // 対象の月を取得
        $month = $request->input('month');
        $currentMonth = $month ? Carbon::parse($month) : Carbon::now();

        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $currentMonth->copy()->endOfMonth();

        // 勤怠データを取得
        $attendances = Attendance::with('breakLogs')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->format('m/d');
            });

        // CSV出力
        $response = new StreamedResponse(function () use ($attendances, $user, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー
            fputcsv($handle, ["【{$user->name} さんの勤怠】"]);
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            // 各日の出力
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $weekdayJp = ['日', '月', '火', '水', '木', '金', '土'];
                $wday = $weekdayJp[$date->dayOfWeek];

                // 日付（曜日）形式で出力
                $dateStr = $date->format('m/d') . "（{$wday}）";
                $attendance = $attendances->get($date->format('m/d')); 
                $clockIn = $attendance?->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
                $clockOut = $attendance?->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';

                $breakMinutes = 0;
                if ($attendance && $attendance->breakLogs) {
                    foreach ($attendance->breakLogs as $break) {
                        if ($break->start_time && $break->end_time) {
                            $start = Carbon::parse($break->start_time);
                            $end = Carbon::parse($break->end_time);
                            $breakMinutes += $end->diffInMinutes($start);
                        }
                    }
                }

                $workingMinutes = null;
                if ($attendance?->clock_in && $attendance?->clock_out) {
                    $workTime = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in));
                    $workingMinutes = $workTime - $breakMinutes;
                }

                $breakTimeStr = ($clockIn || $clockOut)
                    ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT)
                    : '';

                $workingTimeStr = ($workingMinutes !== null)
                    ? floor($workingMinutes / 60) . ':' . str_pad($workingMinutes % 60, 2, '0', STR_PAD_LEFT)
                    : '';

                fputcsv($handle, [
                    $dateStr,
                    $clockIn,
                    $clockOut,
                    $breakTimeStr,
                    $workingTimeStr,
                ]);
            }

            fclose($handle);
        });

        $fileName = $user->name . '_' . $currentMonth->format('Y_m') . '_attendances.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
