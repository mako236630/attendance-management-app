@extends('layouts.auth')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection
@section('content')
    <form action="{{ route('register') }}" method="post" novalidate>
        @csrf

        <div class="register">

            <div class="litle">
                <h1>会員登録</h1>
            </div>

            <div class="text">
                <label>名前</label>
                <input type="text" name="name" value="{{ old('name') }}">
                <div class="error">
                    @error('name')
                        {{ $message }}
                    @enderror
                </div>

                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">
                <div class="error">
                    @error('email')
                        {{ $message }}
                    @enderror
                </div>

                <label>パスワード</label>
                <input type="password" name="password" value="{{ old('password') }}">
                <div class="error">
                    @error('password')
                        {{ $message }}
                    @enderror
                </div>

                <label>パスワード確認</label>
                <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}">
                <div class="error">
                    @error('password_confirmation')
                        {{ $message }}
                    @enderror
                </div>

                <div class="button">
                    <button type="submit">登録する</button>
                </div>
            </div>

            <div class="login">
                <a href="{{ route('login') }}">ログインはこちら</a>
            </div>
        </div>
    </form>
@endsection
