# Progress

## Last Update

2026-09-22 - Referensi frontend Jadwal Ruangan dibuat setelah Tahap 4E.

## Tahap Saat Ini

Verifikasi sebelum publikasi commit terpisah (2026-09-22): `php artisan test` **93 passed, 2573 assertions**. Perubahan dikelompokkan menjadi commit unit organisasi, user, lantai/fasilitas, room, assignment PIC/akses, integrasi route/frontend, desain jadwal Tailwind, dan dokumentasi. Publikasi ditujukan ke origin/main sesuai instruksi pengguna; file .env, node_modules, dan public/build diabaikan Git.

Tahap 1, 2A, 2B, 3, dan 4A-4E selesai. Atas permintaan pengguna, membuat satu referensi desain Jadwal Ruangan untuk diskusi tim; backend Tahap 5/7 belum dilanjutkan.

## Referensi Frontend - Jadwal Ruangan

- `resources/views/shared/schedule.blade.php` menggunakan layout khusus `layouts/schedule.blade.php`: sidebar primaryDark, header akun, judul, kalender bulanan, panel agenda kosong, tautan ruangan, dan footer. Berlaku di /schedule serta /admin/schedule setelah login. Menu mengikuti Gate; mobile memiliki tombol buka/tutup navigasi dan kalender dapat digeser horizontal.
- Palet pengguna dimasukkan dalam tailwind.config.js. Memakai instalasi Tailwind/Vite existing tanpa dependency tambahan, ikon SVG lokal, dan CSS komponen kecil. Halaman lain tetap memakai layout sebelumnya.
- `resources/js/schedule.js`: navigasi bulan, Hari Ini, pilihan tanggal dengan label Indonesia, penanda hari ini, fokus keyboard, serta menu mobile. Tidak memasukkan data booking dummy. Penanda pratinjau menjelaskan agenda belum terhubung dan tanggal kosong bukan bukti ketersediaan.
- Build `npm run build` berhasil setelah sandbox memblokir esbuild (spawn EPERM) dan build dijalankan melalui eskalasi. `node --check resources/js/schedule.js` berhasil. `FrontendSkeletonTest`: **6 passed, 674 assertions**. Test tautan disesuaikan agar hanya mengunjungi anchor navigasi, bukan aset CSS Vite atau fragment skip-link. `view:cache`/`view:clear` berhasil. Smoke check JavaScript dengan DOM stub memverifikasi 42 tanggal, navigasi bulan, pilihan tanggal, dan Hari Ini. Tidak melakukan pengujian browser visual.

## Tahap 4E - Assignment PIC dan Restricted Unit Access (Selesai)

