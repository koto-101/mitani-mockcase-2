@extends('layouts.default')

@section('title', '勤怠詳細（管理者）')

@section('css')
{{-- CSS はあとから別ファイルで追加予定 --}}
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>

    @if ($errors->any())
        <div style="color: red; margin-bottom: 1em;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div style="color: green; margin-bottom: 1em;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.attendance.requestCorrection', $attendance->id) }}" method="POST">
        @csrf

        <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; margin-top: 20px;">
            <tr>
                <th style="width: 150px;">名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in"
                        value="{{ old('clock_in', $correctionRequest && $correctionRequest->clock_in ? \Carbon\Carbon::parse($correctionRequest->clock_in)->format('H:i') : optional($attendance->clock_in)->format('H:i')) }}">

                    <input type="time" name="clock_out"
                        value="{{ old('clock_out', $correctionRequest && $correctionRequest->clock_out ? \Carbon\Carbon::parse($correctionRequest->clock_out)->format('H:i') : optional($attendance->clock_out)->format('H:i')) }}">
                </td>
            </tr>

            <tr>
                <th>休憩</th>
                <td>
                    @foreach ($attendance->breakLogs as $i => $break)
                        <div style="margin-bottom: 5px;">
                            <label>休憩{{ $i + 1 }}</label>
                            <input type="time" name="breaks[{{ $i }}][start]"
                                value="{{ old('breaks.'.$i.'.start', $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '') }}">
                            〜
                            <input type="time" name="breaks[{{ $i }}][end]"
                                value="{{ old('breaks.'.$i.'.end', $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '') }}">
                        </div>
                    @endforeach
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="reason" rows="3" style="width: 100%;">{{ old('reason', $correctionRequest->note ?? '') }}</textarea>
                </td>
            </tr>
        </table>

        <div style="margin-top: 20px;">
            <button type="submit">修正申請</button>
        </div>
    </form>
</div>
@endsection
