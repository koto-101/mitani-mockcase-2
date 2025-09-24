@extends('layouts.default')

@section('title', '勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance-show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>

    @if ($errors->any())
        <div class="message error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="message success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.attendance.requestCorrection', $attendance->id) }}" method="POST">
        @csrf

        <table>
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td>
                    <span class="date-year">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                    <span class="date-space">&nbsp;&nbsp;</span>
                    <span class="date-month-day">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                </td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in"
                        value="{{ old('clock_in', $correctionRequest && $correctionRequest->clock_in ? \Carbon\Carbon::parse($correctionRequest->clock_in)->format('H:i') : optional($attendance->clock_in)->format('H:i')) }}">
                    <span>〜</span>
                    <input type="time" name="clock_out"
                        value="{{ old('clock_out', $correctionRequest && $correctionRequest->clock_out ? \Carbon\Carbon::parse($correctionRequest->clock_out)->format('H:i') : optional($attendance->clock_out)->format('H:i')) }}">
                </td>
            </tr>

            @php
                // 修正申請の休憩ログが存在するか？
                $breakLogs = ($correctionRequest && $correctionRequest->correctionBreakLogs->isNotEmpty())
                    ? $correctionRequest->correctionBreakLogs
                    : $attendance->breakLogs;

                $breakCount = $breakLogs->count();
                $showCount = $breakCount + 1;
            @endphp

            @for ($i = 0; $i < $showCount; $i++)
                <tr>
                    <th>
                        @if ($i === 0)
                            休憩
                        @else
                            休憩{{ $i + 1 }}
                        @endif
                    </th>
                    <td>
                        <input type="time" name="breaks[{{ $i }}][start]" value="{{ old("breaks.$i.start", optional($breakLogs[$i]->start_time ?? null)->format('H:i')) }}">
                        <span>〜</span>
                        <input type="time" name="breaks[{{ $i }}][end]" value="{{ old("breaks.$i.end", optional($breakLogs[$i]->end_time ?? null)->format('H:i')) }}">
                    </td>
                </tr>
            @endfor

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="reason" rows="3">{{ old('reason', $correctionRequest->note ?? '') }}</textarea>
                </td>
            </tr>
        </table>

        <div>
            <button type="submit">修正</button>
        </div>
    </form>
</div>
@endsection