# Hak Akses Singkat — Tahap 2B

Role menentukan area yang boleh dibuka. Unit kerja hanya ditampilkan dan belum membatasi akses. Semua halaman modul di bawah masih placeholder, bukan fitur sebenarnya.

## Catatan penjelasan untuk diskusi berikutnya

Pengguna meminta istilah Laravel dijelaskan singkat, memakai contoh dari project, dan dibedakan antara bawaan Laravel dengan nama yang dipilih dalam implementasi.

- `can` adalah middleware bawaan Laravel untuk memeriksa izin (Gate). `access-admin`, `access-pic`, `access-general`, dan `access-employee` adalah nama izin yang dibuat di project, bukan role baru. Aturannya ada di `AuthServiceProvider`; `@can` memakai aturan yang sama untuk menampilkan menu.
- `employee` berarti pegawai. `access-employee` saat ini mengizinkan role `user` dan `room_pic` membuka My Booking. Nama `access-my-bookings` pernah disebut sebagai alternatif yang lebih jelas, tetapi belum diterapkan; nama aktual tetap `access-employee`.
- `name('admin.')` atau `name('pic.')` menambahkan awalan pada **nama route**. Contoh `admin.` + `dashboard` menjadi `admin.dashboard`. Titik bukan bagian URL. `prefix('admin')` yang membuat URL `/admin/dashboard`; `route('admin.dashboard')` menghasilkan URL tersebut.
- `routes/auth.php` tetap file route. Login/logout dipisahkan ke sana agar `routes/web.php` rapi, lalu dimuat melalui `require __DIR__.'/auth.php'`.
- Tidak perlu `npm run dev` untuk halaman saat ini karena Blade memakai HTML sederhana tanpa asset Vite. `php artisan serve` menjalankan Laravel; NPM/Vite diperlukan nanti jika frontend memakai asset yang perlu diproses.

## Siapa boleh membuka apa?

| Area | User | Room PIC | Admin |
| --- | --- | --- | --- |
| Dashboard Pegawai, daftar ruang, jadwal | Ya | Ya | Ya |
| My Booking | Ya | Ya | Belum |
| Dashboard PIC, Ruangan Saya, Permintaan Approval | Tidak | Ya | Ya |
| Dashboard Admin dan administrasi | Tidak | Tidak | Ya |

Admin boleh membuka placeholder PIC, tetapi tidak otomatis menjadi PIC seluruh ruangan. Assignment nantinya tetap memakai `room_pics`. Hak booking pribadi admin belum diputuskan; admin memiliki placeholder Semua Booking.

## Route

Semua route berikut GET dan dilindungi `auth` + `can`:

| Gate | URL |
| --- | --- |
| `access-general` | `/dashboard`, `/rooms`, `/schedule` |
| `access-employee` | `/my-bookings` |
| `access-pic` | `/pic/dashboard`, `/pic/rooms`, `/pic/approvals` |
| `access-admin` | `/admin/dashboard`, `/admin/users`, `/admin/organizational-units`, `/admin/floors`, `/admin/facilities`, `/admin/rooms`, `/admin/bookings` |

Setelah login: pegawai menuju `/dashboard`, PIC menuju `/pic/dashboard`, admin menuju `/admin/dashboard`. Membuka `/login` saat sudah login juga mengarah ke dashboard masing-masing. Guest diarahkan ke login; akses dengan role salah mendapat **403 Akses Ditolak**, termasuk jika mengetik URL sendiri. Role tidak dikenal juga ditolak. Aturan akun nonaktif dan logout dari Tahap 2A tetap berlaku.

## File utama

- `app/Providers/AuthServiceProvider.php`: satu tempat untuk aturan Gate; tidak ada package atau middleware role tambahan.
- `app/Models/User.php`: `dashboardRouteName()` menentukan tujuan dashboard; dipakai controller login, guest middleware, dan navigasi.
- `app/Http/Controllers/DashboardController.php`: identitas authenticated user dan judul dashboard.
- `routes/web.php`: kelompok route dan placeholder.
- `resources/views/layouts/navigation.blade.php`: menu memakai `@can` sesuai Gate backend.
- `resources/views/dashboard.blade.php`, `placeholder.blade.php`, `layouts/base.blade.php`, `errors/403.blade.php`: view sederhana bersama.
- `tests/Feature/AuthorizationTest.php`: akses langsung URL, navigasi, dashboard, akun nonaktif, dan role tidak dikenal. Test memakai schema PostgreSQL sementara existing.

Jalankan `php artisan serve`, buka `/login`, lalu gunakan akun development dari `docs/DATABASE.md`. Tidak perlu `npm run dev`. Jalankan `php artisan test` untuk verifikasi otomatis. Tahap 3 belum dikerjakan.

## Pembaruan lokasi dan privasi 2026-10-19

DEC-051 mempertahankan Admin terpusat dan PIC per assignment room. Lokasi adalah filter data; tidak memberikan izin tambahan atau membuat role Admin cabang. Admin mengelola lokasi melalui GET/POST `/admin/branches` dan PUT/PATCH `/admin/branches/{branch}` di grup auth + access-admin. PIC tetap hanya memutuskan dan melaporkan room penugasannya. Pegawai melihat jadwal seluruh lokasi setelah login, dengan data privat booking tetap dibatasi seperti sebelumnya.

DEC-053 menutup akses jadwal sebelum login: `/jadwal-ruangan` kini memakai auth + access-general lalu redirect `/schedule`. PublicRoomSchedule legacy juga memeriksa Gate pada setiap request/refresh Livewire. Beranda hanya menampilkan informasi umum.

## Aturan lokasi aktif — DEC-054

Koreksi 2026-10-19 menggantikan asumsi filter-only DEC-051 di atas. Admin mengelola semua lokasi dan memiliki dropdown; pegawai/PIC tidak memiliki dropdown dan hanya mengakses gabungan lokasi yang dicentang Admin pada Kelola User. Pivot branch_user memisahkan izin lokasi dari role/unit organisasi. Nol izin menolak seluruh data lokasi.

Batas berlaku di query dan service, bukan hanya Blade: katalog, jadwal/polling, detail dan URL booking, submit/retry, riwayat pribadi, badge/dashboard PIC, approval, laporan/CSV/print dan penerima notifikasi. PIC membutuhkan izin lokasi sekaligus assignment room. Penambahan assignment tidak otomatis memberi izin. Pencabutan izin langsung efektif pada request/refresh berikutnya; data booking tidak dihapus.

## Izin resepsionis (DEC-058)

users.is_receptionist merupakan izin tambahan, bukan role. Admin mengaturnya melalui SaveUserRequest/kelola akun. User/PIC aktif yang ditunjuk dapat memilih tamu aktif di kantor ruangan saat booking, tetap dibatasi izin lokasi/unit/ruangan. Akun Admin tidak mendapat hak booking dari flag ini. Flag tidak mass assignable. BookingService memeriksa user yang dikunci ulang sebelum pemrosesan referensi/idempotensi tamu; Livewire tidak memberikan daftar tamu kepada akun biasa atau setelah izin dicabut. Persetujuan booking tetap kewenangan PIC/Admin existing.
