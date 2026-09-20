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

Sistem hanya digunakan untuk kebutuhan internal perusahaan.

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

### Super Admin

Dapat:

- Mengelola user dan unit organisasi.
- Mengelola lantai, fasilitas, dan ruang rapat.
- Menentukan PIC.
- Menentukan aturan akses ruangan.
- Menentukan apakah ruangan membutuhkan approval.
- Mengelola kondisi operasional ruangan.
- Melihat seluruh booking.
- Melakukan administrasi sistem.

## Ruangan

Untuk data development, gedung diasumsikan memiliki Lantai 1 sampai Lantai 8. Jangan menganggap semua lantai pasti mempunyai ruang rapat.

Informasi ruang minimal nantinya:

- Nama.
- Lantai.
- Kapasitas.
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

## Pencarian Ruangan

User sebaiknya memasukkan tanggal, jam mulai, jam selesai, dan jumlah peserta terlebih dahulu. Sistem kemudian menampilkan ruangan yang sesuai dengan mempertimbangkan:

- Status aktif.
- Maintenance.
- Kapasitas.
- Hak akses unit.
- Jadwal booking yang sudah ada.

## Booking

Data booking minimal nantinya:

- Pemohon.
- Unit kerja pemohon.
- Ruangan.
- Judul/agenda.
- Tanggal.
- Jam mulai.
- Jam selesai.
- Jumlah peserta.
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

## Notification

Tahap awal menggunakan notification internal website, misalnya:

- Booking berhasil dibuat.
- Booking approved.
- Booking rejected.
- PIC mendapatkan request approval.

Tidak perlu email, WhatsApp, atau SMS saat ini.

## Frontend

Frontend development dibuat sangat sederhana menggunakan Blade/HTML untuk memastikan seluruh fungsi dapat diuji. Semua tombol, form, tabel, filter, dan navigasi yang dibutuhkan harus tersedia. Data berasal dari Laravel dan tidak hardcoded di HTML.

Tidak perlu desain kompleks atau CSS custom berlebihan. Frontend final akan dikembangkan oleh anggota tim lain menggunakan HTML dan Tailwind CSS. Prioritaskan functionality dan struktur Blade yang mudah diganti tanpa mengubah backend.

## Requirement yang Perlu Diklarifikasi Sebelum Implementasi Terkait

Hal berikut belum diputuskan dan bukan aturan implementasi:

- Fondasi menggunakan satu role dan maksimal satu unit per user sesuai instruksi Tahap 1. Pada Tahap 2B, PIC mendapat akses halaman pegawai termasuk placeholder My Booking. Hak Super Admin untuk melakukan booking pribadi belum diputuskan.
- Apakah akses unit `restricted` diwariskan ke unit turunan?
- Apa batas waktu dan kewenangan pembatalan, serta apakah booking dapat diubah atau dijadwalkan ulang?
- Apakah booking yang bersebelahan diperbolehkan, apakah ada buffer, jam operasional, durasi maksimum, atau booking lintas hari? Zona waktu bisnis juga perlu ditetapkan.
- Kapan Pending kedaluwarsa, kapan booking menjadi Completed, dan bagaimana Completed diperlakukan dalam pemeriksaan konflik?
- Jika terdapat beberapa PIC, apakah satu keputusan PIC cukup? Apakah alasan rejection wajib dan apakah Super Admin dapat melakukan override?
- Bagaimana booking yang sudah ada ditangani ketika ruangan menjadi maintenance/nonaktif atau aturan aksesnya berubah?
- Apakah unit pemohon pada riwayat booking disimpan sebagai snapshot saat pengajuan atau mengikuti unit pegawai terkini?
