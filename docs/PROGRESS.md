# Progress

## Last Update

2026-09-20 — Tahap 2B Role & Authorization selesai dan terverifikasi.

## Tahap Saat Ini

Tahap 1, Tahap 2A, dan Tahap 2B selesai. Menunggu instruksi untuk Tahap 3.

## Completed

- Dokumentasi konteks, roadmap, keputusan, dan aturan agent.
- Repository GitHub terhubung; commit awal project `b3d94a4` (`first commit`) telah dipush sebelumnya. Pengguna mengizinkan publikasi gabungan hasil Tahap 1, 2A, dan 2B beserta dokumentasi penjelasan.
- PHP `pdo_pgsql` aktif; koneksi Laravel ke PostgreSQL `spmt_booking_room` berhasil. Password hanya disimpan dalam `.env` lokal yang diabaikan Git.
- Analisis project: Laravel 10.50.3, model User dan migration bawaan, tanpa package role/permission. Tidak mengubah dependency.
- Tujuh migration baru untuk tabel fondasi. Sesuai permintaan pengguna, kolom unit, role, dan status aktif digabung langsung ke migration pembuatan users; migration penambahan terpisah dihapus. Migration organizational_units diurutkan sebelum users agar foreign key valid.
- Model OrganizationalUnit, Floor, Facility, Room dan relasi User; validasi calon PIC melalui EligibleRoomPic.
- Constraint PostgreSQL: foreign key, batas kapasitas, nilai enum, kode ruang unik, dan pasangan pivot unik. Penghapusan unit/lantai yang masih digunakan dibatasi.
- Seeder development: 3 unit, 3 akun, 8 lantai, 7 fasilitas, dan 4 ruang contoh. Seeder bisa diulang dan tidak mereset password akun lama.
- Template koneksi `.env.example` dan default database diarahkan ke PostgreSQL.
- Panduan singkat tabel, akun dummy, dan cara melihat data di `docs/DATABASE.md`.
- Test integrasi memakai schema PostgreSQL sementara; konfigurasi XML PHPUnit disesuaikan dengan PHPUnit 10 yang sudah terpasang.
- Tahap 2A: Breeze 1.29.1 baru dipasang pada Laravel 10.50.3. Scaffolding session/LoginRequest resmi disesuaikan untuk login internal, logout, Remember Me, dan dashboard Blade sederhana tanpa NPM build.
- Akun nonaktif ditolak sebelum login; session/Remember Me akun yang dinonaktifkan juga dihentikan. Password salah tidak mengungkap status akun.
- Route login memakai guest, dashboard/logout memakai auth; form POST memakai CSRF. Sejak Tahap 2B, redirect dashboard mengikuti role. Registrasi publik dan route profil/reset password/verifikasi email tidak tersedia.
- Helper schema test dipindahkan ke `tests/PostgresTestCase.php` agar dipakai bersama oleh test fondasi dan authentication. Panduan menjalankan tersedia di `docs/AUTHENTICATION.md`.
- Tahap 2B: empat Gate terpusat, middleware `can` pada kelompok route, dashboard Pegawai/PIC/Super Admin, navigasi `@can`, halaman 403, dan sebelas halaman placeholder melalui satu view bersama.
- Login dan guest redirect memakai mapping dashboard yang sama. Role tidak dikenal ditolak dengan 403. Unit organisasi hanya ditampilkan; tidak dipakai untuk authorization.
- PIC dapat mengakses halaman pegawai; admin dapat mengakses administrasi, informasi umum, dan placeholder PIC tanpa mengubah assignment `room_pics`. Rincian route dan akses ada di `docs/AUTHORIZATION.md`.
- Penjelasan percakapan tentang `can`, nama izin `access-employee`, awalan nama route `admin.`/`pic.`, pemisahan auth.php, dan alasan tidak membutuhkan NPM disimpan di `docs/AUTHORIZATION.md` untuk rujukan berikutnya.

## Verifikasi Aktual

- Migration awal pernah menjalankan 12 file. Setelah penggabungan migration users, riwayat migration lokal diselaraskan menjadi 11 file tanpa menghapus atau mengubah data aplikasi. `php artisan migrate:status`: seluruh 11 migration berstatus Ran.
- `php artisan db:seed --no-interaction`: berhasil.
- `php artisan test` setelah Tahap 2B: **38 passed, 513 assertions**, mencakup 16 test authentication, 9 test authorization, 11 test fondasi, dan 2 test existing.
- `php artisan migrate --no-interaction`: Nothing to migrate; migration Tahap 1 tidak diubah pada Tahap 2A. Seeder existing dijalankan ulang dan berhasil.
- Route list terverifikasi: kelompok umum, pegawai, PIC, admin memakai auth + can; login/logout tetap tersedia dan tidak ada route registrasi publik.
- Ketiga akun development pada database lokal terverifikasi aktif dan password dummy lolos validasi guard Laravel; tidak mereset password akun.
- Test authentication mencakup ketiga role, password salah, akun nonaktif, auth/guest, session regeneration, logout/session invalidation/CSRF rotation, CSRF 419, throttle login, Remember Me, dan penonaktifan session lama.
- Test authorization login memakai akun seeder pada schema PostgreSQL sementara, lalu mencoba URL setiap area secara langsung. Memverifikasi menu, redirect per role, 403, role tidak dikenal dalam memori, perubahan role pada request berikutnya, unit null, dan assignment PIC tetap sama. Tidak menjalankan test pada tabel development/produksi.
- Pengujian mencakup seluruh relasi, seed ulang, password, validasi PIC, enum, kapasitas, seluruh foreign key fondasi, unique pivot/kode, restrict/cascade, penolakan seed produksi, dan rollback/migrate ulang pada schema test.
- Laravel Pint pada file PHP terkait: passed. `git diff --check`: tidak ada kesalahan whitespace.
- Tidak menjalankan reset atau rollback terhadap tabel development; rollback test hanya di schema sementara.

## Batasan dan Asumsi

- Satu role dan maksimal satu unit per user. PIC mendapat halaman pegawai; hak booking pribadi admin dan pewarisan akses unit masih belum ditentukan.
- Data dummy bukan struktur/data resmi perusahaan. Password development tercatat di `docs/DATABASE.md`.
- Validasi role PIC tersedia untuk dipakai pemanggil; penulisan pivot langsung tidak menjalankannya otomatis. Validasi siklus unit, minimal satu PIC, dan perubahan role ditangani pada modul pengelolaan berikutnya.
- Belum membuat CRUD master data, booking, approval, kalender, notification, atau dashboard kompleks. Halaman modul masih placeholder; dashboard hanya identitas, unit, navigasi sesuai akses, dan logout. Migration, seeder, dependency, serta struktur role tidak diubah pada Tahap 2B.

## Next Step

Tunggu instruksi pengguna untuk Tahap 3. Jangan membuat CRUD master data atau booking secara otomatis.
