# Decisions

Keputusan berikut telah ditentukan sebagai dasar pengembangan. Catat alasan jika suatu keputusan diubah dan tambahkan keputusan arsitektur atau bisnis baru ketika disepakati. Pertanyaan terbuka pada `PROJECT_CONTEXT.md` belum menjadi keputusan.

## DEC-001 — Role

Sistem menggunakan tiga role utama:

- User.
- Room PIC.
- Super Admin.

Alasan: menjaga authorization tetap sederhana dan tidak menjadikan jabatan organisasi sebagai role aplikasi.

## DEC-002 — Organizational Unit

Struktur organisasi nantinya menggunakan hierarchical organizational unit.

Alasan: Direktorat, Divisi, dan Departemen dapat disimpan dalam satu struktur yang fleksibel dan mudah menyesuaikan perubahan organisasi.

## DEC-003 — PIC Ruangan

PIC dikaitkan dengan ruangan, bukan otomatis dengan divisi. Satu ruang dapat mempunyai lebih dari satu PIC.

## DEC-004 — Room Access

Sistem mendukung `all` dan `restricted`. Default desain adalah ruang dapat digunakan seluruh pegawai kecuali terdapat aturan khusus.

## DEC-005 — Room Approval

Approval dikonfigurasi per ruangan menggunakan `requires_approval`. Tidak semua room wajib melalui approval.

## DEC-006 — Frontend

Frontend tahap development dibuat basic dan functional menggunakan Blade/HTML sederhana. Frontend final akan dikembangkan terpisah oleh anggota tim lain menggunakan HTML dan Tailwind CSS. Struktur Blade harus mudah diganti tanpa mengubah backend.

## DEC-007 — Booking Conflict

Booking berstatus Pending dan Approved dianggap memblokir slot waktu. Rejected dan Cancelled tidak memblokir slot waktu.

## DEC-008 — Scope

Sistem saat ini tidak mencakup catering, snack, konsumsi, rapat eksternal, pengelolaan tamu eksternal, WhatsApp, SMS, payment, atau fitur di luar kebutuhan booking ruang rapat. Notification tahap awal hanya internal website; email tidak diperlukan saat ini.

## DEC-009 — Fondasi PostgreSQL (Tahap 1)

- Database memakai PostgreSQL sesuai arahan pengguna, dengan migration Laravel 10. Tidak menambah package role.
- Satu user memiliki satu `role` (`user`, `room_pic`, `super_admin`) dan satu `organizational_unit_id` nullable sesuai prompt Tahap 1. Hak booking PIC/admin belum ditentukan.
- Role, status, tipe unit, dan tipe akses memakai enum schema Laravel (CHECK pada PostgreSQL). Kapasitas menggunakan CHECK eksplisit `>= 0` karena PostgreSQL tidak menerapkan unsigned integer.
- Penghapusan unit yang masih direferensikan parent/user dan lantai yang masih memiliki ruang dibatasi (restrict). Pivot memakai cascade untuk membersihkan hubungan saja, dengan primary key gabungan agar tidak duplikat.
- Kode ruang nullable dan unik; nomor lantai dan nama fasilitas juga unik agar master data tidak ambigu. Nama ruang/unit tidak harus unik secara global.
- `role`, `is_active`, dan `organizational_unit_id` pada User tidak dibuka untuk mass assignment. Modul admin nantinya harus mengatur field tersebut secara eksplisit setelah authorization.
- Validasi calon PIC disediakan lewat `EligibleRoomPic`. Belum ada workflow penugasan atau trigger lintas tabel; pemanggil relasi nanti wajib memvalidasi PIC.
- Data contoh hanya untuk local/testing. Test memakai schema PostgreSQL sementara agar constraint database asli diuji tanpa menghapus data development.

## DEC-010 — Penggabungan Migration User

