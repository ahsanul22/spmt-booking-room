# Project Context

## Nama Project

Sistem Booking Ruang Rapat PT Pelindo Multi Terminal.

## Tujuan

Website internal perusahaan untuk membantu pegawai:

- Mengetahui ruang rapat yang tersedia.
- Melihat jadwal ruang rapat.
- Mencari ruang berdasarkan tanggal dan waktu.
- Melakukan booking ruang rapat.
- Mencegah bentrok jadwal.
- Mengelola approval untuk ruang tertentu.
- Melihat status dan riwayat booking.

Sistem digunakan untuk kebutuhan internal perusahaan. Beranda sebelum login memperkenalkan sistem dan menampilkan jadwal ruangan publik aktif (DEC-031); booking, jadwal internal lengkap, dan pengajuan tetap memerlukan akun pegawai.

## Scope yang Tidak Dikerjakan Saat Ini

Jangan mengembangkan catering, snack, konsumsi, rapat eksternal, pengelolaan tamu eksternal, WhatsApp integration, SMS, payment, atau fitur di luar kebutuhan booking ruang rapat. Email juga tidak diperlukan pada tahap awal.

## Struktur Organisasi

Struktur organisasi dapat memiliki tingkatan Direktorat → Divisi → Departemen.

Database nantinya menggunakan konsep hierarchical organizational unit agar fleksibel. Direktorat, Divisi, dan Departemen bukan role aplikasi. Unit organisasi menunjukkan asal pegawai; role menentukan hak akses aplikasi.

## Role

### User / Pegawai

Dapat:

- Login.
- Melihat daftar dan detail ruang.
- Melihat jadwal dan mencari ruang tersedia.
- Melakukan booking.
- Melihat booking sendiri dan status booking.
- Membatalkan booking sesuai aturan.
- Melihat riwayat booking.

### Room PIC

PIC terhubung ke ruangan, bukan otomatis ke divisi. Satu ruangan dapat memiliki satu atau lebih PIC.

PIC dapat:

- Melihat ruangan yang dikelolanya dan jadwal ruangan tersebut.
- Melihat request booking yang membutuhkan approval.
- Approve booking.
- Reject booking dan memberikan alasan rejection.

PIC tidak mengelola seluruh master data sistem.

### Admin

Dapat:

- Mengelola user dan unit organisasi.
- Mengelola lantai, fasilitas, dan ruang rapat.
- Menentukan PIC.
- Menentukan aturan akses ruangan.
- Menentukan apakah ruangan membutuhkan approval.
- Mengelola kondisi operasional ruangan.
- Melihat seluruh booking.
- Melakukan administrasi sistem.
- Menyetujui atau menolak pengajuan Pending yang membutuhkan approval di seluruh ruangan (DEC-037).

## Ruangan

Gedung memiliki master Lantai 1 sampai Lantai 8. Data ruangan sesuai rincian pengguna: lantai 2 satu, lantai 3 dua, lantai 4 dua, lantai 6 dua, lantai 7 tiga. Total rincian 10, masih menunggu koreksi atas angka awal 9. Lantai 7 sementara Selat Malaka I/II/III. Ruangan lain terbuka untuk seluruh pegawai tanpa approval; Selat Malaka menerima pengajuan dengan approval PIC (DEC-029).

Informasi ruang minimal nantinya:

- Nama.
- Lantai.
- Fasilitas.
- Status operasional.
- Aturan akses.
- Membutuhkan approval atau tidak.
- PIC.

Data asli ruangan harus dapat dimasukkan melalui aplikasi. Jangan hardcode daftar ruang ke source code.

## Akses Ruangan

- `all`: semua pegawai dapat mengajukan penggunaan ruang.
- `restricted`: hanya organizational unit tertentu yang diperbolehkan menggunakan ruang.

Default konsep sistem adalah ruang dapat digunakan seluruh pegawai, kecuali terdapat aturan khusus.

## Approval

Setiap ruang memiliki pengaturan `requires_approval = true/false`.

- Jika `false`: User → Booking → Validasi → Approved.
- Jika `true`: User → Booking → Pending → Room PIC → Approved / Rejected.

Validasi booking tetap berlaku untuk kedua alur.

## Pemilihan Ruangan

Menu Booking Ruangan berisi katalog kartu ruangan. User memilih satu kartu, lalu mengisi tanggal, waktu, dan agenda tanpa dropdown ruangan. Dashboard menampilkan ringkasan tiga kartu dari katalog yang sama. My Booking khusus pengajuan dan riwayat pribadi. Halaman Cari Ruangan dihapus sesuai permintaan pengguna. Jumlah peserta dan kapasitas ruangan tidak ditampilkan atau dikumpulkan melalui UI. Pemeriksaan status operasional, hak akses, dan bentrok jadwal berjalan di server saat pemeriksaan dan submit booking.

## Jadwal Ruangan

