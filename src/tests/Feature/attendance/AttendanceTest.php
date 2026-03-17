<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class attendanceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test__現在の日時情報がUIと同じ形式で出力されている()
    {
        $this->travelTo(now()->parse('2026-03-16 10:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => "2026-03-16 10:00:00",
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年3月16日');
        $response->assertSee('10:00');
    }

    public function test__勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test__出勤中の場合、勤怠ステータスが正しく表示される()
    {
        $this->travelTo(now()->parse('2026-03-16 10:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test__休憩中の場合、勤怠ステータスが正しく表示される()
    {
        $this->travelTo(now()->parse('2026-03-16 10:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2026-03-16 12:00:00",
            "rest_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test__退勤済の場合、勤怠ステータスが正しく表示される()
    {
        $this->travelTo(now()->parse('2026-03-16 10:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => "2026-03-16 18:00:00",
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2026-03-16 12:00:00",
            "rest_out_at" => "2026-03-16 12:30:00",
            "requested_in_at" => null,
            "requested_out_at" => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    public function test__出勤ボタンが正しく機能する()
    {
        $this->travelTo(now()->parse('2026-03-16 10:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button class="button__is-working" type="submit" name="is_working" value="1">出勤</button>', false);

        $response = $this->ActingAs($user)->post('/attendance', ['is_working' => '1']);

        $this->assertDatabaseHas('attendances', [
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test__出勤は一日一回のみできる()
    {
        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => "2026-03-16 18:00:00",
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('<button class="button__is-working" type="submit" name="is_working" value="1">出勤</button>', false);
        $response->assertSee('お疲れ様でした');
    }

    public function test__出勤時刻が勤怠一覧画面で確認できる()
    {
        $this->travelTo(now()->parse('2026-03-16 10:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $response = $this->ActingAs($user)->post('/attendance', ['is_working' => '1']);

        $attendance = Attendance::where('user_id', $user->id)->first();


        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '03/16',
            '(月)',
            '10:00',
            'attendance/detail/' . $attendance->id
        ], false);
    }

    public function test__休憩ボタンが正しく機能する()
    {
        $this->travelTo(now()->parse('2026-03-16 12:00:00'));
        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button class="button__is-break" type="submit" name="is_break" value="1">休憩入</button>', false);

        $response = $this->ActingAs($user)->post('/attendance', ['is_break' => '1']);

        $this->assertDatabaseHas('rests', [
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2026-03-16 12:00:00",
            "rest_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test__休憩は一日に何回でもできる()
    {
        $this->travelTo(now()->parse('2026-03-16 12:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button class="button__is-break" type="submit" name="is_break" value="1">休憩入</button>', false);

        $response = $this->ActingAs($user)->post('/attendance', ['is_break' => '1']);

        $this->assertDatabaseHas('rests', [
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2026-03-16 12:00:00",
            "rest_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('<button class="button__is-breakout" type="submit" name="is_breakout" value="1">休憩戻</button>', false);

        $this->travelTo(now()->parse('2026-03-16 12:30:00'));
        $response = $this->ActingAs($user)->post('/attendance', ['is_breakout' => '1']);

        $this->assertDatabaseHas('rests', [
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2026-03-16 12:00:00",
            "rest_out_at" => "2026-03-16 12:30:00",
            "requested_in_at" => null,
            "requested_out_at" => null,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('<button class="button__is-break" type="submit" name="is_break" value="1">休憩入</button>', false);
    }

    public function test__休憩戻ボタンが正しく機能する()
    {
        $this->travelTo(now()->parse('2026-03-16 12:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->get('/attendance');
        $response = $this->ActingAs($user)->post('/attendance', ['is_break' => '1']);

        $response = $this->get('/attendance');
        $response->assertSee('<button class="button__is-breakout" type="submit" name="is_breakout" value="1">休憩戻</button>', false);

        $response = $this->ActingAs($user)->post('/attendance', ['is_breakout' => '1']);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test__休憩戻は一日に何回でもできる()
    {
        $this->travelTo(now()->parse('2026-03-16 12:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button class="button__is-break" type="submit" name="is_break" value="1">休憩入</button>', false);

        $response = $this->ActingAs($user)->post('/attendance', ['is_break' => '1']);

        $this->assertDatabaseHas('rests', [
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2026-03-16 12:00:00",
            "rest_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
        ]);

        $this->travelTo(now()->parse('2026-03-16 12:30:00'));
        $response = $this->ActingAs($user)->post('/attendance', ['is_breakout' => '1']);

        $this->assertDatabaseHas('rests', [
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2026-03-16 12:00:00",
            "rest_out_at" => "2026-03-16 12:30:00",
            "requested_in_at" => null,
            "requested_out_at" => null,
        ]);

        $this->travelTo(now()->parse('2026-03-16 15:30:00'));
        $response = $this->ActingAs($user)->post('/attendance', ['is_break' => '1']);

        $this->assertDatabaseHas('rests', [
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2026-03-16 15:30:00",
            "rest_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button class="button__is-breakout" type="submit" name="is_breakout" value="1">休憩戻</button>', false);
    }

    public function test__休憩時刻が勤怠一覧画面で確認できる()
    {
        $this->travelTo(now()->parse('2026-03-16 12:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->ActingAs($user)->post('/attendance', ['is_break' => '1']);

        $this->travelTo(now()->parse('2026-03-16 12:30:00'));
        $response = $this->ActingAs($user)->post('/attendance', ['is_breakout' => '1']);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '03/16',
            '(月)',
            '10:00',
            '00:30',
            'attendance/detail/' . $attendance->id
        ], false);
    }

    public function test__退勤ボタンが正しく機能する()
    {
        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2026-03-16 10:00:00",
            "punched_out_at" => null,
            "requested_in_at" => null,
            "requested_out_at" => null,
            "note" => null,
            "status" => 0,
        ]);

        $response = $this->ActingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button class="button__is-off" type="submit" name="is_off" value="1">退勤</button>', false);

        $response = $this->ActingAs($user)->post('/attendance', ['is_off' => '1']);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function test__退勤時刻が勤怠一覧画面で確認できる()
    {
        $this->travelTo(now()->parse('2026-03-16 10:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $response = $this->ActingAs($user)->post('/attendance', ['is_working' => '1']);

        $attendance = Attendance::where('user_id', $user->id)->first();

        $response = $this->get('/attendance');

        $this->travelTo(now()->parse('2026-03-16 18:30:00'));
        $response = $this->ActingAs($user)->post('/attendance', ['is_off' => '1']);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '03/16',
            '(月)',
            '10:00',
            '18:30',
            'attendance/detail/' . $attendance->id
        ], false);
    }
}
