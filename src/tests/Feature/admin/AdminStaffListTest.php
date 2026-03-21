<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;
use Carbon\CarbonPeriod;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user1 = User::create([
            "name" => "一般ユーザー1",
            "email" => "test0001@test.com",
            "password" => Hash::make("password0001"),
            "is_admin" => 0,
        ]);

        $user2 = User::create([
            "name" => "一般ユーザー2",
            "email" => "test0002@test.com",
            "password" => Hash::make("password0002"),
            "is_admin" => 0,
        ]);

        $user3 = User::create([
            "name" => "一般ユーザー3",
            "email" => "test0003@test.com",
            "password" => Hash::make("password0003"),
            "is_admin" => 0,
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/staff/list");
        $response->assertStatus(200);

        $response->assertSee('スタッフ一覧');
        $response->assertSee('一般ユーザー1');
        $response->assertSee('一般ユーザー2');
        $response->assertSee('一般ユーザー3');
        $response->assertSee('test0001@test.com');
        $response->assertSee('test0002@test.com');
        $response->assertSee('test0003@test.com');
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー1",
            "email" => "test0001@test.com",
            "password" => Hash::make("password0001"),
            "is_admin" => 0,
        ]);

        $attendance1 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-02-15 09:00:00",
            "punched_out_at" => "2025-02-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance1->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $attendance2 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance2->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $attendance3 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-04-15 09:00:00",
            "punched_out_at" => "2025-04-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance3->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);

        $response->assertSee('一般ユーザー1さんの勤怠');
        $response->assertSee('2025/03');
        $response->assertSee('03/15(土)');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
    }

    public function test_「前月」を押下した時に表示月に前月の情報が表示される()
    {
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー1",
            "email" => "test0001@test.com",
            "password" => Hash::make("password0001"),
            "is_admin" => 0,
        ]);

        $attendance1 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-02-15 09:00:00",
            "punched_out_at" => "2025-02-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance1->id,
            "rest_in_at" => "2025-02-15 12:00:00",
            "rest_out_at" => "2025-02-15 13:00:00",
        ]);

        $attendance2 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance2->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $attendance3 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-04-15 09:00:00",
            "punched_out_at" => "2025-04-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance3->id,
            "rest_in_at" => "2025-04-15 12:00:00",
            "rest_out_at" => "2025-04-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/staff/{$user->id}?month=2025-02");

        $response->assertSee('一般ユーザー1さんの勤怠');
        $response->assertSee('2025/02');
        $response->assertSee('02/15(土)');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
    }

    public function test_「翌月」を押下した時に表示月に翌月の情報が表示される()
    {
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー1",
            "email" => "test0001@test.com",
            "password" => Hash::make("password0001"),
            "is_admin" => 0,
        ]);

        $attendance1 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-02-15 09:00:00",
            "punched_out_at" => "2025-02-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance1->id,
            "rest_in_at" => "2025-02-15 12:00:00",
            "rest_out_at" => "2025-02-15 13:00:00",
        ]);

        $attendance2 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance2->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $attendance3 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-04-15 09:00:00",
            "punched_out_at" => "2025-04-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance3->id,
            "rest_in_at" => "2025-04-15 12:00:00",
            "rest_out_at" => "2025-04-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/staff/{$user->id}?month=2025-04");

        $response->assertSee('一般ユーザー1さんの勤怠');
        $response->assertSee('2025/04');
        $response->assertSee('04/15(火)');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
    }

    public function test__「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $this->travelTo(now()->parse('2025-03-15 09:00:00'));

        $adminuser = User::create([
            "name" => "管理者ユーザー",
            "email" => "testadmin@test.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            "name" => "一般ユーザー1",
            "email" => "test0001@test.com",
            "password" => Hash::make("password0001"),
            "is_admin" => 0,
        ]);

        $attendance1 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-02-15 09:00:00",
            "punched_out_at" => "2025-02-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance1->id,
            "rest_in_at" => "2025-02-15 12:00:00",
            "rest_out_at" => "2025-02-15 13:00:00",
        ]);

        $attendance2 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance2->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $attendance3 = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-04-15 09:00:00",
            "punched_out_at" => "2025-04-15 18:00:00",
            "status" => 0,
        ]);

        Rest::create([
            "attendance_id" => $attendance3->id,
            "rest_in_at" => "2025-04-15 12:00:00",
            "rest_out_at" => "2025-04-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/staff/{$user->id}");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->get("/admin/attendance/{$attendance2->id}");

        $response->assertSee('勤怠詳細');
        $response->assertSee('一般ユーザー1');
        $response->assertSee('2025年');
        $response->assertSee('3月15日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}