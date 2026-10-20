@extends('layouts.visitor')
@section('title', 'Pendataan Tamu')
@section('visitor-background', 'harbor')
@section('content')
    <div class="flex flex-1 items-center justify-center py-4 sm:py-7">
        <section aria-labelledby="registration-title" class="w-full max-w-3xl rounded-3xl border border-white/80 bg-surface p-6 shadow-xl shadow-primaryDark/5 sm:p-10 lg:p-12">
            <div class="mb-8 text-center">
                <p class="mb-3 text-sm font-bold uppercase tracking-[0.18em] text-primary">Selamat datang</p>
                <h1 id="registration-title" class="text-3xl font-bold text-primaryDark sm:text-4xl">Pendataan Tamu</h1>
                <p class="mx-auto mt-4 max-w-xl text-base leading-7 text-slate-600">Lengkapi identitas Anda untuk melihat jadwal ruangan. Resepsionis akan membantu pengajuan booking Anda.</p>
            </div>
            <x-form-feedback />
            <form method="POST" action="{{ route('guests.store') }}" class="grid gap-6 sm:grid-cols-2">
                @csrf
                <x-workspace-field name="name" label="Nama lengkap" :value="old('name')" autocomplete="name" maxlength="255" required />
                <x-workspace-field name="organization" label="Instansi" :value="old('organization')" autocomplete="organization" maxlength="255" required />
                <x-workspace-field name="nip" label="NIP (opsional)" :value="old('nip')" maxlength="50" hint="Kosongkan jika tidak memiliki NIP." />
                <x-workspace-field name="contact" label="Nomor kontak" type="tel" :value="old('contact')" autocomplete="tel" maxlength="50" required />
                <div class="sm:col-span-2"><x-workspace-field name="purpose" label="Tujuan kunjungan" type="textarea" :rows="3" :value="old('purpose')" maxlength="2000" required /></div>
                <div class="space-y-5 border-t border-slate-200 pt-6 sm:col-span-2">
                    <p class="text-sm leading-6 text-slate-600">Data digunakan untuk membantu pengajuan ruangan. Setelah masa aktif berakhir, data registrasi dihapus. Nama dan instansi pada booking tetap tercatat dalam riwayat dan laporan.</p>
                    <button class="workspace-button w-full justify-center py-3.5" type="submit">Daftar dan lihat jadwal <span aria-hidden="true">&rarr;</span></button>
                </div>
            </form>
        </section>
    </div>
@endsection
