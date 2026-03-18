<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test__勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $this->travelTo(now()->parse('2026-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2026-03-16 09:00:00',
            'punched_out_at' => '2026-03-16 18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('テスト用ユーザー');

    }

    public function test__勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:00:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('2025年');
        $response->assertSee('3月16日');
    }

    public function test__「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:30:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('09:30');
        $response->assertSee('18:00');
    }

    public function test__「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:30:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-16 12:30:00",
            "rest_out_at" => "2025-03-16 13:00:00",
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('12:30');
        $response->assertSee('13:00');
    }

    public function test__出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:30:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "2025-03-16 19:00:00",
            "out_time" => "2025-03-16 18:00:00",
            "note" => "テスト用の備考です",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "time_error" => "出勤時間が不適切な値です"
        ]);
    }

    public function test__休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:30:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-16 12:30:00",
            "rest_out_at" => "2025-03-16 13:00:00",
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "2025-03-16 09:00:00",
            "out_time" => "2025-03-16 18:00:00",
            "note" => "テスト用の備考です",
            "rests" => [
                $rest->id =>[
                    "in" => "2025-03-16 19:00:00",
                    "out" => "2025-03-16 14:00:00",
                ]
            ]
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "rests.{$rest->id}.in" => "休憩時間が不適切な値です"
        ]);
    }

    public function test__休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:30:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-16 12:30:00",
            "rest_out_at" => "2025-03-16 13:00:00",
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "2025-03-16 09:00:00",
            "out_time" => "2025-03-16 18:00:00",
            "note" => "テスト用の備考です",
            "rests" => [
                $rest->id => [
                    "in" => "2025-03-16 17:00:00",
                    "out" => "2025-03-16 19:00:00",
                ]
            ]
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "rests.{$rest->id}.out" => "休憩時間もしくは退勤時間が不適切な値です"
        ]);
    }

    public function test__備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:30:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-16 12:30:00",
            "rest_out_at" => "2025-03-16 13:00:00",
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "2025-03-16 09:00:00",
            "out_time" => "2025-03-16 18:00:00",
            "note" => "",
            "rests" => [
                $rest->id => [
                    "in" => "2025-03-16 12:00:00",
                    "out" => "2025-03-16 13:00:00",
                ]
            ]
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "note" => "備考を記入してください",
        ]);
    }

    public function test__修正申請処理が実行される()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test@com",
            "password" => Hash::make("passwordadmin"),
            "is_admin" => 1,
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:00:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-16 12:30:00",
            "rest_out_at" => "2025-03-16 13:00:00",
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "09:30",
            "out_time" => "18:00",
            "note" => "電車遅延",
            "rests" => [
                $rest->id => [
                    "in" => "12:00",
                    "out" => "13:00",
                ]
            ]
        ]);

        $response = $this->actingAs($adminuser)->get("/stamp_correction_request/list");
        $response->assertStatus(200);
        $response->assertSee("テスト用ユーザー");
        $response->assertSee("承認待ち");
        $response->assertSee("2025/03/16");
        $response->assertSee("電車遅延");

        $response = $this->actingAs($adminuser)->get("/stamp_correction_request/approve/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee("テスト用ユーザー");
        $response->assertSee("2025年");
        $response->assertSee("3月16日");
        $response->assertSee("09:30");
        $response->assertSee("電車遅延");
    }

    public function test__「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:00:00',
            'punched_out_at' => '2025-03-16 18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-17 09:00:00',
            'punched_out_at' => '2025-03-17 18:00:00',
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-16 12:30:00",
            "rest_out_at" => "2025-03-16 13:00:00",
        ]);

        $rest2 = Rest::create([
            "attendance_id" => $attendance2->id,
            "rest_in_at" => "2025-03-16 12:30:00",
            "rest_out_at" => "2025-03-16 13:00:00",
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "09:30",
            "out_time" => "18:00",
            "note" => "電車遅延",
            "rests" => [
                $rest->id => [
                    "in" => "12:00",
                    "out" => "13:00",
                ]
            ]
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance2->id}", [
            "user_id" => $user->id,
            "in_time" => "10:30",
            "out_time" => "18:00",
            "note" => "私用外出",
            "rests" => [
                $rest->id => [
                    "in" => "12:00",
                    "out" => "13:00",
                ]
            ]
        ]);

        $response = $this->actingAs($user)->get("/stamp_correction_request/list?tab=");
        $response->assertStatus(200);
        $response->assertSee("テスト用ユーザー");
        $response->assertSee("承認待ち");
        $response->assertSee("2025/03/16");
        $response->assertSee("電車遅延");
        $response->assertSee("テスト用ユーザー");
        $response->assertSee("承認待ち");
        $response->assertSee("2025/03/16");
        $response->assertSee("私用外出");
    }

    public function test__「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 10:00:00',
            'punched_out_at' => '2025-03-16 18:00:00',
            'note' => '電車遅延',
            'status' => 2,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-17 11:00:00',
            'punched_out_at' => '2025-03-17 18:00:00',
            'note' => '私用外出',
            'status' => 2,
        ]);

        $response = $this->actingAs($user)->get("/stamp_correction_request/list?tab=requestok");
        $response->assertStatus(200);

        $response->assertSee("2025/03/16");
        $response->assertSee("電車遅延");

        $response->assertSee("2025/03/17");
        $response->assertSee("私用外出");

        $response->assertSee("テスト用ユーザー");
        $response->assertSee("承認済み");
    }

    public function test__各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト用ユーザー",
            "email" => "test@test@com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2025-03-16 09:00:00',
            'punched_out_at' => '2025-03-16 18:00:00',
            'requested_in_at' => '2025-03-16 10:00:00',
            'requested_out_at' => '2025-03-16 18:00:00',
            'note' => '電車遅延',
            'status' => 1,
        ]);

        $response = $this->actingAs($user)->get("/stamp_correction_request/list");
        $response->assertStatus(200);

        $response->assertSee(url("/attendance/detail/{$attendance->id}"));

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertSee("勤怠詳細");
        $response->assertSee("テスト用ユーザー");
        $response->assertSee("2025年");
        $response->assertSee("3月16日");
        $response->assertSee("10:00");
        $response->assertSee("電車遅延");
    }
}