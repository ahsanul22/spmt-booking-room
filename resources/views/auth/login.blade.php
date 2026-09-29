<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Pemesanan Ruang Rapat · Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative min-h-screen bg-gradient-to-br from-[#d4e9f7] via-[#ebf4fa] to-[#bfe1f7] font-sans text-text antialiased overflow-x-hidden selection:bg-primary selection:text-white">
    <a href="#login-form" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:rounded-lg focus:bg-surface focus:p-4 focus:shadow-lg">Langsung ke form login</a>

    <!-- Decorative background organic shapes (matching reference mockup) -->
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden z-0">
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-secondary/25 blur-3xl"></div>
        <div class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-secondaryLight/50 blur-3xl"></div>
        <!-- Organic wave shape at bottom-left as in reference mockup -->
        <svg class="absolute -bottom-16 -left-16 h-80 w-80 text-secondary/35" viewBox="0 0 200 200" fill="currentColor">
            <path d="M 0 70 C 60 130, 70 50, 130 115 C 180 165, 150 200, 200 200 L 0 200 Z" />
        </svg>
        <svg class="absolute -bottom-6 -left-6 h-56 w-56 text-primary/20" viewBox="0 0 200 200" fill="currentColor">
            <path d="M 0 100 C 40 140, 80 80, 140 135 C 190 185, 170 200, 200 200 L 0 200 Z" />
        </svg>
    </div>

    <!-- Centered Fullscreen Container Wrapper -->
    <main class="relative z-10 flex min-h-screen w-full items-center justify-center p-3 sm:p-5 lg:p-6">
        
        <!-- Main Hero Card Container (Continuous Port Photo Background) -->
        <div class="relative w-full max-w-[1040px] rounded-3xl sm:rounded-[32px] overflow-hidden bg-slate-900 shadow-[0_25px_60px_-15px_rgba(14,51,106,0.30)] border border-white/80 grid grid-cols-1 lg:grid-cols-12 min-h-[540px] lg:min-h-[580px]">
            
            <!-- Continuous Port Photo across the entire card -->
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-0">
                <img src="{{ asset('images/login-port.jpg') }}" alt="" class="h-full w-full object-cover object-center">
            </div>

            <!-- Left Column: Port Photo & Branding Hero (7 cols / ~58%) -->
            <div class="relative z-10 lg:col-span-7 flex flex-col justify-between p-6 sm:p-8 lg:p-10 min-h-[300px] lg:min-h-full">
                <!-- Soft ocean dark gradient at bottom for text readability -->
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#0E336A]/95 via-[#0E336A]/30 to-transparent"></div>

                <!-- Top Left: Pelindo Logo in white translucent badge -->
                <div class="relative z-10 self-start">
                    <div class="inline-flex items-center rounded-2xl bg-white/95 px-4 py-2.5 shadow-md backdrop-blur-sm">
                        <x-application-logo variant="light" class="h-7 sm:h-8 w-auto max-w-[170px] sm:max-w-[195px] object-contain" />
                    </div>
                </div>

                <!-- Bottom Left: Wave Decoration & Bold Portal Title -->
                <div class="relative z-10 mt-12 lg:mt-auto pt-4">
                    <!-- 3 Wave Lines Icon -->
                    <svg class="h-5 w-11 text-secondaryLight drop-shadow-sm mb-3" viewBox="0 0 44 16" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3 Q 7 0, 12 3 T 22 3 T 32 3 T 42 3" stroke-linecap="round"/>
                        <path d="M2 8 Q 7 5, 12 8 T 22 8 T 32 8 T 42 8" stroke-linecap="round"/>
                        <path d="M2 13 Q 7 10, 12 13 T 22 13 T 32 13 T 42 13" stroke-linecap="round"/>
                    </svg>

                    <h2 class="text-2xl sm:text-3xl lg:text-[38px] font-black uppercase leading-tight tracking-tight text-white drop-shadow-md">
                        PORTAL PEMESANAN<br>RUANG RAPAT
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm font-bold uppercase tracking-[0.18em] text-secondaryLight drop-shadow-sm">
                        PELINDO MULTI TERMINAL
                    </p>
                </div>
            </div>

            <!-- Right Column: Blurred Port Background with Floating Login Card (5 cols / ~42%) -->
            <div class="relative z-10 lg:col-span-5 flex items-center justify-center p-4 sm:p-6 lg:p-7">
                <!-- Dimming & soft blur overlay on the right side so floating card stands out -->
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[#0E336A]/45 backdrop-blur-[2px]"></div>

                <!-- Floating White Login Card (matches reference mockup exactly) -->
                <div class="relative z-10 w-full max-w-[360px] sm:max-w-[375px] rounded-2xl sm:rounded-3xl bg-surface p-5 sm:p-6 shadow-[0_15px_35px_rgba(0,0,0,0.22)] border border-slate-100">
                    
                    <!-- Decorative Wavy Lines on Card -->
                    <svg class="absolute top-3.5 left-3.5 h-4 w-9 text-slate-300 pointer-events-none" viewBox="0 0 36 14" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M1 2 Q 5 0, 9 2 T 17 2 T 25 2 T 33 2" stroke-linecap="round"/>
                        <path d="M1 6 Q 5 4, 9 6 T 17 6 T 25 6 T 33 6" stroke-linecap="round"/>
                        <path d="M1 10 Q 5 8, 9 10 T 17 10 T 25 10 T 33 10" stroke-linecap="round"/>
                    </svg>
                    <svg class="absolute bottom-3.5 right-3.5 h-4 w-9 text-slate-300 pointer-events-none" viewBox="0 0 36 14" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M1 2 Q 5 0, 9 2 T 17 2 T 25 2 T 33 2" stroke-linecap="round"/>
                        <path d="M1 6 Q 5 4, 9 6 T 17 6 T 25 6 T 33 6" stroke-linecap="round"/>
                        <path d="M1 10 Q 5 8, 9 10 T 17 10 T 25 10 T 33 10" stroke-linecap="round"/>
                    </svg>

                    <!-- Center Logo inside card -->
                    <div class="flex justify-center pt-1">
                        <x-application-logo variant="light" class="h-7 w-auto max-w-[155px] object-contain" />
                    </div>

                    <!-- Card Heading -->
                    <div class="mt-3.5 text-center">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Selamat Datang</h1>
                        <p class="mt-0.5 text-xs text-slate-500">Silakan Masuk ke Akun Anda</p>
                    </div>

                    <!-- Session / Error Alerts -->
                    @if (session('status'))
                        <div role="status" class="mt-3 rounded-xl border border-secondaryLight bg-secondaryLight/20 px-3 py-2 text-xs text-primaryDark font-medium">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div role="alert" class="mt-3 rounded-xl border border-danger/25 bg-danger/5 px-3 py-2 text-xs text-danger">
                            <p class="font-semibold">Login belum berhasil.</p>
                            <ul class="mt-1 list-disc space-y-0.5 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if($selectedRoom ?? null)
                        <div class="mt-3 rounded-xl border border-secondaryLight bg-secondaryLight/20 p-2.5 text-xs text-primaryDark">
                            Setelah login, lanjutkan booking <strong>{{ $selectedRoom->name }}</strong>.
                        </div>
                    @endif

                    <!-- Form -->
                    <form id="login-form" method="POST" action="{{ route('login') }}" class="mt-3.5 space-y-3">
                        @csrf

                        <!-- Email Input -->
                        <div>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Email / Username Pegawai"
                                    @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                                    @class([
                                        'block w-full rounded-xl border bg-slate-50/50 pl-10 pr-4 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20',
                                        'border-danger' => $errors->has('email'),
                                        'border-slate-300' => ! $errors->has('email')
                                    ])>
                            </div>
                            @error('email')
                                <p id="email-error" class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Kata Sandi"
                                    @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                                    @class([
                                        'block w-full rounded-xl border bg-slate-50/50 pl-10 pr-10 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20',
                                        'border-danger' => $errors->has('password'),
                                        'border-slate-300' => ! $errors->has('password')
                                    ])>
                                <button type="button" onclick="togglePasswordVisibility()" aria-label="Tampilkan atau sembunyikan kata sandi" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg id="eye-closed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                    <svg id="eye-open" class="hidden h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p id="password-error" class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Lupa Kata Sandi link -->
                        <div class="flex items-center justify-end">
                            <button type="button" onclick="alert('Untuk mengatur ulang kata sandi, silakan hubungi Administrator IT atau PIC SPMT.');" class="text-[11px] font-semibold text-primary hover:text-primaryDark transition-colors">
                                Lupa Kata Sandi?
                            </button>
                        </div>

                        <!-- Remember Me checkbox -->
                        <div>
                            <label for="remember" class="inline-flex cursor-pointer items-center gap-2 text-xs text-slate-600 select-none">
                                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))
                                    @if ($errors->has('remember')) aria-invalid="true" aria-describedby="remember-error" @endif
                                    class="h-4 w-4 rounded border-slate-300 text-primary accent-primary focus:ring-primary/20">
                                <span>Ingat Saya</span>
                                <span class="sr-only">Remember Me</span>
                            </label>
                            @error('remember')
                                <p id="remember-error" class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-1">
                            <button type="submit" class="w-full rounded-xl bg-[#0066b2] hover:bg-[#004f8c] active:scale-[0.99] transition-all py-2.5 px-4 text-xs sm:text-sm font-bold uppercase tracking-wider text-white shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                MASUK
                            </button>
                        </div>
                    </form>

                    <!-- Footer note inside card -->
                    <p class="mt-3.5 text-center text-xs text-slate-500">
                        Belum punya akun? <span class="font-semibold text-primary">Hubungi Admin</span>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Script for show/hide password -->
    <script>
        function togglePasswordVisibility() {
            var pwd = document.getElementById('password');
            var eyeOpen = document.getElementById('eye-open');
            var eyeClosed = document.getElementById('eye-closed');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            } else {
                pwd.type = 'password';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
