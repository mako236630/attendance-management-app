<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $displayMonth = $request->input('display_month', now()->format('Y-m'));
        $date = \Carbon\Carbon::parse($displayMonth);

        $prevMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::with('rests')
            ->where('user_id', Auth::id())
            ->whereYear('punched_in_at', $date->year)
            ->whereMonth('punched_in_at', $date->month)
            ->get();

        return view('attendance.list', compact('attendances', 'displayMonth', 'prevMonth', 'nextMonth'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        $attendance->view_in = date('H:i', strtotime($attendance->punched_in_at));

        foreach($attendance->rests as $rest) {
            $rest->view_in = date('H:i', strtotime($rest->rest_in_at));
            $rest->view_out = $rest->rest_out_at ? date('H:i', strtotime($rest->rest_out_at)) : '';
        }

        return view("attendance.detail", compact('attendance'));
    }

    public function updateRequest(DetailRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $date = date('Y-m-d', strtotime($attendance->punched_in_at));
        $requested_in = $date . ' ' . $request->in_time . ':00';
        $requested_out = $request->out_time ? $date . ' ' . $request->out_time . ':00' : null;

        $attendance->update([
            'requested_in_at' =>$requested_in,
            'requested_out_at' => $requested_out,
            'note' => $request->note,
            'status' => 1,
        ]);

        if ($request->has('rests')) {
            foreach ($request->rests as $restId => $data) {
                $rest = Rest::findOrFail($restId);
                $rest->update([
                    'requested_in_at' => $date . ' ' . $data['in'] . ':00',
                    'requested_out_at' => $data['out'] ? $date . ' ' . $data['out'] . ':00' : null,
                ]);
            }
        }

        return redirect()->route('attendance.update', ['id' => $id]);
    }
}
