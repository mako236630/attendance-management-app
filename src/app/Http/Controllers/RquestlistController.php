<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RquestlistController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->is_admin === 1) {
            $query = Attendance::with('user');
        } else {
            $query = Attendance::where('user_id', $user->id);
        }

        $requestwaiting = url($request->path() . '?' . http_build_query(array_merge($request->query(), ['tab' => ''])));
        $requestok = url($request->path() . '?' . http_build_query(array_merge($request->query(), ['tab' => 'requestok'])));

        $tab = $request->query("tab");

        if ($tab === "requestok") {
            $attendances = $query->where('status', 2)->get();
        } else {
            $attendances = $query->where('status', 1)->get();
        }
        
        if ($user->is_admin === 1) {
            return view("admin.requestlist", compact('tab', 'attendances', 'requestwaiting', 'requestok'));
        }

        return view("attendance.requestlist", compact('tab', 'attendances', 'requestwaiting', 'requestok'));
    }
}
