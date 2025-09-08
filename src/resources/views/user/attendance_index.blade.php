@extends('layouts.default')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
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
    $attendanceMap = $attendances->mapWithKeys(function ($attendance) {
        return [$attendance->date->format('Y-m-d') => $attendance];
    });
@endphp

<div class="attendance-list-container">
    <h1>勤怠一覧</h1>

    <div class="month-navigation">
        <a href="{{ route('attendance.list', ['month' => $prevMonth->format('Y-m')]) }}">←前月</a>
        <span class="current-month">
            📅 {{ $currentMonth->format('Y/m') }}
        </span>
        <a href="{{ route('attendance.list', ['month' => $nextMonth->format('Y-m')]) }}">翌月→</a>
    </div>

    <table class="attendance-table">
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
                    $formatted = $date->format('m/d');
                    $weekday = $weekDays[$date->dayOfWeek];
                    $attendance = $attendanceMap[$date->format('Y-m-d')] ?? null;

                    $breakMinutes = 0;

                    if ($attendance && $attendance->breakLogs->isNotEmpty()) {
                        foreach ($attendance->breakLogs as $break) {
                            if ($break->start_time && $break->end_time) {
                                $start = \Carbon\Carbon::parse($break->start_time);
                                $end = \Carbon\Carbon::parse($break->end_time);
                                $breakMinutes += $end->diffInMinutes($start);
                            }
                        }
                    }

                    // 合計勤務時間
                    $totalMinutes = null;
                    if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                        $workMinutes = $attendance->clock_out->diffInMinutes($attendance->clock_in);
                        $totalMinutes = $workMinutes - $breakMinutes;
                    }

                @endphp

                <tr>
                    <td>{{ $formatted }}（{{ $weekday }}）</td>
                    <td>{{ $attendance?->clock_in?->format('H:i') }}</td>
                    <td>{{ $attendance?->clock_out?->format('H:i') }}</td>
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
                            <a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
@endsection
