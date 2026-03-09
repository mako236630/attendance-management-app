@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/requestlist.css') }}">
@endsection

@section('content')
    <div class="request__list-container">

        <h1>申請一覧</h1>

        <div class="tab">
            <a href="{{ $requestwaiting }}" class="tab__list {{ $tab !== 'requestok' ? 'is-active' : '' }}">承認待ち</a>
            <a href="{{ $requestok }}" class="tab__requestlist {{ $tab === 'requestok' ? 'is-active' : '' }}">承認済み</a>
        </div>
        <hr>

        <div class="table__container">
            @if ($attendances->isEmpty())
                @if ($tab === 'requestok')
                    <p>承認済みのデータはありません</p>
                @else
                    <p>承認待ちのデータはありません</p>
                @endif
            @else
                <table class="request_list-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->status == 1 ? '承認待ち' : '承認済み' }}</td>
                                <td>{{ $attendance->user->name }}</td>
                                <td>{{ $attendance->created_at->format('Y/m/d') }}</td>
                                <td>{{ $attendance->note }}</td>
                                <td>{{ $attendance->updated_at->format('Y/m/d') }}</td>
                                <td><a href="{{ route('attendance.show', ['id' => $attendance->id]) }}">詳細</a></td>
                            </tr>
                        @endforeach
            @endif
            </tbody>
            </table>
        </div>
    </div>
@endsection
