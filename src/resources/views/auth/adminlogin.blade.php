@extends('layouts.auth')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection
@section('content')

    <form action="{{ route('login') }}" method="post" novalidate>
        @csrf

        <div class="login">
            <div class="text">

                <div class="litle">
                    <h1>管理者ログイン</h1>
                </div>

                <div class="error">
                    @error('email')
                        @if ($message === 'ログイン情報が登録されていません')
                            {{ $message }}
                        @endif
                    @enderror
                </div>

                <label>メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}">
                <div class="error">
                    @error('email')
                        @if ($message !== 'ログイン情報が登録されていません')
                            {{ $message }}
                        @endif
                    @enderror
                </div>

                <label>パスワード</label>
                <input type="password" name="password" value="{{ old('password') }}">
                <div class="error">
                    @error('password')
                        {{ $message }}
                    @enderror
                </div>

                <div class="button">
                    <button type="submit">管理者ログインする</button>
                </div>
            </div>
        </div>
    </form>
@endsection