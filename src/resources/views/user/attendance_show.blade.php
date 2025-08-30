@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('requestCorrection', $attendance->id) }}" method="POST">
        @csrf

        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                    ～
                    <input type="time" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">

                </td>
            </tr>

            <tr>
                <th>休憩</th>
                <td>
                    @foreach ($attendance->breakLogs as $i => $break)
                        <div class="break-log-row">
                            <label>休憩{{ $i + 1 }}</label>
                            <input type="time" name="breaks[{{ $i }}][start]" value="{{ old("breaks.$i.start", optional($break->start_time)->format('H:i')) }}">
                            〜
                            <input type="time" name="breaks[{{ $i }}][end]" value="{{ old("breaks.$i.end", optional($break->end_time)->format('H:i')) }}">
                        </div>
                    @endforeach
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                </td>
            </tr>
        </table>

        @if ($hasPendingRequest)
            <p class="alert alert-warning">*承認待ちのため修正はできません。</p>
        @else
            <button type="submit" class="correction-button">修正申請</button>
        @endif
    </form>
</div>
@endsection
