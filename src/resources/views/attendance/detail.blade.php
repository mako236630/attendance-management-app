@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    <div class="attendance__detail-container">
        <div class="title">
            <h1>勤怠詳細</h1>
        </div>

        <form action="{{ route('attendance.update', $attendance->id) }}" method="post">
            @csrf
            <table class="detail__table">
                <tr>
                    <th>名前</th>
                    <td>
                        <span class="name">{{ $attendance->user->name }}</span>
                    </td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>
                        <span class="date__year">{{ $attendance->created_at->format('Y年') }}</span>
                        <span class="date__day">{{ $attendance->created_at->format('n月j日') }}</span>
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        @if ($attendance->status === 1)
                            <span class="if__in-at">{{ date('H:i', strtotime($attendance->requested_in_at)) }}</span>
                            <span class="range-tilde">～</span>
                            <span
                                class="if__out-at">{{ $attendance->requested_out_at ? date('H:i', strtotime($attendance->requested_out_at)) : '' }}<span>
                    </td>
                @else
                    <input type="time" name="in_time"
                        value="{{ old('in_time', \Carbon\Carbon::parse($attendance->punched_in_at)->format('H:i')) }}">
                    <span class="range-tilde">～</span>
                    <input type="time" name="out_time"
                        value="{{ old('out_time', \Carbon\Carbon::parse($attendance->punched_out_at)->format('H:i')) }}">

                    <div class="error">
                        @error('time_error')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                    </td>
                </tr>

                @foreach ($attendance->rests as $index => $rest)
                    <tr>
                        <th>
                            @if ($index === 0)
                                休憩
                            @else
                                休憩{{ $index + 1 }}
                            @endif
                        </th>
                        <td>
                            @if ($attendance->status === 1)
                                <span>{{ date('H:i', strtotime($rest->requested_in_at)) }}</span>
                                <span class="range-tilde">～</span>
                                <span>{{ $rest->requested_out_at ? date('H:i', strtotime($rest->requested_out_at)) : '' }}</span>
                            @else
                                <input type="time" name="rests[{{ $rest->id }}][in]" value="{{ old("rests.{$rest->id}.in", $rest->view_in) }}">
                                <span class="range-tilde">～</span>
                                <input type="time" name="rests[{{ $rest->id }}][out]"
                                    value="{{ old("rests.{$rest->id}.out", $rest->view_out) }}">

                                <div class="error">
                                    @error("rests.{$rest->id}.in")
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                    @error("rests.{$rest->id}.out")
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <th>備考</th>
                    <td>
                        @if ($attendance->status === 1)
                            {{ $attendance->note }}
                        @else
                            <textarea name="note" rows="4">{{ old('note', $attendance->note) }}</textarea>

                            <div class="error">
                                @error('note')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="update__button-conteinar">
                @if ($attendance->status === 1)
                    <p class="status__waiting">*承認待ちのため修正はできません。</p>
                @else
                    <button class="update__button" type="submit">修正</button>
                @endif
            </div>
        </form>

    </div>
@endsection
