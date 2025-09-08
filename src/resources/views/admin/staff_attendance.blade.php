@extends('layouts.default')

@section('title', $user->name . 'さんの勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.attendance.css') }}">
@endsection

@section('content')
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
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') }}</td>
                    <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
                    <td>{{ gmdate('H:i', $attendance->total_break_time ?? 0) }}</td>
                    <td>{{ gmdate('H:i', $attendance->working_time ?? 0) }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">この月の勤怠データはありません。</td>
                </tr>
            @endforelse
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
