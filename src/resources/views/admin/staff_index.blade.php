@extends('layouts.default')

@section('title', 'スタッフ一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff-index.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>スタッフ一覧</h1>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">スタッフが登録されていません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
