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

Permintaan Snack/Makanan dan persetujuan subset oleh PIC/Admin diizinkan sebagai persiapan booking (DEC-048/049), tanpa pengelolaan catering/snack. Notifikasi WhatsApp booking ke PIC/Admin dan konsumsi memakai Fonnte, penerima dari akun user/PIC serta pengaturan Admin (DEC-043); pengiriman mengikuti konfigurasi perangkat/token dan scheduler server. Jangan mengembangkan pemesanan atau penyediaan konsumsi, rapat eksternal, pengelolaan tamu eksternal, SMS, payment, atau fitur di luar kebutuhan booking ruang rapat. Email juga tidak diperlukan pada tahap awal.

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

Gedung memiliki master Lantai 1 sampai Lantai 8. Data ruangan sesuai rincian pengguna: 2A, 3A/3B, 4A/4B, 6A/6B dan Selat Malaka I/II/III di lantai 7 (DEC-045). Total rincian 10, masih menunggu koreksi atas angka awal 9. Ruangan demo tanpa booking sudah dihapus dari database lokal. Seeder mengatur seluruh ruangan membutuhkan approval PIC (DEC-040); akses tetap mengikuti pengaturan ruangan dan PIC aktif harus ditetapkan.

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

Seluruh ruangan pada database proyek saat penugasan 2026-10-14 sudah terhubung ke akun aktif `Room PIC Demo` atas instruksi pengguna (DEC-047). Ruangan baru tetap memerlukan penugasan PIC melalui pengaturan Admin.

- `all`: semua pegawai dapat mengajukan penggunaan ruang.
- `restricted`: hanya organizational unit tertentu yang diperbolehkan menggunakan ruang.

Default konsep sistem adalah ruang dapat digunakan seluruh pegawai, kecuali terdapat aturan khusus.

## Approval

Setiap ruang memiliki pengaturan `requires_approval = true/false`.

- Jika `false`: booking tanpa konsumsi langsung Approved; permintaan konsumsi tetap Pending untuk review PIC/Admin (DEC-049).
- Jika `true`: User → Booking → Pending → Room PIC → Approved / Rejected.

Validasi booking tetap berlaku untuk kedua alur.

## Pemilihan Ruangan

Menu Booking Ruangan berisi katalog kartu ruangan. User memilih satu kartu, lalu mengisi tanggal, waktu, dan agenda tanpa dropdown ruangan. Dashboard menampilkan ringkasan tiga kartu dari katalog yang sama. My Booking khusus pengajuan dan riwayat pribadi. Halaman Cari Ruangan dihapus sesuai permintaan pengguna. Kapasitas tidak ditampilkan. Ruangan dengan persiapan rapat aktif dapat mencatat perkiraan jumlah peserta dan kebutuhan konsumsi setelah checkbox persiapan dicentang (DEC-040). Pemeriksaan status operasional, hak akses, dan bentrok jadwal berjalan di server saat pemeriksaan dan submit booking.

## Jadwal Ruangan

Kalender menampilkan booking tersimpan dengan ringkasan per tanggal dan daftar room/jam WIB. Pending berwarna kuning, Approved terjadwal biru, Approved yang waktunya sedang berjalan merah, dan waktu lewat abu-abu. Rejected/Cancelled tidak mengisi jadwal. Jadwal internal menampilkan snapshot unit kerja pemohon (DEC-040); jadwal publik tidak menampilkan unit. Nama pemohon/agenda/catatan tidak ditampilkan di jadwal bersama. Panel yang sama tersedia di atas form booking untuk room dan tanggal terpilih. Pembaruan otomatis tetap disertai validasi konflik ulang saat submit.

## Booking

Pengajuan tidak lagi mempunyai batas minimal dua jam sebelum rapat (DEC-044). Booking boleh diajukan mendekati waktu mulai; waktu mulai yang sudah lewat tetap ditolak. Pemeriksaan rencana dan submit menggunakan waktu server WIB (`Asia/Jakarta`) dengan presisi menit, dihitung ulang pada setiap pemeriksaan/submit. Form mendukung beberapa tanggal dengan jam yang sama melalui tombol Tambah tanggal booking (DEC-049). Setiap tanggal merupakan booking terpisah; akhir harus setelah awal pada tanggal yang sama, rapat kontinu lintas tengah malam belum tersedia. Ruangan yang membutuhkan approval tetap harus disetujui sebelum waktu mulai.

Data booking yang disimpan:

- Pemohon.
- Unit kerja pemohon.
- Ruangan.
- Judul/agenda.
- Tanggal.
- Jam mulai.
- Jam selesai.
- Catatan opsional.
- Persiapan rapat opsional, perkiraan jumlah peserta dan kebutuhan konsumsi.
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

Email akun harus unik tanpa membedakan huruf besar/kecil. Nomor WhatsApp boleh dipakai beberapa akun (DEC-046); format/normalisasi tetap divalidasi dan penerima bernomor sama dideduplikasi per booking/jenis pesan.

Tahap awal menggunakan notification internal website, misalnya:

- Booking berhasil dibuat.
- Booking approved.
- Booking rejected.
- PIC mendapatkan request approval.

Email dan SMS belum diperlukan. WhatsApp memakai outbox pengajuan Pending dan konsumsi setelah Approved serta adapter Fonnte. Admin mengelola nomor user, penerima akun, opsi PIC ruangan otomatis, template, pratinjau dan riwayat/retry melalui `/admin/whatsapp` (DEC-043). Satu Device Fonnte menjadi pengirim seluruh aplikasi; nomor akun user hanya penerima. Notification internal website dan webhook delivery belum dibuat. Panduan ada di `docs/WHATSAPP.md`.

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
