# Audit controller dan validasi

Tanggal: 2026-10-20. Cakupan: seluruh 24 file controller, route/middleware, Form Request dan service yang dipanggil. Ini audit kode aplikasi dan pengujian regresi terarah, bukan sertifikasi keamanan atau penetration test infrastruktur.

## Kontrak yang dipertahankan

- Role dan akun aktif diperiksa melalui middleware/Gate; input tervalidasi tidak menggantikan authorization. Admin tetap global, pegawai/PIC mengikuti izin lokasi, PIC juga memerlukan assignment ruangan.
- Booking dan keputusan tetap memeriksa kondisi data terbaru dalam transaksi, konflik waktu, idempotensi, status final, dan subset konsumsi/IT. Tidak membuat aturan bisnis pembatalan, lifecycle, role atau jadwal baru.
- Data tulis dibatasi ke field yang tervalidasi. Password tidak diisi ulang/di-flash, perubahan password merotasi remember token; field privilege tidak menjadi mass assignable.
- Permintaan HTML tetap redirect dengan error/old input; JSON memakai 422 untuk input salah, 403 untuk akses terlarang, dan 404 untuk ID URL tidak valid/record di luar cakupan. Daftar menggunakan pagination tetap existing.

## Perbaikan

1. Rule `bail` sebelum query `exists`/`unique`: tipe salah, array bertingkat dan integer melampaui rentang tidak diteruskan ke kolom PostgreSQL. Validasi field lainnya tetap berjalan.
2. `PaginationRequest` membatasi `page` ke integer 1–2147483647 agar offset SQL tidak melampaui bigint. `BranchFilterRequest` menyatukan pemeriksaan filter lokasi dan menolak filter lokasi pegawai/PIC sebelum query.
3. Form Request khusus untuk simpan cabang, perubahan status, reset password, hapus ruangan, keputusan booking, registrasi tamu dan riwayat notifikasi. Approval meneruskan hanya flag hasil validasi ke service; alias `decision_notes`/`rejection_reason` tetap kompatibel.
4. `ValidateRouteIdentifiers` memeriksa parameter numerik user/booking/approval/branch/PIC/template/message sebelum model binding dan konversi parameter controller ke `int`. Key ruangan/lantai/fasilitas/unit tetap memakai pemeriksaan bounded readable ID existing.
5. Handler mengubah hanya enam constraint unik PostgreSQL yang dikenal menjadi error field yang jelas, termasuk konflik akibat dua request yang sama-sama lolos pengecekan awal. SQL/detail database tidak dikirim sebagai pesan validasi. Constraint tak dikenal dan error database lain tetap error server agar bug tidak disamarkan.
6. `UserService` memegang transaksi akun/pivot lokasi dan reset password. Penyimpanan membaca kembali record setelah lock, sehingga request tanpa field izin tidak menggunakan snapshot izin yang sudah usang. `GuestRegistrationService` memusatkan kantor server, expiry dan referensi tamu; field referensi/expiry dari klien tetap diabaikan.
7. Tanggal jadwal tamu menolak tahun nol yang tidak didukung PostgreSQL. Draft filter lokasi Livewire diperiksa sebelum query opsi ruangan, karena render terjadi sebelum tombol Tampilkan memvalidasi input.
8. Endpoint profil bawaan `/api/user` juga memerlukan `can:access-general` setelah autentikasi Sanctum. Token saja tidak lagi cukup bagi akun nonaktif atau role yang tidak dikenal. Tidak menambah API atau alur penerbitan token baru.

## Inventaris controller

