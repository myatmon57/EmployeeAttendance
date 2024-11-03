<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/allAttendance', [App\Http\Controllers\HomeController::class, 'allAttendance'])->name('allAttendance');
Route::get('/allUserInfo', [App\Http\Controllers\UserInfoController::class, 'index'])->name('allUserInfo');
Route::post('/clickAttendance', [App\Http\Controllers\HomeController::class, 'clickAttendance'])->name('clickAttendance');
Route::get('/users/export-csv', [App\Http\Controllers\UserInfoController::class, 'exportCsv'])->name('users.export-csv');
Route::get('/attendance/export-csv', [App\Http\Controllers\HomeController::class, 'exportCsv'])->name('downloadAllAttendance');
