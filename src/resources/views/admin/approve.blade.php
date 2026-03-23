@extends('layouts.adminapp')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/detail.css') }}">
@endsection

@section('content')
    <div class="attendance__detail-container">
        <h1>勤怠詳細</h1>

        <form action="{{ route('request.update', $attendance->id) }}" method="post">
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
                                @elseif ($attendance->status === 2)
                                    <span class="if__in-at">{{ date('H:i', strtotime($attendance->punched_in_at)) }}</span>
                                    <span class="range-tilde">～</span>
                                    <span
                                        class="if__out-at">{{ $attendance->punched_out_at ? date('H:i', strtotime($attendance->punched_out_at)) : '' }}<span>
                        @endif
                    </td>
                </tr>

                @if (
                    $attendance->rests->count() > 0 &&
                        (!empty($attendance->rests->first()->rest_in_at) || !empty($attendance->rests->first()->requested_in_at)))
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
                                    <span>{{ $rest->requested_out_at ? date('H:i', strtotime($rest->requested_out_at)) : '00:00' }}</span>
                                @elseif ($attendance->status === 2)
                                    <span>{{ date('H:i', strtotime($rest->rest_in_at)) }}</span>
                                    <span class="range-tilde">～</span>
                                    <span>{{ $rest->rest_out_at ? date('H:i', strtotime($rest->rest_out_at)) : '00:00' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <th>休憩</th>
                        <td>
                            @if ($attendance->status == 1 || $attendance->status == 2)
                                <span>なし</span>
                            @endif
                        </td>
                    </tr>
                @endif

                <tr>
                    <th>備考</th>
                    <td>
                        {{ $attendance->note }}
                    </td>
                </tr>
            </table>

            <div class="update__button-conteinar">
                @if ($attendance->status === 1)
                    <button class="update__button" type="submit">承認</button>
                @elseif ($attendance->status === 2)
                    <p class="approve">承認済み</p>
                @endif
            </div>
        </form>

    </div>
@endsection
