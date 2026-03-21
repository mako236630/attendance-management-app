<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

class AdminRequestApproveTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test__承認待ちの修正申請が全て表示されている()
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
        $user2 = User::create([
            "name" => "一般ユーザー2",
            "email" => "test002@test.com",
            "password" => Hash::make("password002"),
            "is_admin" => 0,
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "requested_in_at" => "2025-03-15 10:00:00",
            "requested_out_at" => "2025-03-15 18:00:00",
            "note" => "電車遅延",
            "status" => 1,
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
            "requested_in_at" => "2025-03-15 12:00:00",
            "requested_out_at" => "2025-03-15 13:00:00"
        ]);

        $attendance2 = Attendance::create([
            "user_id" => $user2->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "requested_in_at" => "2025-03-15 12:00:00",
            "requested_out_at" => "2025-03-15 18:00:00",
            "note" => "私用外出",
            "status" => 1,
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance2->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
            "requested_in_at" => "2025-03-15 12:00:00",
            "requested_out_at" => "2025-03-15 13:00:00"
        ]);

        $response = $this->actingAs($adminuser)->get("/stamp_correction_request/list");
        $response->assertStatus(200);
        $response->assertSee('申請一覧');
        $response->assertSee('一般ユーザー');
        $response->assertSee('承認待ち');
        $response->assertSee('2025/03/15');
        $response->assertSee('電車遅延');

        $response->assertSee('一般ユーザー2');
        $response->assertSee('承認待ち');
        $response->assertSee('2025/03/15');
        $response->assertSee('私用外出');
    }

    public function test__承認済みの修正申請が全て表示されている()
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
        $user2 = User::create([
            "name" => "一般ユーザー2",
            "email" => "test002@test.com",
            "password" => Hash::make("password002"),
            "is_admin" => 0,
        ]);

        $attendance = Attendance::create([
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 10:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "note" => "電車遅延",
            "status" => 2,
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $attendance2 = Attendance::create([
            "user_id" => $user2->id,
            "punched_in_at" => "2025-03-15 09:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "note" => "私用外出",
            "status" => 2,
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance2->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
        ]);

        $response = $this->actingAs($adminuser)->get("/stamp_correction_request/list?tab=requestok");
        $response->assertStatus(200);
        $response->assertSee('申請一覧');
        $response->assertSee('一般ユーザー');
        $response->assertSee('承認済み');
        $response->assertSee('2025/03/15');
        $response->assertSee('電車遅延');

        $response->assertSee('一般ユーザー2');
        $response->assertSee('承認済み');
        $response->assertSee('2025/03/15');
        $response->assertSee('私用外出');
    }

    public function test__修正申請の詳細内容が正しく表示されている()
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
            "requested_in_at" => "2025-03-15 10:00:00",
            "requested_out_at" => "2025-03-15 18:00:00",
            "note" => "電車遅延",
            "status" => 1,
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
            "requested_in_at" => "2025-03-15 12:00:00",
            "requested_out_at" => "2025-03-15 13:00:00"
        ]);


        $response = $this->actingAs($adminuser)->get("/stamp_correction_request/list");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->get("/stamp_correction_request/approve/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('一般ユーザー');
        $response->assertSee('2025年');
        $response->assertSee('3月15日');
        $response->assertSee('10:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('電車遅延');
    }

    public function test__修正申請の承認処理が正しく行われる()
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
            "requested_in_at" => "2025-03-15 10:00:00",
            "requested_out_at" => "2025-03-15 18:00:00",
            "note" => "電車遅延",
            "status" => 1,
        ]);

        $rest = Rest::create([
            "attendance_id" => $attendance->id,
            "rest_in_at" => "2025-03-15 12:00:00",
            "rest_out_at" => "2025-03-15 13:00:00",
            "requested_in_at" => "2025-03-15 12:00:00",
            "requested_out_at" => "2025-03-15 13:00:00"
        ]);


        $response = $this->actingAs($adminuser)->get("/stamp_correction_request/list");
        $response->assertStatus(200);

        $response = $this->actingAs($adminuser)->get("/stamp_correction_request/approve/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee('<button class="update__button" type="submit">承認</button>', false);

        $response = $this->actingAs($adminuser)->post("/stamp_correction_request/approve/{$attendance->id}");

        $this->assertDatabaseHas("attendances", [
            "user_id" => $user->id,
            "punched_in_at" => "2025-03-15 10:00:00",
            "punched_out_at" => "2025-03-15 18:00:00",
            "note" => "電車遅延",
            "status" => 2,
            ]);

        $this->followRedirects($response)->assertSee("承認済み");
    }
}