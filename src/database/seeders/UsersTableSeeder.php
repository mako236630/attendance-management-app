<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 管理者用ユーザー
        $admin_user = User::create([
            "name" => "管理者",
            "email" => "admin@example.com",
            "password" => Hash::make("adminpass"),
            "is_admin" => 1,
        ]);

        $user = User::create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);


        $start = Carbon::now()->subMonths(2)->startOfMonth();
        $end = Carbon::now();

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            if ($date->isWeekend()) continue;

            if ($date->isToday()) continue;

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'created_at' => $date->format('Y-m-d H:i:s'),
                'punched_in_at' => $date->copy()->setTime(9, 0, 0),
                'punched_out_at' => $date->copy()->setTime(18, 0, 0),
            ]);

            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_in_at' => $date->copy()->setTime(12, 0, 0),
                'rest_out_at' => $date->copy()->setTime(13, 0, 0),
            ]);
        }

        $users = User::factory(6)->create();

        foreach ($users as $user) {
            $attendances = Attendance::factory(90)->create(['user_id' => $user->id]);

            foreach ($attendances as $attendance) {
                Rest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'rest_in_at' => (clone $attendance->punched_in_at)->modify('+4 hours'),
                    'rest_out_at' => (clone $attendance->punched_in_at)->modify('+5 hours'),
                ]);
            }
        }
    }
}
