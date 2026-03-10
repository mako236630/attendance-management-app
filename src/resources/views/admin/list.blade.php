@extends('layouts.adminapp')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/list.css') }}">
@endsection

@section('content')
    <div class="admin__list-container">

        <h1>{{ $today->format('Y年m月d日') }}の勤怠</h1>

        <div class="prev__next">
            <a href="{{ route('admin.list', ['date' => $prevDate]) }}">← 前日</a>
            <span><img src="{{ asset('images/calendar.png') }}" alt="logo">{{ $displayDate->format('Y/m/d') }}</span>
            <a href="{{ route('admin.list', ['date' => $nextDate]) }}">翌日 →</a>
        </div>

        <table class="admin__list-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $formatTime = function ($minutes) {
                        $h = floor($minutes / 60);
                        $m = $minutes % 60;
                        return sprintf('%02d:%02d', $h, $m);
                    };

                @endphp

                @foreach ($attendances as $attendance)
                @php
                    $start = \Carbon\Carbon::parse($attendance->punched_in_at);
                    $end = $attendance->punched_out_at ? \Carbon\Carbon::parse($attendance->punched_out_at) : null;

                    // 2. 休憩時間の合計（分）を計算
                    $totalRestMinutes = 0;
                    foreach ($attendance->rests as $rest) {
                    if ($rest->rest_in_at && $rest->rest_out_at) {
                    $restStart = \Carbon\Carbon::parse($rest->rest_in_at);
                    $restEnd = \Carbon\Carbon::parse($rest->rest_out_at);

                    $totalRestMinutes += $restEnd->diffInMinutes($restStart);
                    }
                    }

                    $workMinutes = 0;

                    if ($end) {
                    $totalDiff = $end->diffInMinutes($start);
                    $workMinutes = $totalDiff - $totalRestMinutes;
                    }

                    @endphp

                    <tr>
                        <td>{{ $attendance->user->name }}</td>

                        <td>{{ $attendance->punched_in_at ? \Carbon\Carbon::parse($attendance->punched_in_at)->format('H:i') : '' }}
                        </td>

                        <td>{{ $attendance->punched_out_at ? \Carbon\Carbon::parse($attendance->punched_out_at)->format('H:i') : '' }}
                        </td>

                        <td>{{ $formatTime($totalRestMinutes) }}</td>
                        <td>{{ $end ? $formatTime($workMinutes) : '勤務中' }}</td>
                        <td><a href="{{ route('admin.show', ['id' => $attendance->id]) }}">詳細</a></td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
