@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')

@section('title', $title)
@section('breadcrumb', 'Dashboard')

@section('content')
    @can('access-pic')
        <div class="mb-7 flex flex-wrap items-center justify-between gap-5">
            <div>
                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Workspace PIC Ruangan</p>
                <h1 class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">{{ $title }}</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">Periksa pengajuan dan pastikan ketersediaan ruangan tanggung jawab Anda.</p>
            </div>
            <a href="{{ route('pic.approvals.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primaryDark">
                <x-schedule-icon name="clock" />Permintaan Approval
            </a>
        </div>
    @else
        <div class="sr-only">
            <h1 class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">{{ $title }}</h1>
            <p class="mt-3 break-words text-sm leading-6 text-slate-600">Selamat datang, {{ $user->name }}.</p>
        </div>
    @endcan
    @if(session('status'))
        <div role="status" class="mb-6 rounded-xl border border-secondaryLight bg-surface p-4 text-sm text-primaryDark">{{ session('status') }}</div>
    @endif
    @can('access-pic')
        @include('pic.dashboard')
    @else
        @include('user.dashboard')
    @endcan
@endsection
