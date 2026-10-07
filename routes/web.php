<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\FloorController;
use App\Http\Controllers\Admin\OrganizationalUnitController;
use App\Http\Controllers\Admin\RoomAccessController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomPicController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BookingPreparationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\Pic\ApprovalController;
use App\Http\Controllers\RoomDetailsController;
use App\Http\Controllers\UserBookingController;
use Illuminate\Support\Facades\Route;

// Master data accepts legacy IDs and readable ID-name route keys.
Route::pattern('room', '[1-9][0-9]*(?:-[a-z0-9]+(?:-[a-z0-9]+)*)?');
Route::pattern('floor', '[1-9][0-9]*(?:-[a-z0-9]+(?:-[a-z0-9]+)*)?');
Route::pattern('facility', '[1-9][0-9]*(?:-[a-z0-9]+(?:-[a-z0-9]+)*)?');
Route::pattern('unit', '[1-9][0-9]*(?:-[a-z0-9]+(?:-[a-z0-9]+)*)?');

Route::get('/', HomeController::class)->name('home');
Route::view('/jadwal-ruangan', 'public.schedule')->name('public.schedule');

Route::middleware(['auth', 'can:access-employee'])->group(function () {
    Route::get('/my-bookings', [UserBookingController::class, 'index'])->name('my-bookings.index');
    Route::get('/my-bookings/create', [BookingPreparationController::class, 'legacy'])->name('my-bookings.create');
    Route::get('/rooms/{room}/book', BookingPreparationController::class)->name('rooms.book');
    Route::get('/my-bookings/{booking}', [UserBookingController::class, 'show'])->whereNumber('booking')->name('my-bookings.show');
});

Route::middleware(['auth', 'can:access-general'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->defaults('title', 'Dashboard Pegawai')->name('dashboard');
    Route::view('/rooms', 'user.rooms.index')->name('rooms.index');
    Route::get('/rooms/{room}', RoomDetailsController::class)->name('rooms.show');
    Route::view('/schedule', 'shared.schedule')->name('schedule.index');
});

Route::prefix('pic')->name('pic.')->middleware(['auth', 'can:access-pic'])->group(function () {
    Route::get('/reports/monthly', [MonthlyReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/monthly/export', [MonthlyReportController::class, 'index'])->name('reports.export');
    Route::get('/reports/monthly/print', [MonthlyReportController::class, 'index'])->name('reports.print');
    Route::get('/dashboard', DashboardController::class)->defaults('title', 'Dashboard PIC')->name('dashboard');
    Route::get('/rooms', [App\Http\Controllers\Pic\RoomController::class, 'index'])->name('rooms.index');
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/history', [ApprovalController::class, 'history'])->name('approvals.history');
    Route::get('/approvals/{approval}', [ApprovalController::class, 'show'])->whereNumber('approval')->name('approvals.show');
    Route::post('/approvals/{approval}', [ApprovalController::class, 'decide'])->whereNumber('approval')->name('approvals.decide');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:access-admin'])->group(function () {
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/history', [ApprovalController::class, 'history'])->name('approvals.history');
    Route::get('/approvals/{approval}', [ApprovalController::class, 'show'])->whereNumber('approval')->name('approvals.show');
    Route::post('/approvals/{approval}', [ApprovalController::class, 'decide'])->whereNumber('approval')->name('approvals.decide');
    Route::get('/reports/monthly', [MonthlyReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/monthly/export', [MonthlyReportController::class, 'index'])->name('reports.export');
    Route::get('/reports/monthly/print', [MonthlyReportController::class, 'index'])->name('reports.print');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::patch('/users/{user}/status', [UserController::class, 'status'])->whereNumber('user')->name('users.status');
    Route::patch('/users/{user}/password', [UserController::class, 'resetPassword'])->whereNumber('user')->name('users.reset-password');
    Route::resource('users', UserController::class)->whereNumber('user')->except('destroy');
    Route::patch('/organizational-units/{unit}/status', [OrganizationalUnitController::class, 'status'])->name('organizational-units.status');
    Route::resource('organizational-units', OrganizationalUnitController::class)
        ->parameters(['organizational-units' => 'unit'])->except('destroy');
    Route::patch('/floors/{floor}/status', [FloorController::class, 'status'])->name('floors.status');
    Route::resource('floors', FloorController::class)->except('destroy');
    Route::patch('/facilities/{facility}/status', [FacilityController::class, 'status'])->name('facilities.status');
    Route::resource('facilities', FacilityController::class)->except('destroy');
    Route::patch('/rooms/{room}/status', [RoomController::class, 'status'])->name('rooms.status');
    Route::resource('rooms', RoomController::class);
    Route::get('/rooms/{room}/pics', [RoomPicController::class, 'edit'])->name('rooms.pics');
    Route::put('/rooms/{room}/pics', [RoomPicController::class, 'update'])->name('rooms.pics.update');
    Route::delete('/rooms/{room}/pics/{pic}', [RoomPicController::class, 'destroy'])->whereNumber('pic')->name('rooms.pics.destroy');
    Route::get('/rooms/{room}/access', [RoomAccessController::class, 'edit'])->name('rooms.access');
    Route::put('/rooms/{room}/access', [RoomAccessController::class, 'update'])->name('rooms.access.update');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->whereNumber('booking')->name('bookings.show');
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
});

require __DIR__.'/auth.php';