- Melanjutkan dan memeriksa implementasi awal 4E yang sudah tersedia di workspace. `RoomPicController`, `RoomAccessController`, dua Form Request, dan `RoomAssignmentService` mengaktifkan dua halaman pengelolaan dengan data PostgreSQL. Tidak menambah migration/dependency.
- Lima route GET/PUT pics, DELETE pics/{pic}, GET/PUT access memakai auth + access-admin, binding numerik/404, dan CSRF. DELETE hanya melepas assignment pada ruangan terkait. Tersisa 14 route skeleton.
- Form PIC menyimpan beberapa akun role room_pic, menolak role lain/ID tidak ada/duplikat, mempertahankan pilihan, dan dapat mengosongkan semua assignment. Daftar assignment lama tetap terlihat meskipun role akun berubah; pelepasan tidak menghapus akun atau assignment room lain. Rule fondasi diselaraskan ke room_pic sesuai implementasi awal dan UI; alasan dicatat di DEC-018.
- Form unit restricted menyimpan pilihan eksplisit dengan sync, dapat menghapus pilihan, dan menolak ID invalid/duplikat. Tipe all menampilkan daftar tersimpan sebagai tidak digunakan, menyembunyikan form, dan menolak endpoint tulis unit. Mengubah tipe room tidak menghapus pivot. Pewarisan akses dan enforcement booking belum diputuskan/diimplementasikan; teks UI diperjelas agar tidak menyatakan sebaliknya.
- Service mengunci room dalam transaksi; PIC diperiksa ulang dengan shared lock akun. Test memverifikasi rollback saat FK pivot gagal dan memastikan field room, fasilitas, user, unit, serta room lain tetap utuh. Pilihan akun/unit nonaktif diberi label; belum menetapkan minimum PIC/unit.
- `RoomAssignmentTest` berisi 9 test untuk authorization, multi-PIC, validasi, detach scoped, sync unit, tipe all, data tetap utuh, transaksi, 404, escaping, dan CSRF. `FrontendSkeletonTest` memakai 14 route; test rule fondasi mengikuti role yang diterima.
- **Hasil aktual:** `php artisan test`: **93 passed, 2573 assertions**. Pint file PHP terkait berhasil. Route list mengonfirmasi 12 route admin room termasuk lima route assignment, seluruhnya auth + access-admin. `view:cache` berhasil dan cache dibersihkan; `git diff --check` tanpa error whitespace (warning CRLF/LF saja). Pengujian menggunakan schema PostgreSQL sementara, tanpa reset data development; tidak melakukan browser manual.
- Roadmap, DEC-018, dan dokumentasi frontend diperbarui. Room user/PIC, Booking, Approval, kalender, dan notification tetap menunggu tahap berikutnya.
- Setelah perbaikan teks pewarisan akses dan nama test, `php artisan test --filter=RoomAssignmentTest` dijalankan ulang: **9 passed, 179 assertions**.

## Tahap 4D - Backend Room Management (Selesai)

- Menambah `app/Http/Controllers/Admin/RoomController.php`, `app/Http/Requests/SaveRoomRequest.php`, dan `app/Services/RoomService.php`. Memakai model/tabel rooms, floors, facilities, facility_room existing; tidak mengubah migration/dependency.
- `routes/web.php`: tujuh route `admin.rooms.index/create/store/show/edit/update/status`, auth + access-admin, binding ID numerik dan 404; tidak ada DELETE. Empat halaman Room admin dipindahkan dari skeleton, tersisa 16 route skeleton termasuk pics/access.
- Lima Blade room admin (`index`, `create`, `edit`, `show`, `_form`) aktif dan partial `_status` baru. Index pagination 20 menampilkan seluruh field utama serta fasilitas; detail membaca record sesuai ID; pilihan lantai/fasilitas database; form CSRF, validasi, flash, old input, submit aktif. Status operasional melalui edit, aktif/nonaktif lewat edit atau tombol index/detail.
- Validasi kode nullable unique, kapasitas integer nonnegatif sesuai rentang PostgreSQL, lantai/fasilitas existing, fasilitas distinct, enum akses/status, boolean approval/aktif, batas panjang nama/kode/deskripsi. Pilihan master nonaktif ditandai tanpa aturan pembatasan baru.
- Room dan fasilitas disimpan dalam transaksi; update mengunci row room. Sync menambah/menghapus pivot sesuai pilihan; semua fasilitas bisa dikosongkan. Test membuktikan kegagalan foreign key saat sync me-rollback perubahan room dan pivot. Error validasi tidak mengembalikan checkbox lama ketika user sudah mengosongkan semua pilihan.
- Tidak mengubah assignment PIC, pivot restricted unit access, Booking, atau Approval. Detail hanya membaca relasi existing. `pics.blade.php` dan `access.blade.php` hanya diperbaiki tautan kembali ke daftar room karena detail preview kini 404.
- `tests/Feature/RoomManagementTest.php`: 9 test baru untuk guest/user/PIC, create/update, pilihan dan pivot fasilitas, validasi, status operasional/aktif, null code, error/old input, relasi di luar scope tetap utuh, rollback, 404, escaping, dan CSRF. `FrontendSkeletonTest.php` diperbarui untuk 16 route dan form Room aktif.
- Verifikasi memakai schema PostgreSQL sementara melalui `PostgresTestCase`, tanpa reset atau penambahan data uji ke schema development. Tidak melakukan browser manual. Route list mengonfirmasi tujuh route aktif ditambah dua route skeleton pics/access, semuanya auth + access-admin.
- **Hasil aktual akhir:** `php artisan test`: **84 passed, 2460 assertions**, termasuk seluruh 9 test Room Management. Test awal menemukan masalah pilihan fasilitas kosong saat validasi gagal dan test rendering skeleton tanpa middleware session; keduanya diperbaiki dan seluruh suite dijalankan ulang hingga lulus. Pint file PHP terkait berhasil; `view:cache` berhasil, cache dibersihkan; `git diff --check` tanpa error whitespace (hanya warning CRLF/LF).
- Roadmap, keputusan DEC-017, dan dokumentasi frontend diperbarui. Berhenti setelah Tahap 4D.