Kalender menampilkan booking tersimpan dengan ringkasan per tanggal dan daftar room/jam WIB. Pending berwarna kuning, Approved terjadwal biru, Approved yang waktunya sedang berjalan merah, dan waktu lewat abu-abu. Rejected/Cancelled tidak mengisi jadwal. Nama pemohon/agenda/catatan tidak ditampilkan di jadwal bersama. Panel yang sama tersedia di atas form booking untuk room dan tanggal terpilih. Pembaruan otomatis tetap disertai validasi konflik ulang saat submit.

## Booking

Pengajuan harus dilakukan minimal dua jam sebelum jam mulai rapat. Pemeriksaan rencana saat ini memakai waktu server dengan WIB (`Asia/Jakarta`, asumsi konfigurasi untuk alur ini), dan mengulang pemeriksaan ketika tombol Periksa Rencana ditekan. Backend submit menegakkan aturan yang sama pada waktu submit, bukan mengandalkan hasil pemeriksaan sebelumnya. Form saat ini mendukung satu tanggal; akhir harus setelah awal, lintas hari belum tersedia.

Data booking yang disimpan:

- Pemohon.
- Unit kerja pemohon.
- Ruangan.
- Judul/agenda.
- Tanggal.
- Jam mulai.
- Jam selesai.
- Catatan opsional.
- Status.

## Bentrok Jadwal

Dua booking aktif tidak boleh menggunakan ruangan yang sama pada waktu yang bertabrakan.

Validasi dilakukan saat pencarian dan saat submit booking.

- Pending dan Approved memblokir waktu.
- Rejected dan Cancelled tidak memblokir waktu.

## Status Booking

Status utama: Pending, Approved, Rejected, Cancelled, dan Completed.

Booking yang dibatalkan tidak boleh dihapus dari database karena diperlukan sebagai history.

## Laporan Bulanan

Admin dan PIC dapat membuat laporan berdasarkan bulan tanggal rapat dan filter ruangan, melihat rekap status serta durasi terjadwal, mengunduh CSV, dan mencetak/menyimpan PDF melalui browser. Admin mencakup semua ruangan; PIC hanya penugasan saat ini termasuk riwayat ruangan nonaktif. Jam terjadwal menghitung Approved + Completed, bukan bukti penggunaan aktual. Status mengikuti data saat laporan dibuka; tidak mengubah lifecycle booking (DEC-036).

## Notification Internal

Tahap awal menggunakan notification internal website, misalnya:

- Booking berhasil dibuat.
- Booking approved.
- Booking rejected.
- PIC mendapatkan request approval.

Tidak perlu email, WhatsApp, atau SMS saat ini.

## Frontend

Frontend dikembangkan tim menggunakan Blade + Tailwind CSS melalui Vite. Halaman Jadwal Ruangan menjadi acuan visual utama untuk layout, tipografi, komponen, responsivitas, dan palet warna pada `tailwind.config.js`. Panduan kerja CLI dan integrasi HTML ada di `docs/FRONTEND_GUIDE.md`.

Aturan awal yang membatasi frontend ke HTML sederhana untuk testing sudah digantikan oleh instruksi pengembangan frontend setelah 4E. Semua fungsi backend yang aktif harus tetap berfungsi; data berasal dari Laravel. Halaman yang backend-nya belum tersedia boleh didesain dengan empty state dan penanda pratinjau. Task frontend tidak otomatis mengizinkan implementasi backend tahap berikutnya.

## Requirement yang Perlu Diklarifikasi Sebelum Implementasi Terkait

Hal berikut belum diputuskan dan bukan aturan implementasi:

- Fondasi menggunakan satu role dan maksimal satu unit per user sesuai instruksi Tahap 1. Pada Tahap 2B, PIC mendapat akses halaman pegawai termasuk placeholder My Booking. Hak Admin untuk melakukan booking pribadi belum diputuskan.
- Apakah akses unit `restricted` diwariskan ke unit turunan?
- Apa batas waktu dan kewenangan pembatalan, serta apakah booking dapat diubah atau dijadwalkan ulang?
- Booking bersebelahan diperbolehkan tanpa buffer pada implementasi awal; waktu menggunakan WIB dan form satu tanggal (DEC-029). Jam operasional, durasi maksimum, dan dukungan lintas hari belum ditetapkan.
- Kapan Pending kedaluwarsa, kapan booking menjadi Completed, dan bagaimana Completed diperlakukan dalam pemeriksaan konflik?
- Satu keputusan PIC yang ditugaskan atau Admin aktif menyelesaikan pengajuan Pending; alasan rejection wajib. Admin dapat memutuskan seluruh ruangan, tetapi tidak mengubah keputusan final (DEC-037).
- Bagaimana booking yang sudah ada ditangani ketika ruangan menjadi maintenance/nonaktif atau aturan aksesnya berubah?
- Nama unit pemohon disimpan sebagai snapshot saat pengajuan (DEC-029).
