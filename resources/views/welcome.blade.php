@extends('layouts.public')
@section('title', 'Selamat Datang di Booking Room')
@section('content')
<section class="mb-12 grid min-h-[58vh] items-center gap-10 rounded-3xl border border-slate-200/80 bg-surface p-6 sm:p-10 lg:grid-cols-[minmax(0,1.3fr)_minmax(300px,0.7fr)] lg:p-12 xl:p-16">
    <div class="max-w-3xl">
        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-primary">Ruang untuk berkolaborasi</p>
        <h1 class="mt-5 text-3xl font-bold leading-tight tracking-tight text-primaryDark sm:text-4xl xl:text-5xl">Pertemuan yang baik<br>dimulai dari ruang<br class="hidden xl:block"> yang tepat.</h1>
        <p class="mt-6 max-w-2xl text-sm leading-8 text-slate-600">Selamat datang di Booking Room PT Pelindo Multi Terminal. Lihat jadwal ruangan, rencanakan pertemuan, dan kelola booking melalui akun pegawai Anda.</p>
        <div class="mt-8 flex flex-wrap gap-4"><a href="{{ route('login') }}" class="workspace-button">Login Pegawai<x-schedule-icon name="arrow" class="h-4 w-4" /></a><a href="#room-schedule" class="workspace-button-secondary">Lihat Jadwal Ruangan</a></div>
        <p class="mt-4 text-xs leading-6 text-slate-600">Lihat jadwal ruangan di bawah. Login diperlukan untuk melakukan booking.</p>
    </div>
    <div class="rounded-2xl bg-primaryDark p-7 text-white sm:p-9">
        <x-schedule-icon name="room" class="h-10 w-10 text-secondaryLight" />
        <h2 class="mt-5 text-xl font-semibold">Rapat terencana, kerja lebih nyaman.</h2>
        <ol class="mt-7 list-decimal space-y-5 pl-5 text-sm leading-7 text-secondaryLight"><li>Login dengan akun pegawai.</li><li>Pilih ruangan dan periksa jadwal penggunaannya.</li><li>Isi waktu dan agenda, lalu pantau hasil di My Booking.</li></ol>
        <p class="mt-7 border-t border-white/15 pt-5 text-xs leading-6 text-secondaryLight">Ajukan minimal {{ config('booking.minimum_notice_hours') }} jam sebelum rapat. Ruangan tertentu memerlukan persetujuan PIC.</p>
    </div>
</section>
<section id="room-schedule" class="scroll-mt-6" aria-labelledby="public-schedule-title">
    <h2 id="public-schedule-title" class="text-2xl font-bold text-primaryDark sm:text-3xl">Jadwal Ruangan</h2>
    <p class="mb-6 mt-3 text-sm leading-6 text-slate-600">Lihat tanggal, ruangan, dan jam penggunaan sebelum membuat booking. Jadwal menampilkan ruangan publik. Semua waktu dalam WIB.</p>
    <livewire:public-room-schedule />
</section>
@endsection