## Tahap 4C - Backend Floor & Facility Management (Selesai)

- Menambah `app/Http/Controllers/Admin/FloorController.php`, `FacilityController.php`, serta `app/Http/Requests/SaveFloorRequest.php` dan `SaveFacilityRequest.php`. Memakai model/tabel existing tanpa migration/dependency.
- `routes/web.php`: masing-masing tujuh route untuk `admin.floors.*` dan `admin.facilities.*`: index/create/store/show/edit/update/status. Auth + access-admin, ID numerik, binding record/404; tanpa DELETE. Delapan halaman dipindahkan dari skeleton; tersisa 20 route skeleton.
- Sepuluh Blade existing pada `admin/floors` dan `admin/facilities` terhubung ke PostgreSQL; partial `_status` masing-masing dan komponen `form-feedback` baru. Index pagination 20, form create/edit benar-benar menyimpan, detail record sebenarnya, tombol aktif/nonaktif, CSRF, old input, validation error, flash message, tanpa hardcode contoh fasilitas.
- Nama wajib maksimal 255, deskripsi opsional maksimal 5000, status boolean. Nomor lantai integer dalam rentang PostgreSQL dan unique; nama fasilitas unique. Nilai unique milik record sendiri tetap boleh saat edit. Rincian DEC-016.
- Test memastikan create/update/status tersimpan di PostgreSQL dan dibaca kembali di index/detail/edit. Nonaktif/aktif dan edit tidak mengubah record ruangan, relasi Floor-Room, pivot Facility-Room, atau master lainnya. DELETE ditolak dan record tetap ada.
- `tests/Feature/FloorFacilityManagementTest.php` ditambahkan: 12 test (6 skenario untuk masing-masing modul), meliputi guest/user/PIC, create, update, input invalid/duplicate, status, relasi, data existing, error/flash, 404, escaping, dan CSRF. `FrontendSkeletonTest.php` disesuaikan ke 20 route.
- Test khusus: `php artisan test --filter=FloorFacilityManagementTest`: **12 passed, 365 assertions**. Semua memakai schema PostgreSQL sementara melalui `PostgresTestCase`; data development tidak direset atau diisi data uji. Tidak melakukan browser manual.
- **Verifikasi akhir:** `php artisan test`: **75 passed, 2329 assertions**. Pint file PHP terkait berhasil; `view:cache` berhasil dan cache dibersihkan dengan `view:clear`; `git diff --check` tanpa error whitespace (hanya warning CRLF/LF).
- Route list mengonfirmasi 14 route dengan auth + access-admin. Roadmap, DEC-016, dan dokumentasi frontend diperbarui. Room, PIC, restricted access, Booking, dan Approval tidak diimplementasikan.

## Tahap 4B - Backend User Management (Selesai)

