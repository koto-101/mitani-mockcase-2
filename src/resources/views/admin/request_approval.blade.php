@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request-approval.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>

    <table>
        <tbody>
            <tr>
                <th>名前</th>
                <td>{{ $request->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <span class="date-year">{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年') }}</span>
                    <span class="date-space">&nbsp;&nbsp;</span>
                    <span class="date-month-day">{{ \Carbon\Carbon::parse($request->attendance->date)->format('n月j日') }}</span>
                </td>
            </tr>

                <th>出勤・退勤</th>
                    <td>
                    {{ $request->clock_in ? \Carbon\Carbon::parse($request->clock_in)->format('H:i') : '-' }}
                    ～
                    {{ $request->clock_out ? \Carbon\Carbon::parse($request->clock_out)->format('H:i') : '-' }}
                    </td>
                </tr>

            @php
                $breakLogs = $request->correctionBreakLogs->isNotEmpty()
                    ? $request->correctionBreakLogs
                    : $request->attendance->breakLogs;

                $breakCount = $breakLogs->count(); 
            @endphp

            @foreach ($breakLogs as $index => $break)
                <tr>
                    <th>
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </th>
                    <td>
                        {{ \Carbon\Carbon::parse($break->start_time)->format('H:i') }}
                        〜
                        {{ \Carbon\Carbon::parse($break->end_time)->format('H:i') }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <th>休憩{{ $breakCount + 1 }}</th>
                <td>　{{-- 空欄 --}}</td>
            </tr>

                <th>備考</th>
                <td>{{ $request->reason }}</td>
            </tr>
        </tbody>
    </table>

@if ($request->status !== 'approved')
    <form action="{{ route('requests.approve', ['id' => $request->id]) }}" method="POST" >
        @csrf
        <button type="submit" class="btn btn-primary">承認</button>
    </form>
@else
    <p class="approved-label" >承認済み</p>
@endif
</div>
@endsection
