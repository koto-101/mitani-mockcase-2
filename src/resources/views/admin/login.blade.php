@extends('layouts.default')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/login.css') }}">
@endsection

@section('content')
<div class="login-container">
    <h1>管理者ログイン</h1>

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        
        <div>
            <label for="email">メールアドレス</label>
            <input type="text" name="email" autofocus>
            <div class="form__error">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <div>
            <label for="password">パスワード</label>
            <input type="password" name="password" >
            <div class="form__error">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <button type="submit">管理者ログインする</button>
    </form>
</div>
@endsection
