<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\DetailRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;


class AdminattendancelistController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $displaytime = Carbon::parse($date);
        
        $dateParam = $request->query('date');
        $displayDate = $dateParam ? Carbon::parse($dateParam) : Carbon::today();

        $prevDate = $displayDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $displayDate->copy()->addDay()->format('Y-m-d');

        $attendances = Attendance::with(['user', 'rests'])
            ->whereDate('punched_in_at', $displayDate->format('Y-m-d'))
            ->get();

        return view("admin.list", compact('displaytime','attendances', 'displayDate', 'prevDate', 'nextDate'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        $attendance->view_in = date('H:i', strtotime($attendance->punched_in_at));

        foreach ($attendance->rests as $rest) {
            $rest->view_in = date('H:i', strtotime($rest->rest_in_at));
            $rest->view_out = $rest->rest_out_at ? date('H:i', strtotime($rest->rest_out_at)) : '';
        }

        return view("admin.detail", compact('attendance'));
    }

    public function updateRequest(DetailRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $date = date('Y-m-d', strtotime($attendance->punched_in_at));
        $requested_in = $date . ' ' . $request->in_time . ':00';
        $requested_out = $request->out_time ? $date . ' ' . $request->out_time . ':00' : null;

        $attendance->update([
            'requested_in_at' => $requested_in,
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

        return redirect()->route('admin.update', ['id' => $id]);
    }
}
