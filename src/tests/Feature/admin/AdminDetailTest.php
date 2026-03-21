<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

class AdminDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_勤怠詳細画面に表示されているデータが選択したものになっている()
    {
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー",
            "email" => "test@test.com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
        ]);

        Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
        $response->assertSee('一般ユーザー');
        $response->assertSee('2025年');
        $response->assertSee('3月15日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test__出勤時間が退勤時間よりも後になっている場合、エラーメッセージが表示される()
    {
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー",
            "email" => "test@test.com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
        ]);

        Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->post("/admin/attendance/{$attendance->id}",[
            "in_time" => "19:00",
            "out_time" => "18:00",
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            "time_error" => "出勤時間もしくは退勤時間が不適切な値です"
        ]);
    }

    public function test__休憩時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー",
            "email" => "test@test.com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->post("/admin/attendance/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "2025-03-15 09:00:00",
            "out_time" => "2025-03-15 18:00:00",
            "note" => "テスト用の備考です",
            "rests" => [
                $rest->id => [
                    "in" => "2025-03-15 19:00:00",
                    "out" => "2025-03-15 14:00:00",
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
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー",
            "email" => "test@test.com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->post("/admin/attendance/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "2025-03-15 09:00:00",
            "out_time" => "2025-03-15 18:00:00",
            "note" => "テスト用の備考です",
            "rests" => [
                $rest->id => [
                    "in" => "2025-03-15 17:00:00",
                    "out" => "2025-03-15 19:00:00",
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
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー",
            "email" => "test@test.com",
            "password" => Hash::make("password"),
            "is_admin" => 0,
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->post("/admin/attendance/{$attendance->id}", [
            "user_id" => $user->id,
            "in_time" => "2025-03-15 09:00:00",
            "out_time" => "2025-03-15 18:00:00",
            "note" => "",
            "rests" => [
                $rest->id => [
                    "in" => "2025-03-15 12:00:00",
                    "out" => "2025-03-15 13:00:00",
                ]
            ]
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "note" => "備考を記入してください"
        ]);
    }
}