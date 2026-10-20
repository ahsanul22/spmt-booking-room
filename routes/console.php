<?php

use App\Models\GuestRegistration;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('guests:prune', function () {
    $count = GuestRegistration::where('expires_at', '<=', now())->delete();
    $this->info("Registrasi tamu kedaluwarsa dihapus: {$count}");
})->purpose('Hapus registrasi tamu kedaluwarsa tanpa menghapus riwayat booking');
