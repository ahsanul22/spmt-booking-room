@extends(auth()->user()->role === \App\Models\User::ROLE_USER ? 'layouts.user' : 'layouts.schedule')

@section('title', $title)
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="mb-6">
        <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Workspace ruang rapat</p>
        <h1 class="text-3xl font-bold tracking-tight text-primaryDark sm:text-4xl">{{ $title }}</h1>
        <p class="mt-3 break-words text-sm leading-6 text-slate-600">Selamat datang, {{ $user->name }}.</p>
    </div>
    @if(session('status'))
        <div role="status" class="mb-6 rounded-xl border border-secondaryLight bg-surface p-4 text-sm text-primaryDark">{{ session('status') }}</div>
    @endif
    @can('access-pic')
        @include('pic.dashboard')
    @else
        @include('user.dashboard')
    @endcan
@endsection
