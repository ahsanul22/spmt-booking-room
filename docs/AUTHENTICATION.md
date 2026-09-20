# Authentication Singkat

Tahap 2A menyediakan login, logout, Remember Me, dan dashboard sementara. Tahap 2B menambahkan akses per role; lihat `docs/AUTHORIZATION.md`.

## Mencoba

Pastikan PostgreSQL aktif dan `.env` sudah benar, kemudian jalankan:

```sh
php artisan serve
```

Buka `http://127.0.0.1:8000/login`. Tidak perlu `npm install` atau `npm run dev`: halaman tahap ini memakai HTML Blade tanpa asset build.

Gunakan `admin@example.test`, `pic@example.test`, atau `pegawai@example.test` dengan password development `password`, jika password belum diubah. Akun aktif menuju dashboard sesuai role: `/admin/dashboard`, `/pic/dashboard`, atau `/dashboard`. Nama, role, dan unit berasal dari database. Akun tanpa unit menampilkan “Belum ditentukan”.

## Route

| Method | URL | Kegunaan |
| --- | --- | --- |
| GET | `/login` | Form login; user yang sudah login diarahkan ke dashboard. |
| POST | `/login` | Memvalidasi email/password, status aktif, dan memulai session. |
| GET | `/dashboard` | Bukti login berhasil; guest diarahkan ke login. |
| POST | `/logout` | Mengakhiri session dan kembali ke login. |

Tidak ada route registrasi publik, reset password, email verification, atau profil. Halaman `/` bawaan tetap tersedia. Sejak Tahap 2B, role membatasi area melalui Gate Laravel.

## File utama

- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`: login/logout, diambil dari stub resmi Breeze Blade.
- `app/Http/Requests/Auth/LoginRequest.php`: validasi input, pemeriksaan akun aktif melalui guard Laravel, dan throttle login dari Breeze.
- `app/Http/Middleware/EnsureAccountIsActive.php`: mengakhiri akses session/Remember Me ketika akun dinonaktifkan.
- `routes/auth.php` dan `routes/web.php`: route login, logout, dan dashboard.
- `resources/views/auth/login.blade.php`, `dashboard.blade.php`, dan `layouts/base.blade.php`: tampilan sederhana yang boleh diganti tim frontend.
- `tests/Feature/AuthenticationTest.php` dan `tests/PostgresTestCase.php`: test authentication dengan schema PostgreSQL sementara.

Breeze 1.29.1 dipasang melalui Composer sebagai dependency development. Hanya scaffolding yang diperlukan yang diambil, bukan seluruh fitur installer. Middleware web didaftarkan di `app/Http/Kernel.php`; sejak Tahap 2B tujuan setelah login ditetapkan terpusat di `User::dashboardRouteName()`.

## Perilaku keamanan

Password diverifikasi menggunakan hashing Laravel. Akun nonaktif dengan password benar menerima pesan untuk menghubungi administrator; password salah tetap mendapat pesan kredensial tidak sesuai. Remember Me memakai cookie Laravel. Login memperbarui session ID; logout membuang session dan memperbarui token CSRF. Form login/logout wajib POST dengan CSRF.

Jalankan `php artisan test` untuk memeriksa authentication dan fondasi database. Test membuat/menghapus schema sementara, bukan mereset tabel development. Migration Tahap 1 tidak diubah.