Atas permintaan pengguna, seluruh kolom users disatukan dalam migration pembuatannya agar mudah dibaca. Migration organizational_units dijalankan lebih dulu karena users mereferensikannya. Perapian dilakukan saat fondasi masih development dan belum dipush; riwayat migration lokal diselaraskan tanpa reset data. Untuk database yang sudah digunakan bersama/produksi nanti, perubahan struktur berikutnya memakai migration baru.

## DEC-011 — Authentication Dasar (Tahap 2A)

- Laravel tetap 10.50.3. Menambah `laravel/breeze` 1.29.1 sebagai dependency development yang kompatibel Laravel 10; dependency existing tidak di-upgrade.
- Mengambil scaffolding controller session dan LoginRequest dari stub resmi Breeze Blade. Tidak menjalankan installer penuh karena turut memasang registrasi, reset password, profil, dan frontend di luar scope. View login/dashboard memakai Blade HTML sederhana tanpa ketergantungan build Vite atau tambahan package NPM.
- Memakai guard session `web`, hashing/Remember Me Laravel, middleware `auth`/`guest`, dan CSRF bawaan. Pembatasan percobaan login mengikuti Breeze (5 kegagalan per kombinasi email/IP sebelum dibatasi sementara).
- `attemptWhen` memeriksa `is_active` setelah password divalidasi Laravel dan sebelum session login dibuat. Pesan akun tidak aktif hanya diberikan jika kredensial benar. Middleware `EnsureAccountIsActive` juga mengakhiri session/Remember Me akun yang kemudian dinonaktifkan; ini bukan authorization role.
- Semua login berhasil diarahkan ke `/dashboard`; role hanya ditampilkan. Logout melalui POST, invalidate session, regenerate token CSRF, lalu redirect `/login`.
- Registrasi publik, reset password, email verification, dan profil tidak memiliki route pada tahap ini. Akun memakai tabel/seeder existing.
- Helper `Tests\PostgresTestCase` menyatukan mekanisme schema PostgreSQL sementara existing untuk test fondasi dan authentication. Tidak memakai RefreshDatabase yang berisiko mereset schema development.

## DEC-012 — Role dan Authorization (Tahap 2B)

- Menggunakan Gate Laravel terpusat di `AuthServiceProvider`, middleware bawaan `auth` + `can`, dan Blade `@can`. Tidak menambah package, role, atau middleware role custom karena mekanisme bawaan sudah cukup.
- `access-general`: ketiga role boleh melihat dashboard pegawai, daftar ruang, dan jadwal. `access-employee`: user/PIC boleh membuka My Booking. `access-pic`: PIC/admin boleh membuka placeholder PIC. `access-admin`: hanya admin boleh membuka administrasi. Semua Gate juga mensyaratkan akun aktif.
- Izin area PIC untuk admin hanya akses halaman, bukan penugasan sebagai PIC atau izin approval seluruh ruangan. Relasi `room_pics` tetap menentukan assignment ketika modul tersebut dibuat. Tidak menggunakan bypass global `Gate::before`.
- Admin belum mendapat My Booking karena hak booking pribadi admin belum ditentukan; admin mendapat placeholder Semua Booking dan informasi umum. PIC mendapat seluruh halaman pegawai sesuai instruksi Tahap 2B.
- Mapping dashboard tunggal di `User::dashboardRouteName()`: user ke `/dashboard`, PIC ke `/pic/dashboard`, admin ke `/admin/dashboard`. Digunakan saat login, redirect guest middleware, dan link Dashboard. Menggantikan redirect seragam Tahap 2A.
- Akses terlarang selalu HTTP 403 dengan halaman aman. Role tidak dikenal ditolak tanpa fallback; diuji dengan user dalam memori tanpa mengubah constraint PostgreSQL. Logout tetap dapat dipakai.
- Dashboard memakai satu controller/view/layout dengan judul berbeda; placeholder memakai satu view bersama. Tidak ada query booking, CRUD, approval, atau pembatasan berdasarkan organizational unit.
