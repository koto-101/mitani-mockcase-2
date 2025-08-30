@extends('layouts.default')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
<div class="request-list-container">
    <h1>申請一覧</h1>

    {{-- タブ --}}
    <div class="tabs">
        <a href="{{ route('user.requests', ['status' => 'pending']) }}" class="{{ $status === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('user.requests', ['status' => 'approved']) }}" class="{{ $status === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    {{-- 一覧テーブル --}}
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $request)
                <tr>
                    <td>
                        @if ($request->status === 'pending')
                            承認待ち
                        @elseif ($request->status === 'approved')
                            承認済み
                        @else
                            {{ $request->status }}
                        @endif
                    </td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->target_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('attendance.detail', $request->attendance_id) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">申請はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