- Menambah `app/Http/Controllers/Admin/UserController.php` dan `app/Http/Requests/SaveUserRequest.php`; memakai model/tabel users dan organizational_units existing. Tidak mengubah migration, dependency, model fillable, atau mekanisme login.
- `routes/web.php`: delapan route bernama `admin.users.index/create/store/show/edit/update/status/reset-password`, dilindungi auth + access-admin. Model binding ID numerik, 404 record hilang, tanpa endpoint delete. Empat halaman user tidak lagi menggunakan controller skeleton.
- Lima Blade user existing (`index`, `create`, `show`, `edit`, `_form`) terhubung ke database; partial `_feedback` dan `_status` ditambahkan. Index pagination 20 beserta unit, dropdown unit dari PostgreSQL, form simpan/update aktif dengan CSRF, error, old input, flash, tombol aktif/nonaktif di index/detail, reset password pada detail. Dashboard menampilkan flash setelah perubahan role sendiri.
- Nama/email wajib, email valid unique, role hanya user/room_pic/super_admin, unit nullable dan harus ada, status boolean. Unit nonaktif tetap tersedia dengan label. Field role/unit/status diisi eksplisit setelah authorization.
- Password minimal 8 dan confirmed saat create/reset atau bila diisi saat edit; `Hash::make`, password kosong/tidak dikirim saat edit mempertahankan hash lama. Password tidak ditampilkan/diflash. Reset merotasi remember token dan benar-benar mengganti kredensial login; pemutusan seluruh session aktif tidak ditambahkan.
- Perubahan status mempertahankan record dan unit; akun nonaktif ditolak login oleh mekanisme existing. Role sendiri yang diubah diarahkan ke dashboard role baru; nonaktifkan diri mengakhiri session. Assignment room_pics tidak diubah otomatis; pengelolaan Room tetap di luar scope.
- `tests/Feature/UserManagementTest.php`: 10 test baru meliputi semua endpoint guest/user/PIC, create ketiga role, dropdown unit database, email duplicate create/update, validasi/password confirmation, edit/role/unit, password lama dipertahankan atau diganti, status/login nonaktif/aktif, reset password lama ditolak dan baru diterima, token, self-change, CSRF, 404, escaping, rendering dan database assertions.
- `FrontendSkeletonTest.php` disesuaikan menjadi 28 route skeleton; test password form sekarang melewati request HTTP agar middleware session/error ikut berjalan.
- Verifikasi memakai schema PostgreSQL sementara melalui `PostgresTestCase`, tidak mereset atau menambah akun uji pada data development. Tidak melakukan browser manual.
- **Hasil aktual akhir:** `php artisan test`: **63 passed, 2236 assertions**. Test khusus User Management pertama: **10 passed, 312 assertions**, lalu seluruh suite diulang setelah penyesuaian test form dan verifikasi flash dashboard. Pint berhasil; `view:cache` berhasil dan cache dibersihkan. `route:list --path=admin/users -v` mengonfirmasi delapan route dengan auth + access-admin. `git diff --check` tanpa error whitespace (hanya warning normalisasi CRLF/LF).
- Tidak mengaktifkan Floor, Facility, Room, Booking, atau Approval. Roadmap, keputusan DEC-015, dan dokumentasi frontend diperbarui.

## Tahap 4A - Backend Organizational Unit (Selesai)

