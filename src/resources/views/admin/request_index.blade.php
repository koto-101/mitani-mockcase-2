@extends('layouts.default')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.request.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>申請一覧</h1>

    {{-- タブ切り替え --}}
    <div class="tabs">
        <a href="{{ route('requests.index', ['status' => 'pending']) }}" class="{{ $currentStatus === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('requests.index', ['status' => 'approved']) }}" class="{{ $currentStatus === 'approved' ? 'active' : '' }}">承認済</a>
    </div>

    {{-- 申請一覧テーブル --}}
    <table>
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
                    <td>{{ $request->status_label }}</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance_date)->format('Y/m/d ') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d ') }}</td>
                    <td>
                        <a href="{{ route('requests.show', ['id' => $request->id]) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">該当する申請はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
