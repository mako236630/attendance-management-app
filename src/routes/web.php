<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\RquestlistController;
use App\Http\Controllers\AdminattendancelistController;
use App\Http\Controllers\AdminstafflistController;
use App\Http\Controllers\AdminrequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/admin/login', function () {
    return view('auth.adminlogin');
})->name('admin.login');
Route::post('/admin/login', [Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'store']);

Route::post('/admin/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/admin/login');
})->name('admin.logout');

Route::middleware(['auth', 'Admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminattendancelistController::class, 'index'])->name('admin.list');
    Route::get('/admin/attendance/{id}', [AdminattendancelistController::class, 'show'])->name('admin.show');
    Route::post('/admin/attendance/{id}', [AdminattendancelistController::class, 'updateRequest'])->name("admin.update");
    Route::get("/admin/staff/list", [AdminstafflistController::class, 'index'])->name("staff.list");
    Route::get("/admin/atendance/staff/{id}", [AdminstafflistController::class, "show"])->name("attendance.staff");
    Route::get("/stamp_correction_request/approve/{attendance_correct_request_id}", [AdminrequestController::class, "index"])->name('admin.request');
    Route::post("/stamp_correction_request/approve/{attendance_correct_request_id}", [AdminrequestController::class, "update"])->name('request.update');


    Route::get("/admin/attendance/export/{id}/{month}", [AdminstafflistController::class, 'exportCsv'])->name('export.csv');
});



Route::middleware(['auth', 'RejectAdmin'])->group(function () {

    Route::get("/attendance", [AttendanceController::class, "index"])->name("attendance.index");
    Route::post("/attendance", [AttendanceController::class, "store"])->name("attendance.store");
    Route::get("attendance/list", [AttendanceListController::class, "index"])->name("attendance.list");
    Route::get("/attendance/detail/{id}", [AttendanceListController::class, "show"])->name("attendance.show");
    Route::post("/attendance/detail/{id}", [AttendanceListController::class, "updateRequest"])->name("attendance.update");
});

Route::get("/stamp_correction_request/list", [RquestlistController::class, "index"])->name("requestlist.index")->middleware('auth');
