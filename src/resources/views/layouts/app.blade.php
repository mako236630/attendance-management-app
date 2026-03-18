<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>勤怠アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header_logo">
            <img src="{{ asset('images/COACHTECH.png') }}" alt="logo">
        </div>

        <div class="header__nav">
            @auth
        @if (isset($status) && $status === '退勤済')
           <a href="{{ route('attendance.list') }}">
                <button type="submit">今月の出勤一覧</button>
            </a>
            <a href="{{ route('requestlist.index') }}">
                <button type="submit">申請一覧</button>
            </a>
        @else
            <a href="{{ route('attendance.index') }}">
                <button type="submit">勤怠</button>
            </a>
            <a href="{{ route('attendance.list') }}">
                <button type="submit">勤怠一覧</button>
            </a>
            <a href="{{ route('requestlist.index') }}">
                <button type="submit">申請</button>
            </a>
        @endif
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
            @endauth
        </div>

    </header>

    <main class="container">
        @yield('content')
    </main>
</body>

</html>