- Controller `app/Http/Controllers/Admin/OrganizationalUnitController.php`, Form Request `app/Http/Requests/SaveOrganizationalUnitRequest.php`, dan service `app/Services/OrganizationalUnitService.php` ditambahkan. Memakai model/tabel existing, tanpa migration/dependency baru.
- `routes/web.php`: empat route halaman unit dipindahkan dari skeleton ke controller modul; store, update, dan status diaktifkan. Prefix nama `admin.organizational-units.`: index/create/store/show/edit/update/status. GET untuk halaman, POST store, PUT/PATCH update, PATCH `/{unit}/status`; auth + access-admin di semua route. Tidak ada delete.
- Lima Blade existing unit (`index`, `create`, `show`, `edit`, `_form`) dihubungkan ke database; partial `_feedback` dan `_status` ditambahkan. Index pagination 20, parent/child dari relasi, pilihan parent database, form aktif dengan CSRF, validation error, old input, flash message, link detail/edit berbasis record, serta tombol aktif/nonaktif.
- Validasi nama wajib maksimal 255, type terbatas pada tiga tipe existing, parent harus ada, status boolean. Directorate tanpa parent; Division parent Directorate; Department parent Division. Self-parent/siklus dan perubahan type yang merusak child existing ditolak. Validasi hierarchy + save memakai transaction dan lock PostgreSQL (DEC-014).
- Status berlaku pada unit yang dipilih saja; child dan relasi user tetap tersimpan, tanpa hard delete. Parent nonaktif tetap tersedia dengan label. Modul lain tetap skeleton, termasuk dropdown unit di Tambah User.
- `tests/Feature/OrganizationalUnitTest.php` ditambahkan (9 test). `FrontendSkeletonTest.php` disesuaikan menjadi 32 route skeleton karena empat halaman unit sudah aktif.
- **Hasil aktual:** `php artisan test --filter=OrganizationalUnitTest`: **9 passed, 222 assertions** pada verifikasi terakhir (termasuk perubahan ke Directorate tanpa field parent untuk memastikan parent lama dikosongkan). Suite penuh sebelumnya `php artisan test`: **53 passed, 2053 assertions**; setelah perbaikan terakhir, seluruh test modul dijalankan ulang dan lulus. Pint pada file PHP terkait berhasil.
- **Verifikasi end-to-end:** request HTTP create Directorate/Division/Department, edit nama/parent/type yang valid, nonaktif/aktif, penolakan parent salah/self-parent/siklus/child tidak valid, error dan old input, flash message, role guest/user/PIC, 404, serta escaping. Record dibaca ulang dan dicek dengan database assertions; index/detail/form membaca data PostgreSQL. Semua memakai schema PostgreSQL sementara melalui `PostgresTestCase`, tidak menambah/mengubah data development untuk pengujian. Tidak melakukan pengujian browser manual.
- `php artisan route:list --path=organizational-units -v` mengonfirmasi tujuh route beserta middleware. Blade `view:cache` berhasil dan cache dibersihkan; `git diff --check` tanpa error whitespace (hanya warning normalisasi CRLF/LF). Dokumentasi roadmap, frontend, dan keputusan diperbarui.

## Tambahan Seeder Unit Kerja

- Atas permintaan pengguna, menambah 6 unit dummy di `FoundationSeeder`; total contoh menjadi 9 unit (2 direktorat, 3 divisi, 4 departemen). Hierarki dan petunjuk penggunaan tercatat di `docs/DATABASE.md`.
- Menggunakan `firstOrCreate` seperti seeder existing: seed ulang tidak menggandakan unit yang sama atau menimpa data existing. Unit baru aktif; akun, unit akun existing, dan aturan akses ruangan tidak diubah oleh penambahan ini.
- `php artisan db:seed --class=FoundationSeeder --no-interaction` berhasil dijalankan pada database lokal tanpa reset/migration.
- Test fondasi diperbarui untuk jumlah unit dan memeriksa status aktif serta hierarki parent. `php artisan test --filter=DatabaseFoundationTest`: **11 passed, 126 assertions** pada schema PostgreSQL sementara. Pint berhasil.
- Form Tambah User tetap skeleton; dropdown belum membaca database dan penyimpanan user belum tersedia. Tambahan ini hanya data development, bukan implementasi Tahap 4.

## Tahap 3 — Frontend Skeleton (Selesai)

