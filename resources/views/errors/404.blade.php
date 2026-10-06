@extends(auth()->user()?->can('access-general') ? (auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule') : 'layouts.public')
@section('title', 'Halaman Tidak Ditemukan')
@section('breadcrumb', 'Halaman Tidak Ditemukan')
@section('content')
    <section class="rounded-2xl border border-slate-200 bg-surface p-6 sm:p-9">
        <p class="font-semibold text-primary">404</p>
        <h1 class="mt-3 text-3xl font-bold text-primaryDark">Halaman Tidak Ditemukan</h1>
        <p class="mt-4 text-base leading-7 text-slate-600">Halaman atau data yang Anda cari tidak tersedia.</p>
        <a class="workspace-button-secondary mt-6" href="{{ auth()->user()?->can('access-general') ? route(auth()->user()->dashboardRouteName()) : route('home') }}">Kembali</a>
    </section>
@endsection
