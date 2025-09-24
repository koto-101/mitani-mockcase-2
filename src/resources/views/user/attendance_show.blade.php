@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance-show.css') }}">
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
                           value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}"
                           {{ $hasPendingRequest ? 'readonly' : '' }}>
                    ～
                    <input type="time" name="clock_out"
                           value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}"
                           {{ $hasPendingRequest ? 'readonly' : '' }}>
                </td>
            </tr>


            @php
                $oldBreaks = collect(old('breaks', []))
                    ->filter(fn($b) => !empty($b['start']) || !empty($b['end']));

                if ($oldBreaks->isNotEmpty()) {
                    $showCount = $oldBreaks->count()+1;
                } else {
                    $showCount = $breakLogs->count() + 1; 
                }
            @endphp

            @for ($i = 0; $i < $showCount; $i++)
                @php
                    $break = $breakLogs[$i] ?? null; 
                    $startTime = $break && $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '';
                    $endTime = $break && $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '';
                @endphp

                <tr>
                    <th>
                        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                    </th>
                    <td>
                        <input type="time" name="breaks[{{ $i }}][start]"
                               value="{{ old("breaks.$i.start", $startTime) }}"
                               {{ $hasPendingRequest ? 'readonly' : '' }}>
                        〜
                        <input type="time" name="breaks[{{ $i }}][end]"
                               value="{{ old("breaks.$i.end", $endTime) }}"
                               {{ $hasPendingRequest ? 'readonly' : '' }}>
                    </td>
                </tr>
            @endfor

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="reason" rows="3" {{ $hasPendingRequest ? 'readonly' : '' }}>{{ old('reason', $attendance->note) }}</textarea>
                </td>
            </tr>
        </table>

        @if ($hasPendingRequest)
            <p class="alert alert-warning">*承認待ちのため修正はできません。</p>
        @else
            <button type="submit" class="correction-button">修正</button>
        @endif
    </form>
</div>
@endsection