| Controller | Pemeriksaan dan penanganan input |
| --- | --- |
| `Admin/BookingController` | BranchFilterRequest; hanya Admin; binding booking numerik |
| `Admin/BranchController` | PaginationRequest, SaveBranchRequest; whitelist nama; konflik unik terpetakan |
| `Admin/DashboardController` | Tidak mengonsumsi input bisnis; statistik dari AdminDashboard, akses Admin |
| `Admin/FacilityController` | PaginationRequest, SaveFacilityRequest, UpdateStatusRequest; unique nama, key tervalidasi |
| `Admin/FloorController` | PaginationRequest, SaveFloorRequest, UpdateStatusRequest; rentang integer lantai dan unique |
| `Admin/OrganizationalUnitController` | PaginationRequest, SaveOrganizationalUnitRequest, UpdateStatusRequest; service memeriksa hierarchy/cycle/child dalam lock |
| `Admin/RoomAccessController` | SaveRoomAccessRequest; daftar unit bertipe integer; restricted diperiksa ulang dalam service |
| `Admin/RoomController` | BranchFilterRequest, SaveRoomRequest, UpdateStatusRequest, DeleteRoomRequest; transaksi room/fasilitas dan larangan hapus/pindah lokasi room bersejarah |
| `Admin/RoomPicController` | SaveRoomPicsRequest; role/lokasi PIC diperiksa lagi dalam service, pelepasan hanya pivot milik room |
| `Admin/ScheduleController` | View tanpa input bisnis; data dan parameter kalender melalui komponen Livewire dengan Gate/scope |
| `Admin/UserController` | BranchFilterRequest, SaveUserRequest, UpdateStatusRequest, ResetUserPasswordRequest; UserService dan pengalihan aman saat perubahan akun sendiri |
| `Admin/WhatsappController` | WhatsappHistoryRequest, SaveWhatsappSettingsRequest; service mengunci setting/retry dan memeriksa ulang eligibility penerima |
| `Auth/AuthenticatedSessionController` | LoginRequest; room login diperiksa sebelum query; session regenerate/logout; scope lokasi diperiksa setelah login |
| `BookingPreparationController` | Readable binding atau pemeriksaan ID legacy; izin lokasi; validasi submit tetap di Livewire/BookingService |
| `DashboardController` | Tanpa input bisnis; statistik PIC mengikuti izin lokasi/assignment, delegasi Admin |
| `GuestRegistrationController` | StoreGuestRegistrationRequest; referensi/kantor/expiry dari server; jadwal hanya session tamu aktif dan okupansi anonim |
| `HomeController` | Tanpa input bisnis; redirect berdasarkan role akun terautentikasi |
| `MonthlyReportController` | MonthlyReportRequest termasuk pagination; scope room/lokasi sebelum query/export/cetak, CSV formula escaping existing |
| `Pic/ApprovalController` | BranchFilterRequest, DecideBookingRequest; scope assignment/lokasi, lalu pengecekan ulang BookingService dalam transaksi |
| `Pic/RoomController` | BranchFilterRequest; hanya ruangan penugasan dan lokasi yang diizinkan |
| `RoomDetailsController` | Binding readable, lokasi dan visibilitas room nonaktif; hanya baca |
| `UserBookingController` | BranchFilterRequest; daftar/detail milik pemohon dan lokasi yang diizinkan |
| `Controller` | Base Laravel AuthorizesRequests/ValidatesRequests; tidak mempunyai endpoint |
| `FrontendSkeletonController` | Legacy, tidak direferensikan route aktif; view berasal dari default route, bukan input query pengguna |

## Batas dan operasional

- Tidak menjalankan seeder/migration pada schema aplikasi atau mereset database. Tes fitur memakai schema PostgreSQL sementara milik tes.
- Penghapusan master data terkait tidak tersedia, sehingga validasi referensi tidak memperkenalkan alur penghapusan baru. Perubahan role/izin/status tetap mengikuti kewenangan Admin existing.
- Endpoint GET tanpa input bisnis tidak memerlukan Form Request kosong. Validasi inline tanggal jadwal tamu dan ID legacy yang sederhana tetap eksplisit, dengan penolakan sebelum query.
- Deployment tetap memerlukan `APP_DEBUG=false`, HTTPS, konfigurasi session yang benar dan perlindungan kredensial server. Audit ini tidak mengganti konfigurasi production.
- Baseline FrontendSkeletonTest tentang form akses unit yang dikomentari serta assertion UI lama pada beberapa tes authorization merupakan temuan terpisah dari audit input ini; hasil tes aktual dicatat di PROGRESS.md.

Hasil verifikasi aktual dan cakupan regresi dicatat pada entri audit controller di `PROGRESS.md`.
