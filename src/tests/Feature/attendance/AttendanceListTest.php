<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test__自分が行った勤怠情報が全て表示されている()
    {
        $this->travelTo(now()->parse('2026-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("passworrd"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2026-03-15 09:00:00',
            'punched_out_at' => '2026-03-15 18:00:00',
        ]);
        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2026-03-16 09:10:00',
            'punched_out_at' => '2026-03-16 18:00:00',
        ]);
        $attendance3 = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2026-03-17 09:20:00',
            'punched_out_at' => '2026-03-17 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('03/15');
        $response->assertSee('09:00');
        $response->assertSee('attendance/detail/' . $attendance1->id);

        $response->assertSee('03/16');
        $response->assertSee('09:10');
        $response->assertSee('attendance/detail/' . $attendance2->id);

        $response->assertSee('03/17');
        $response->assertSee('09:20');
        $response->assertSee('attendance/detail/' . $attendance3->id);
    }

    public function test__勤怠一覧に遷移した際に現在の月が表示される()
    {
        $this->travelTo(now()->parse('2025-04-15 09:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("passworrd"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('2025/04');
    }

    public function test__「前月」を押下したときに表示月の前月の情報が表示される()
    {
        $this->travelTo(now()->parse('2026-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("passworrd"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2026-02-16 09:00:00',
            'punched_out_at' => '2026-02-16 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response = $this->get('/attendance/list?display_month=2026-02');
        $response->assertSee('2026/02');
        $response->assertSee('02/16');
        $response->assertSee('09:00');
        $response->assertSee('attendance/detail/' . $attendance->id);
    }

    public function test__「翌月」を押下したときに表示月の翌月の情報が表示される()
    {
        $this->travelTo(now()->parse('2026-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("passworrd"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2026-04-16 09:00:00',
            'punched_out_at' => '2026-04-16 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response = $this->get('/attendance/list?display_month=2026-04');
        $response->assertSee('2026/04');
        $response->assertSee('04/16');
        $response->assertSee('09:00');
        $response->assertSee('attendance/detail/' . $attendance->id);
    }

    public function test__「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $this->travelTo(now()->parse('2026-03-16 09:00:00'));

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("passworrd"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'punched_in_at' => '2026-03-16 09:00:00',
            'punched_out_at' => '2026-03-16 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

    }
}