<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test__その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $users = User::factory()->count(5)->create();

        $users->each(function ($user) {
            $attendance = Attendance::create([
                "user_id" => $user->id,
                "punched_in_at" => "2025-03-16 09:00:00",
                "punched_out_at" => "2025-03-16 18:00:00",
            ]);

            Rest::create([
                "attendance_id" => $attendance->id,
                "rest_in_at" => "2025-03-16 12:00:00",
                "rest_out_at" => "2025-03-16 13:00:00",
            ]);
        });

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/list");
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $attendance = $user->attendances()->first();

            $response->assertSee("09:00");
            $response->assertSee("18:00");
            $response->assertSee("01:00");
        }
    }

    public function test__遷移した際に現在の日付が表示される()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/list");
        $response->assertStatus(200);

        $response->assertSee("2025年3月16日の勤怠");
    }

    public function test__「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $this->travelTo(now()->parse('2025-03-16 09:00:00'));

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

        $response = $this->actingAs($adminuser)->get("/admin/attendance/list");
        $response->assertStatus(200);
        
        $response->assertSee('href="http://localhost/admin/attendance/list?date=2025-03-15"', false);
        $response = $this->actingAs($adminuser)->get("/admin/attendance/list?date=2025-03-15");
        $response->assertStatus(200);
        $response->assertSee("2025年3月15日の勤怠");
        $response->assertSee("一般ユーザー");
        $response->assertSee("09:00");
        $response->assertSee("18:00");
        $response->assertSee("01:00");
    }

    public function test__「翌日」を押下した時に次の日の勤怠情報が表示される()
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
            "punched_in_at" => "2025-03-16 09:00:00",
            "punched_out_at" => "2025-03-16 18:00:00",
        ]);

        Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-16 12:00:00",
            "rest_out_at" => "2025-03-16 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/list");
        $response->assertStatus(200);

        $response->assertSee('href="http://localhost/admin/attendance/list?date=2025-03-16"', false);
        $response = $this->actingAs($adminuser)->get("/admin/attendance/list?date=2025-03-16");
        $response->assertStatus(200);
        $response->assertSee("2025年3月16日の勤怠");
        $response->assertSee("一般ユーザー");
        $response->assertSee("09:00");
        $response->assertSee("18:00");
        $response->assertSee("01:00");
    }
}