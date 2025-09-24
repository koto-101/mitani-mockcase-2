@extends('layouts.default')

@section('title', $user->name . 'さんの勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff-attendance.css') }}">
@endsection

@section('content')
@php
    if (!function_exists('minutesToTime')) {
        function minutesToTime($minutes) {
            return floor($minutes / 60) . ':' . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT);
        }
    }

    $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
    $daysInMonth = $currentMonth->daysInMonth;
    $attendanceMap = $attendanceList->mapWithKeys(function ($item) {
        return [$item['date'] => $item['attendance']];
    });
@endphp

<div class="container">
    <h1>{{ $user->name }} さんの勤怠</h1>

    {{-- 月ナビゲーション --}}
    <div class="month-navigation">
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth->format('Y-m')]) }}">← 前月</a>
        <strong>📅 {{ $currentMonth->format('Y年m月') }}</strong>
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth->format('Y-m')]) }}">翌月 →</a>
    </div>

    {{-- 勤怠テーブル --}}
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = $currentMonth->copy()->day($day);
                    $dateStr = $date->format('Y-m-d');
                    $formatted = $date->format('m/d');
                    $weekday = $weekDays[$date->dayOfWeek];
                    $attendance = $attendanceMap[$dateStr] ?? null;

                    $breakMinutes = 0;

                    if ($attendance && $attendance->breakLogs && $attendance->breakLogs->isNotEmpty()) {
                        foreach ($attendance->breakLogs as $break) {
                            if ($break->start_time && $break->end_time) {
                                $start = \Carbon\Carbon::parse($break->start_time);
                                $end = \Carbon\Carbon::parse($break->end_time);
                                $breakMinutes += $end->diffInMinutes($start);
                            }
                        }
                    }

                    $totalMinutes = null;
                    if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                        $workMinutes = \Carbon\Carbon::parse($attendance->clock_out)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_in));
                        $totalMinutes = $workMinutes - $breakMinutes;
                    }
                @endphp

                <tr>
                    <td>{{ $formatted }}（{{ $weekday }}）</td>
                    <td>{{ $attendance?->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                    <td>{{ $attendance?->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                    <td>
                        @if ($attendance && $attendance->clock_in && $attendance->clock_out)
                            {{ minutesToTime($breakMinutes) }}
                        @endif
                    </td>
                    <td>
                        @if (!is_null($totalMinutes))
                            {{ minutesToTime($totalMinutes) }}
                        @endif
                    </td>
                    <td>
                        @if ($attendance)
                            <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}">詳細</a>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- CSV出力ボタン --}}
    <div class="csv-export">
        <form action="{{ route('admin.attendance.export', ['user' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}" method="GET">
            <button type="submit">CSV出力</button>
        </form>
    </div>
</div>
@endsection
