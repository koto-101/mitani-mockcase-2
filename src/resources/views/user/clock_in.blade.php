@extends('layouts.default')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/clock-in.css') }}">
@endsection

@section('content')

@php
    $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
    $date = now();
    $dayOfWeek = $weekDays[$date->dayOfWeek];
@endphp

<div class="attendance-container">
    <p class="status">{{ $statusLabel }}</p>
    
    <p class="date">{{ $date->format("Y年n月j日") }}（{{ $dayOfWeek }}）</p>
    <p class="time">{{ $date->format("H:i") }}</p>

    <form method="POST" action="{{ route('attendance') }}">
        @csrf

        @if ($status === 'off')
            <button type="submit" name="action" value="clock_in">出勤</button>
        @elseif ($status === 'clock_in')
            <button type="submit" name="action" value="clock_out">退勤</button>
            <button type="submit" name="action" value="break_in" class="white-button">休憩入</button>
        @elseif ($status === 'break_in')
            <button type="submit" name="action" value="break_out">休憩戻</button>
        @elseif ($status === 'done')
            <p>お疲れ様でした。</p>
        @endif

    </form>
</div>

@endsection
