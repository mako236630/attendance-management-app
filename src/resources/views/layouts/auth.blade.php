<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>勤怠アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/auth.css') }}">
    @yield('css')
</head>

<body>
<header class="header">
    <div class="header_logo">
        <img src="{{ asset('images/COACHTECH.png') }}" alt="logo">
    </div>
</header>
    <main class="container">
        @yield('content')
    </main>
</body>

</html>