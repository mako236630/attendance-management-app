<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class AdminrequestController extends Controller
{
    public function index($attendance_correct_request_id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($attendance_correct_request_id);

        $attendance->view_in = date('H:i', strtotime($attendance->punched_in_at));

        foreach ($attendance->rests as $rest) {
            $rest->view_in = date('H:i', strtotime($rest->rest_in_at));
            $rest->view_out = $rest->rest_out_at ? date('H:i', strtotime($rest->rest_out_at)) : '';
        }

        return view("admin.approve", compact('attendance'));
    }

    public function update($attendance_correct_request_id)
    {
        $attendance = Attendance::with('rests')->findOrFail($attendance_correct_request_id);

        DB::transaction(
            function () use ($attendance) {
                $attendance->update([
                    'punched_in_at'  => $attendance->request_in_at,
                    'punched_out_at' => $attendance->request_out_at,
                    'status'         => 2,
                    'request_in_at'  => null,
                    'request_out_at' => null,
                ]);

                foreach ($attendance->rests as $rest) {
                    if ($rest->requested_in_at && $rest->requested_out_at) {
                        $rest->update(
                            [
                                'rest_in_at' => $rest->requested_in_at,
                                'rest_out_at' => $rest->requested_out_at,
                                'requested_in_at' => null,
                                'requested_out_at' => null,
                            ]
                        );
                    }
                }
            }
        );

        return redirect()->back();
    }
}
