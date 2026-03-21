<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Attendance;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminstafflistController extends Controller
{
    public function index()
    {
        $users = User::where('is_admin', 0)->get();

        return view("admin.stafflist", compact('users'));
    }

    public function show(Request $request, $id)
    {
        $displayDate = Carbon::today();

        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $displayMonth = Carbon::parse($month);

        $start = $displayMonth->copy()->startOfMonth();
        $end = $displayMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('punched_in_at', [$start, $end])->whereIn('status', [0, 1, 2])
            ->get();

        $user = User::find($id);

        return view('admin.attendance', compact('displayDate', 'attendances', 'user', 'displayMonth'));
    }

    public function exportCsv($id, $month)
    {
        $user = User::findOrFail($id);
        $displayMonth = \Carbon\Carbon::parse($month);
        $start = $displayMonth->copy()->startOfMonth();
        $end = $displayMonth->copy()->endOfMonth();

        $attendances = Attendance::with('rests')
            ->where('user_id', $id)
            ->whereBetween('punched_in_at', [$start, $end])
            ->orderBy('punched_in_at', 'asc')
            ->get();

        return new StreamedResponse(function () use ($user, $displayMonth, $attendances) {
            $stream = fopen('php://output', 'w');

            // 文字化け防止（Excel用）
            stream_filter_append($stream, 'convert.iconv.utf-8/cp932//TRANSLIT');

            fputcsv($stream, ['日付', '名前', '出勤', '退勤', '休憩時間', '勤務合計']);

            $formatTime = function ($minutes) {
                $h = floor($minutes / 60);
                $m = $minutes % 60;
                return sprintf('%02d:%02d', $h, $m);
            };

            // データ行（31日分のループ）
            for ($i = 1; $i <= $displayMonth->daysInMonth; $i++) {
                $currentDate = $displayMonth->copy()->day($i);
                $attendance = $attendances->first(fn($item) => \Carbon\Carbon::parse($item->punched_in_at)->isSameDay($currentDate));

                if ($attendance) {
                    $punchIn = \Carbon\Carbon::parse($attendance->punched_in_at);
                    $punchOut = $attendance->punched_out_at ? \Carbon\Carbon::parse($attendance->punched_out_at) : null;

                    $totalRestMinutes = 0;
                    foreach ($attendance->rests as $rest) {
                        if ($rest->rest_in_at && $rest->rest_out_at) {
                            $totalRestMinutes += \Carbon\Carbon::parse($rest->rest_out_at)->diffInMinutes(\Carbon\Carbon::parse($rest->rest_in_at));
                        }
                    }

                    $workMinutes = 0;
                    if ($punchOut) {
                        $totalDiff = $punchOut->diffInMinutes($punchIn);
                        $workMinutes = $totalDiff - $totalRestMinutes;
                    }
                        fputcsv($stream, [
                            $currentDate->format('Y/m/d'),
                            $user->name,
                            $punchIn->format('H:i'),
                            $punchOut ? $punchOut->format('H:i') : '',
                            $formatTime($totalRestMinutes),
                            $punchOut ? $formatTime($workMinutes) : '勤務中'
                        ]);
                    } else {
                        // データがない日は空の行を入れる
                        fputcsv($stream, [$currentDate->format('Y/m/d'), $user->name, '', '', '', '']);
                    }
                }
            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $user->name . '_' . $month . '.csv"',
        ]);
    }
}
