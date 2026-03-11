@extends('layouts.adminapp')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/stafflist.css') }}">
@endsection

@section('content')
    <div class="staff__list-container">
        <h1>スタッフ一覧</h1>

        <table class="staff__list-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('attendance.staff', ['id' => $user->id]) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
@endsection
