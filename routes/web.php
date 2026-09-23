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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontendSkeletonController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'can:access-employee'])->group(function () {
    Route::get('/rooms/search', FrontendSkeletonController::class)->defaults('view', 'user.rooms.search')->name('rooms.search');
    Route::get('/my-bookings', FrontendSkeletonController::class)->defaults('view', 'user.bookings.index')->name('my-bookings.index');
    Route::get('/my-bookings/create', FrontendSkeletonController::class)->defaults('view', 'user.bookings.create')->name('my-bookings.create');
    Route::get('/my-bookings/{booking}', FrontendSkeletonController::class)->defaults('view', 'user.bookings.show')->name('my-bookings.show');
});

Route::middleware(['auth', 'can:access-general'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->defaults('title', 'Dashboard Pegawai')->name('dashboard');
    Route::get('/rooms', FrontendSkeletonController::class)->defaults('view', 'user.rooms.index')->name('rooms.index');
    Route::get('/rooms/{room}', FrontendSkeletonController::class)->defaults('view', 'user.rooms.show')->name('rooms.show');
    Route::get('/schedule', FrontendSkeletonController::class)->defaults('view', 'shared.schedule')->name('schedule.index');
});

Route::prefix('pic')->name('pic.')->middleware(['auth', 'can:access-pic'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->defaults('title', 'Dashboard PIC')->name('dashboard');
    Route::get('/rooms', FrontendSkeletonController::class)->defaults('view', 'pic.rooms.index')->name('rooms.index');
    Route::get('/approvals', FrontendSkeletonController::class)->defaults('view', 'pic.approvals.index')->name('approvals.index');
    Route::get('/approvals/history', FrontendSkeletonController::class)->defaults('view', 'pic.approvals.history')->name('approvals.history');
    Route::get('/approvals/{approval}', FrontendSkeletonController::class)->defaults('view', 'pic.approvals.show')->name('approvals.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:access-admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::patch('/users/{user}/status', [UserController::class, 'status'])->whereNumber('user')->name('users.status');
    Route::patch('/users/{user}/password', [UserController::class, 'resetPassword'])->whereNumber('user')->name('users.reset-password');
    Route::resource('users', UserController::class)->whereNumber('user')->except('destroy');
    Route::patch('/organizational-units/{unit}/status', [OrganizationalUnitController::class, 'status'])->whereNumber('unit')->name('organizational-units.status');
    Route::resource('organizational-units', OrganizationalUnitController::class)
        ->parameters(['organizational-units' => 'unit'])->whereNumber('unit')->except('destroy');
    Route::patch('/floors/{floor}/status', [FloorController::class, 'status'])->whereNumber('floor')->name('floors.status');
    Route::resource('floors', FloorController::class)->whereNumber('floor')->except('destroy');
    Route::patch('/facilities/{facility}/status', [FacilityController::class, 'status'])->whereNumber('facility')->name('facilities.status');
    Route::resource('facilities', FacilityController::class)->whereNumber('facility')->except('destroy');
    Route::patch('/rooms/{room}/status', [RoomController::class, 'status'])->whereNumber('room')->name('rooms.status');
    Route::resource('rooms', RoomController::class)->whereNumber('room')->except('destroy');
    Route::get('/rooms/{room}/pics', [RoomPicController::class, 'edit'])->whereNumber('room')->name('rooms.pics');
    Route::put('/rooms/{room}/pics', [RoomPicController::class, 'update'])->whereNumber('room')->name('rooms.pics.update');
    Route::delete('/rooms/{room}/pics/{pic}', [RoomPicController::class, 'destroy'])->whereNumber(['room', 'pic'])->name('rooms.pics.destroy');
    Route::get('/rooms/{room}/access', [RoomAccessController::class, 'edit'])->whereNumber('room')->name('rooms.access');
    Route::put('/rooms/{room}/access', [RoomAccessController::class, 'update'])->whereNumber('room')->name('rooms.access.update');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
});

require __DIR__.'/auth.php';
