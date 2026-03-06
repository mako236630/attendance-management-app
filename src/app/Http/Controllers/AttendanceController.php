<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = \Carbon\Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())->whereDate('created_at', $today)->first();

        $rest = null;

        if ($attendance) {
            $rest = Rest::where('attendance_id', $attendance->id)->whereDate('created_at', $today)->latest()->first();
        }

        if (!$attendance) {
            $status = '勤務外';

        } elseif ($attendance->punched_out_at) {
            $status = '退勤済';

        } elseif ($rest && is_null($rest->rest_out_at)) {
            $status = '休憩中';
            
        } else{
            $status = '出勤中';
        }

        return view("attendance.attendance", compact('status'));
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;

        $attendance = Attendance::where('user_id', Auth::id())->whereDate('created_at', now())->first();

        $rest = null;

        if ($attendance) {
            $rest = Rest::where('attendance_id', $attendance->id)->whereDate('created_at', now())->latest()->first();
        }

        if ($request->is_working) {
            Attendance::create([
                'user_id' => $user->id,
                'punched_in_at' => now(),
            ]);
        } elseif ($request->is_off) {
            $attendance->update([
                'punched_out_at' => now(),
            ]);
        } elseif ($request->is_break) {
            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_in_at' => now(),
            ]);
        } elseif ($request->is_breakout) {
            $rest->update([
                'rest_out_at' => now(),
            ]);
        }

        return redirect()->back();
    }
}
