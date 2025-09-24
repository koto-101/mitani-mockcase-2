@extends('layouts.default')

@section('title', '勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance-index.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="main-title-container">
    <h1>{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>
    </div>

    {{-- 日付と前日・翌日リンク --}}
    <div class="date-navigation">
        <a href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}">← 前日</a>
        <strong>{{ $currentDate->format('📅Y/m/d') }}</strong>
        <a href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}">翌日 →</a>
    </div>

    {{-- 勤怠テーブル --}}
    <table>
        <thead>
            <tr>
                <th>名前</th>
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
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
                    <td>{{ gmdate('G:i', $attendance->total_break_time ?? 0) }}</td>
                    <td>{{ gmdate('G:i', $attendance->working_time ?? 0) }}</td>
                    <td><a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">この日の勤怠データはありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