- Membaca AGENTS, konteks, roadmap, progress, keputusan, routes, layout, model, dan test existing sebelum implementasi. Roadmap lama diperbarui sesuai urutan terbaru pengguna.
- Seluruh halaman utama user/PIC/admin tersedia: pencarian/daftar/detail ruangan, jadwal, form/detail/My Booking; ruangan PIC, permintaan/detail/riwayat approval; daftar/tambah/edit/detail lima master data, Kelola PIC, Kelola Akses, semua/detail booking, serta jadwal admin.
- 39 route halaman terlindungi: 3 dashboard existing dan 36 route skeleton; 25 route baru. Gate Tahap 2B tetap dipakai. Admin mempertahankan akses URL umum/PIC existing, dengan navigasi utama administrasi. Cari Ruangan dan booking pribadi tetap untuk user/PIC.
- Satu `FrontendSkeletonController` hanya membuka view dari default route. Tidak ada query modul, implicit model binding, penyimpanan, endpoint tulis, atau business logic baru. Identifier `preview` memungkinkan peninjauan detail/edit tanpa record palsu.
- Layout bersama memuat nama aplikasi, identitas/role, menu sesuai izin, logout, dan content. Jadwal, informasi ruangan, detail booking/approval, dan form tambah/edit memakai partial bersama. Komponen field/select/textarea/table/form/notice/tombol nonaktif mengurangi duplikasi.
- List dan pilihan memakai empty state; tidak memasok dummy permanen meskipun seeder development sudah berisi data. Data user login dan unit tetap berasal dari backend existing.
- Tombol proses dinonaktifkan, implicit form submit dicegah dengan satu handler kecil. Form POST dengan CSRF tidak memiliki endpoint tulis. Password tambah wajib, edit opsional, dan tidak pernah diprefill.
- Dokumentasi halaman, route, folder, variabel view, placeholder, dan integrasi berikutnya tersedia di `docs/FRONTEND.md`. Keputusan frontend dicatat pada DEC-013.
- **Verifikasi aktual:** `php artisan test` menghasilkan **44 passed, 1952 assertions**; 6 test baru frontend mencakup seluruh route skeleton per role/guest, tautan, empty state, penolakan POST/PUT/PATCH/DELETE, field, escaping data, dan password. Test database tetap menggunakan schema PostgreSQL sementara.
- Laravel Pint pada controller/route/test terkait berhasil. `php artisan view:cache` berhasil; cache view kemudian dibersihkan dengan `view:clear`. `git diff --check` berhasil tanpa error whitespace (Git hanya memberi peringatan normalisasi CRLF/LF). Tidak menjalankan test browser/styling atau mengubah dependency/migration/model/rule existing.

## Completed Tahap 0–2B (Historis)

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

## Verifikasi Tahap 1–2B (Historis)

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
- Assignment PIC memvalidasi role room_pic saat penyimpanan; penulisan pivot langsung tidak menjalankan rule otomatis. Perubahan role setelah assignment tidak otomatis melepas pivot, tetapi assignment lama dapat dilepas. Aturan minimum PIC/unit belum ditetapkan.
- Master data admin, assignment PIC, dan daftar unit restricted sudah aktif pada Tahap 4A-4E. Room user/PIC, Booking, Approval, kalender aktif, notification, dan dashboard kompleks belum diimplementasikan. Tahap 4E tidak mengubah migration, seeder, dependency, atau struktur role.
- Parameter URL halaman skeleton (Room user/PIC, Booking, Approval) belum melakukan resolution record; backend berikutnya wajib menambahkan 404 dan authorization record. Nama field booking di view bersifat kontrak presentasi sementara, bukan keputusan schema.
- UI PIC dan EligibleRoomPic menerima role room_pic. Inheritance restricted access dan booking pribadi admin tetap belum diputuskan; penyimpanan unit eksplisit tidak menetapkan aturan pewarisan saat booking.

## Next Step

Tahap 4E selesai. Tunggu instruksi Tahap 5 Booking Backend; jangan lanjut ke Booking atau Approval secara otomatis. `FrontendSkeletonController` tetap digunakan oleh modul yang belum aktif.
