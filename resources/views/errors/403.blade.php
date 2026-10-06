@extends(auth()->user()?->can('access-general') ? (auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule') : 'layouts.public')

@section('title', 'Akses Ditolak')
@section('breadcrumb', 'Akses Ditolak')

@section('content')
    <section class="rounded-2xl border border-slate-200 bg-surface p-6 sm:p-9">
        <p class="font-semibold text-primary">403</p>
        <h1 class="mt-3 text-3xl font-bold text-primaryDark">Akses Ditolak</h1>
        <p class="mt-4 text-base leading-7 text-slate-600">Anda tidak memiliki izin untuk membuka halaman ini.</p>
        <a class="workspace-button-secondary mt-6" href="{{ auth()->user()?->can('access-general') ? route(auth()->user()->dashboardRouteName()) : route('login') }}">Kembali</a>
    </section>
@endsection
