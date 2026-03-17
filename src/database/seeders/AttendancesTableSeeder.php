<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('is_admin', 0)->get();

        foreach ($users as $user) {
            for ($i = 90; $i >= 2; $i--) {
                $date = Carbon::now()->subDays($i);

                if ($date->isWeekend()) continue;

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'status' => 0,
                    'punched_in_at' => $date->format('Y-m-d') . ' 09:00:00',
                    'punched_out_at' => $date->format('Y-m-d') . ' 18:00:00',
                    'requested_in_at' => null,
                    'requested_out_at' => null,
                    'note' => null,
                    'created_at' => $date,
                ]);

                Rest::create([
                    'attendance_id' => $attendance->id,
                    'rest_in_at' => $date->format('Y-m-d') . ' 12:00:00',
                    'rest_out_at' => $date->format('Y-m-d') . ' 13:00:00',
                ]);
            }

            $yesterday = Carbon::yesterday();
            $atd1 = Attendance::create([
                'user_id' => $user->id,
                'status'  => 1,
                'punched_in_at'   => $yesterday->format('Y-m-d') . ' 09:00:00',
                'punched_out_at'  => $yesterday->format('Y-m-d') . ' 18:00:00',
                'requested_in_at' => $yesterday->format('Y-m-d') . ' 10:00:00',
                'requested_out_at' => $yesterday->format('Y-m-d') . ' 18:00:00',
                'note' => '電車遅延の為',
                'created_at' => $yesterday,
            ]);
            Rest::create([
                'attendance_id' => $atd1->id,
                'rest_in_at' => $yesterday->format('Y-m-d') . ' 12:00:00',
                'rest_out_at' => $yesterday->format('Y-m-d') . ' 13:00:00',
                'requested_in_at' => $yesterday->format('Y-m-d') . ' 12:00:00',
                'requested_out_at' => $yesterday->format('Y-m-d') . ' 13:00:00',
            ]);

            Rest::create([
                'attendance_id' => $atd1->id,
                'rest_in_at' => $yesterday->format('Y-m-d') . ' 15:00:00',
                'rest_out_at' => $yesterday->format('Y-m-d') . ' 16:00:00',
                'requested_in_at' => $yesterday->format('Y-m-d') . ' 17:00:00',
                'requested_out_at' => $yesterday->format('Y-m-d') . ' 17:30:00',
            ]);
        }
    }
}
