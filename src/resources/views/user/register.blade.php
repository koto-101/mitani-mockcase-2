@extends('layouts.default')

@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/register.css') }}"> {{-- 任意 --}}
@endsection

@section('content')
<div class="auth-container">
    <h1 class="auth-title">会員登録</h1>

    {{-- 登録フォーム --}}
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="name">名前</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" autofocus>
        </div>
        <div class="form__error">
            @error('name')
            {{ $message }}
            @enderror
        </div>

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="text" name="email" id="email" value="{{ old('email') }}" >
        </div>
        <div class="form__error">
            @error('email')
            {{ $message }}
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password" >
        </div>
        <div class="form__error">
            @error('password')
            {{ $message }}
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">パスワード確認</label>
            <input type="password" name="password_confirmation" id="password_confirmation" >
        </div>

        <div class="form-group">
            <button type="submit" class="auth-button">登録する</button>
        </div>
    </form>

    <div class="auth-link">
        <a href="{{ route('login') }}">ログインはこちら</a>
    </div>
</div>
@endsection
