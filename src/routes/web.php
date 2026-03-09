<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\RquestlistController;

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
Route::middleware('auth')->group(function () {
    
Route::get("/attendance", [AttendanceController::class, "index"])->name("attendance.index");
Route::post("/attendance", [AttendanceController::class, "store"])->name("attendance.store");
Route::get("attendance/list", [AttendanceListController::class, "index"])->name("attendance.list");
Route::get("/attendance/detail/{id}", [AttendanceListController::class, "show"])->name("attendance.show");
});
Route::post("/attendance/detail/{id}", [AttendanceListController::class, "updateRequest"])->name("attendance.update");
Route::get("/stamp_correction_request/list", [RquestlistController::class, "index"])->name("requestlist.index");
