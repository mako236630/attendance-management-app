@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/attendance.css') }}">
@endsection
@section('content')
    <div class="attendance__display">
        <div class="attendance__status">
            @if ($status === '勤務外')
                <p class="status__is-off">勤務外</p>
            @elseif ($status === '出勤中')
                <p class="status__is-working">出勤中</p>
            @elseif ($status === '休憩中')
                <p class="status__is-break">休憩中</p>
            @elseif ($status === '退勤済')
                <p class="status__is-off">退勤済</p>
            @endif
        </div>

        <div class="time__display">
            <p id="real-time-date"></p>
            <p id="real-time-clock"></p>
        </div>

        <div class="attendance__button">
            <form action="{{ route('attendance.store') }}" method="post">
                @csrf
                @if ($status === '勤務外')
                    <button class="button__is-working" type="submit" name="is_working" value="1">出勤</button>
                @elseif ($status === '出勤中')
                    <button class="button__is-off" type="submit" name="is_off" value="1">退勤</button>
                    <button class="button__is-break" type="submit" name="is_break" value="1">休憩入</button>
                @elseif ($status === '休憩中')
                    <button class="button__is-breakout" type="submit" name="is_breakout" value="1">休憩戻</button>
                @endif
            </form>
        </div>

        <div class="work__out">
            @if ($status === '退勤済')
                <p class="work__out-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>


    <script>
        function updateClock() {
            const now = new Date();

            const year = now.getFullYear();
            const month = now.getMonth() + 1;
            const date = now.getDate();
            const dayList = ["日", "月", "火", "水", "木", "金", "土"];
            const day = dayList[now.getDay()];

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            document.getElementById('real-time-date').textContent = `${year}年${month}月${date}日(${day})`;
            document.getElementById('real-time-clock').textContent = `${hours}:${minutes}`;
        }

        setInterval(updateClock, 1000);

        updateClock();
    </script>
@endsection
