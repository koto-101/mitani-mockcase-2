@extends('layouts.default')

@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/login.css') }}"> 
@endsection

@section('content')
<div class="login-container">
    <h1 class="form__title">ログイン</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- メールアドレス --}}
        <div class="form__group">
            <label for="email" class="form__label">メールアドレス</label>
            <input type="text" name="email" id="email" class="form__input" value="{{ old('email') }}"  autofocus>
            <div class="form__error">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>

        {{-- パスワード --}}
        <div class="form__group">
            <label for="password" class="form__label">パスワード</label>
            <input type="password" name="password" id="password" class="form__input" >
            <div class="form__error">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>

        {{-- ログインボタン --}}
        <div class="form__group">
            <button type="submit" class="form__button">ログインする</button>
        </div>

        {{-- 会員登録リンク --}}
        <div class="form__link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection
