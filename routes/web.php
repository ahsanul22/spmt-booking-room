<?php

use App\Http\Controllers\DashboardController;
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
    return view('welcome');
});

Route::middleware(['auth', 'can:access-general'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->defaults('title', 'Dashboard Pegawai')->name('dashboard');
    Route::view('/rooms', 'placeholder', ['title' => 'Daftar Ruangan', 'message' => 'Modul daftar ruangan akan dikembangkan pada tahap berikutnya.'])->name('rooms.index');
    Route::view('/schedule', 'placeholder', ['title' => 'Jadwal Ruangan', 'message' => 'Modul jadwal ruangan akan dikembangkan pada tahap berikutnya.'])->name('schedule.index');
});

Route::middleware(['auth', 'can:access-employee'])->group(function () {
    Route::view('/my-bookings', 'placeholder', ['title' => 'My Booking', 'message' => 'Modul booking belum tersedia.'])->name('my-bookings.index');
});

Route::prefix('pic')->name('pic.')->middleware(['auth', 'can:access-pic'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->defaults('title', 'Dashboard PIC')->name('dashboard');
    Route::view('/rooms', 'placeholder', ['title' => 'Ruangan Saya', 'message' => 'Modul ruangan yang dikelola PIC akan dikembangkan pada tahap berikutnya.'])->name('rooms.index');
    Route::view('/approvals', 'placeholder', ['title' => 'Permintaan Approval', 'message' => 'Modul approval akan dikembangkan pada tahap berikutnya.'])->name('approvals.index');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:access-admin'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->defaults('title', 'Dashboard Super Admin')->name('dashboard');
    Route::view('/users', 'placeholder', ['title' => 'Kelola User', 'message' => 'Modul User Management akan dikembangkan pada Tahap 3.'])->name('users.index');
    Route::view('/organizational-units', 'placeholder', ['title' => 'Unit Organisasi', 'message' => 'Modul unit organisasi akan dikembangkan pada Tahap 3.'])->name('organizational-units.index');
    Route::view('/floors', 'placeholder', ['title' => 'Lantai', 'message' => 'Modul lantai akan dikembangkan pada Tahap 3.'])->name('floors.index');
    Route::view('/facilities', 'placeholder', ['title' => 'Fasilitas', 'message' => 'Modul fasilitas akan dikembangkan pada Tahap 3.'])->name('facilities.index');
    Route::view('/rooms', 'placeholder', ['title' => 'Ruang Rapat', 'message' => 'Modul pengelolaan ruang rapat akan dikembangkan pada Tahap 3.'])->name('rooms.index');
    Route::view('/bookings', 'placeholder', ['title' => 'Semua Booking', 'message' => 'Modul booking belum tersedia.'])->name('bookings.index');
});

require __DIR__.'/auth.php';
