@extends('layouts.adminapp')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance__staff-container">
        <h1>{{ $user->name }}さんの勤怠</h1>

        <div class="prev__next">
            <a href="?month={{ $displayMonth->copy()->subMonth()->format('Y-m') }}">← 前日</a>
            <span><img src="{{ asset('images/calendar.png') }}" alt="logo">{{ $displayMonth->format('Y/m') }}</span>
            <a href="?month={{ $displayMonth->copy()->addMonth()->format('Y-m') }}">翌日 →</a>
        </div>

        <table class="attendance__staff-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $startDate = \Carbon\Carbon::parse($displayMonth)->startOfMonth();
                    $endDate = \Carbon\Carbon::parse($displayMonth)->endOfMonth();
                @endphp

                @for ($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                    @php
                        $attendance = $attendances->first(function ($item) use ($date) {
                            return \Carbon\Carbon::parse($item->created_at)->isSameDay($date);
                        });

                        $totalRestMinutes = 0;
                        $formattedRest = '';
                        $workTime = '';

                        if ($attendance) {
                            foreach ($attendance->rests as $rest) {
                                if ($rest->rest_in_at && $rest->rest_out_at) {
                                    $start = \Carbon\Carbon::parse($rest->rest_in_at);
                                    $end = \Carbon\Carbon::parse($rest->rest_out_at);
                                    $totalRestMinutes += $start->diffInMinutes($end);
                                }
                            }

                            if ($totalRestMinutes > 0) {
                                $formattedRest = sprintf(
                                    '%02d:%02d',
                                    floor($totalRestMinutes / 60),
                                    $totalRestMinutes % 60,
                                );
                            }

                            if ($attendance->punched_in_at && $attendance->punched_out_at) {
                                $in = \Carbon\Carbon::parse($attendance->punched_in_at);
                                $out = \Carbon\Carbon::parse($attendance->punched_out_at);

                                $netWorkMinutes = $in->diffInMinutes($out) - $totalRestMinutes;

                                if ($netWorkMinutes < 0) {
                                    $netWorkMinutes = 0;
                                }

                                $workTime = sprintf('%02d:%02d', floor($netWorkMinutes / 60), $netWorkMinutes % 60);
                            }
                        }
                    @endphp

                    <tr>
                        <td>{{ $date->format('m/d') }}({{ $date->isoFormat('ddd') }})</td>

                        <td>{{ $attendance && $attendance->punched_in_at ? \Carbon\Carbon::parse($attendance->punched_in_at)->format('H:i') : '' }}
                        </td>
                        <td>{{ $attendance && $attendance->punched_out_at ? \Carbon\Carbon::parse($attendance->punched_out_at)->format('H:i') : '' }}
                        </td>
                        <td>{{ $formattedRest }}</td>
                        <td>{{ $workTime }}</td>

                        <td>
                            @if ($attendance)
                                <a href="{{ route('admin.show', ['id' => $attendance->id]) }}">詳細</a>
                            @else
                                &nbsp;
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="attendance__csv">
            <a href="{{ route('export.csv', ['id' => $user->id, 'month' => $displayMonth->format('Y-m')]) }}"
                class="csv-btn">
                CSV出力
            </a>
        </div>

    </div>
@endsection
