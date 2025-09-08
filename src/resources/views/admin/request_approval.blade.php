@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.request.css') }}">
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
                <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
            </tr>
            <tr>
                <th>出勤</th>
                <td>{{ $request->clock_in ? \Carbon\Carbon::parse($request->clock_in)->format('H:i') : '-' }}</td>
            </tr>
            <tr>
                <th>退勤</th>
                <td>{{ $request->clock_out ? \Carbon\Carbon::parse($request->clock_out)->format('H:i') : '-' }}</td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>{{ gmdate('H:i', $request->requested_break_time ?? 0) }}</td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>
                    {{ $request->requested_break_time2 !== null ? gmdate('H:i', $request->requested_break_time2) : '' }}
                </td>
            </tr>
            <tr>
                <th>備考（申請理由）</th>
                <td>{{ $request->reason }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 承認ボタン --}}
    <form action="{{ route('requests.approve', ['id' => $request->id]) }}" method="POST" style="margin-top: 20px;">
        @csrf
        <button type="submit" class="btn btn-primary">承認</button>
    </form>
</div>
@endsection
