<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-sans text-text antialiased">
    <a href="#login" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:rounded-lg focus:bg-surface focus:p-4">Langsung ke form login</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <aside class="relative flex flex-col overflow-hidden bg-primaryDark px-6 py-8 text-white sm:px-10 lg:px-14 lg:py-12">
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-32 hidden h-96 w-96 rounded-full border-[60px] border-secondary/10 lg:block"></div>
            <div class="relative flex items-center justify-between gap-5 lg:block">
                <div aria-label="Pelindo Multi Terminal">
                    <div class="text-3xl font-bold italic tracking-tight">pelindo<span class="text-secondary" aria-hidden="true">∿</span></div>
                    <div class="mt-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-secondaryLight">Multi Terminal</div>
                </div>
                <p class="text-xs font-medium text-secondaryLight lg:mt-8 lg:border-t lg:border-white/15 lg:pt-6 lg:text-sm">Meeting Room</p>
            </div>
            <div class="relative my-auto hidden max-w-md py-16 lg:block">
                <span class="inline-flex rounded-2xl border border-white/10 bg-white/10 p-4 text-secondaryLight"><x-schedule-icon name="room" class="h-8 w-8" /></span>
                <p class="mt-8 text-[11px] font-semibold uppercase tracking-[0.2em] text-secondaryLight">Ruang untuk berkolaborasi</p>
                <h2 class="mt-4 text-4xl font-bold leading-tight tracking-tight xl:text-5xl">Ruang yang tepat.<br>Diskusi lebih produktif.</h2>
                <p class="mt-6 max-w-sm text-sm leading-7 text-secondaryLight">Mulai kolaborasi yang baik dengan perencanaan yang tepat bersama Pelindo Multi Terminal.</p>
                <div class="mt-10 flex items-center gap-4 border-t border-white/15 pt-6">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-secondaryLight"><x-schedule-icon /></span>
                    <div><p class="text-sm font-semibold">Satu ruang, banyak ide.</p><p class="mt-1 text-xs leading-5 text-secondaryLight">Workspace internal untuk kebutuhan ruang rapat tim.</p></div>
                </div>
            </div>
            <p class="relative hidden text-xs text-secondaryLight lg:block">PT Pelindo Multi Terminal</p>
        </aside>
        <div class="flex min-w-0 flex-col">
            <header class="hidden min-h-[80px] items-center justify-between gap-4 border-b border-slate-200/70 bg-surface px-9 text-xs lg:flex">
                <span class="font-semibold text-primaryDark">Meeting Room</span><span class="text-slate-500">Internal Workspace</span>
            </header>
            <main id="login" class="flex flex-1 items-center justify-center px-5 py-10 sm:px-9 lg:py-12">
                <section class="w-full max-w-md rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm sm:p-9" aria-labelledby="login-title">
                    <span class="inline-flex rounded-xl bg-secondaryLight/30 p-3 text-primary"><x-schedule-icon name="room" class="h-6 w-6" /></span>
                    <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Selamat datang kembali</p>
                    <h1 id="login-title" class="mt-2 text-3xl font-bold tracking-tight text-primaryDark">Login</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Masuk dengan akun Anda untuk mengakses workspace ruang rapat.</p>
                    @if (session('status'))
                        <p role="status" class="mt-6 rounded-xl border border-secondaryLight bg-secondaryLight/20 px-4 py-3 text-sm text-primaryDark">{{ session('status') }}</p>
                    @endif
                    @if ($errors->any())
                        <div role="alert" class="mt-6 rounded-xl border border-danger/25 bg-danger/5 px-4 py-3 text-sm text-danger">
                            <p class="font-semibold">Login belum berhasil.</p>
                            <ul class="mt-2 list-disc space-y-1 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
                        @csrf
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-primaryDark">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                                @class(['block min-h-[48px] w-full rounded-xl border bg-surface px-4 py-3 text-base outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20', 'border-danger' => $errors->has('email'), 'border-slate-300' => ! $errors->has('email')])>
                            @error('email')
                                <p id="email-error" class="mt-2 text-xs leading-5 text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-primaryDark">Password</label>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                                @class(['block min-h-[48px] w-full rounded-xl border bg-surface px-4 py-3 text-base outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20', 'border-danger' => $errors->has('password'), 'border-slate-300' => ! $errors->has('password')])>
                            @error('password')
                                <p id="password-error" class="mt-2 text-xs leading-5 text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="remember" class="inline-flex min-h-[44px] cursor-pointer items-center gap-3 text-sm text-slate-600">
                                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))
                                    @if ($errors->has('remember')) aria-invalid="true" aria-describedby="remember-error" @endif
                                    class="h-4 w-4 rounded border-slate-300 accent-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
                                Remember Me
                            </label>
                            @error('remember')
                                <p id="remember-error" class="mt-1 text-xs leading-5 text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primaryDark focus-visible:outline-primary">
                            Login<x-schedule-icon name="arrow" class="h-4 w-4" />
                        </button>
                    </form>
                    <p class="mt-7 border-t border-slate-100 pt-5 text-xs leading-6 text-slate-600">Belum memiliki akun atau mengalami kendala masuk? Hubungi administrator untuk bantuan akses.</p>
                </section>
            </main>
            <footer class="flex flex-wrap justify-center gap-x-3 gap-y-1 px-5 pb-6 text-center text-xs leading-5 text-slate-500">
                <span>PT Pelindo Multi Terminal</span><span aria-hidden="true">·</span><span>Internal Workspace</span>
            </footer>
        </div>
    </div>
</body>
</html>
