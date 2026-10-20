# Pendataan dan booking tamu

Tamu membuka `/tamu`, mengisi nama, instansi, NIP opsional, nomor kontak, dan tujuan kunjungan. Kantor/cabang tidak ditampilkan sebagai isian. Lokasi ditentukan server melalui `GUEST_BRANCH_ID`; jika belum diatur, memakai kantor dengan ID pertama dari master data.
Session browser tersebut mendapat akses `/tamu/jadwal` sampai registrasinya kedaluwarsa.
Tidak membuat user/password atau memberikan akses pegawai. Jadwal tamu hanya memuat
ruangan aktif dengan akses `all` di kantor tujuan dan jam Pending/Approved, tanpa
agenda, identitas pemohon, unit, atau catatan rapat. Halaman jadwal perlu dimuat ulang
untuk memperoleh kondisi terkini; waktu kosong bukan jaminan ketersediaan.

Admin membuka Kelola User, memilih Kelola pada pegawai/PIC yang ditunjuk, mencentang
**Petugas resepsionis**, lalu Simpan. Tidak ada role baru atau pengaturan lokasi tambahan;
petugas memakai izin kantor/cabang yang sudah diberikan pada akun. Semua akun existing
mulai tanpa izin resepsionis. Akun Admin tidak memperoleh hak booking dari checkbox ini.

Tamu cukup menyebutkan nama dan instansi kepada resepsionis. Pada form booking existing,
petugas memilih tamu melalui dropdown **Booking untuk**; pilihan **Diri sendiri** tetap
tersedia untuk booking pribadi. Pilihan tamu hanya registrasi aktif di lokasi ruangan
tersebut, dengan waktu registrasi untuk membantu membedakan nama yang sama. Pegawai biasa
tidak menerima daftar/field tamu dan tidak dapat mengirim referensi tamu lewat request
buatan. Izin diperiksa ulang di server, termasuk setelah dicabut saat form masih terbuka.
Tidak ada antrean permintaan atau menu administrasi baru.

Izin pegawai, lokasi, restricted unit, status operasional, waktu, dan bentrok tetap
diperiksa server. Booking atas nama tamu selalu membutuhkan PIC aktif dan persetujuan.
Kode registrasi tetap identifier internal untuk snapshot/idempotensi, tanpa perlu
ditunjukkan tamu atau diketik petugas.

Booking menyimpan snapshot nama, instansi, dan kode tamu serta `user_id` pegawai
pengaju. Detail booking, laporan bulanan layar, cetak/PDF, dan CSV menampilkan kedua
pihak. Kontak, NIP, dan tujuan kunjungan tidak disalin ke booking/laporan; ketiganya mengikuti masa aktif registrasi. Booking existing tetap tampil
seperti sebelumnya. Penghapusan registrasi tidak membatalkan booking atau menghapus
snapshot. Retry token pengajuan yang sama tetap mengembalikan booking existing.

## Masa aktif dan penghapusan

Default sementara: tiga hari sejak registrasi. Konfigurasi `.env` opsional:

```dotenv
GUEST_EXPIRY_MODE=days
GUEST_ACTIVE_DAYS=3
```

`GUEST_EXPIRY_MODE=end_of_day` berakhir pukul 17.00 WIB. Registrasi mulai pukul
17.00 atau sesudahnya berakhir pukul 17.00 hari berikutnya. Pilihan kebijakan ini
masih perlu dikonfirmasi pemilik proses; konfigurasi tidak mengubah expiry record
yang sudah tersimpan. Jalankan `php artisan config:clear` setelah mengubah konfigurasi.

Scheduler existing `php artisan schedule:run` setiap menit menjalankan
`guests:prune`. Untuk eksekusi manual: `php artisan guests:prune`.
Akses dan pengajuan baru langsung ditolak saat expiry, walaupun scheduler belum
sempat menghapus record. Data registrasi dan kontak dihapus permanen; snapshot
minimal pada booking dipertahankan untuk laporan.

## QR untuk resepsionis

QR mengarah ke URL publik aplikasi diikuti `/tamu`, bukan URL localhost yang tidak
dapat diakses dari ponsel tamu. Form juga ditautkan dari beranda dan form booking.
Untuk membuat berkas SVG siap cetak pada mesin pengembangan yang memiliki package
Python `qrcode`:

```powershell
python scripts/generate_guest_qr.py https://domain-aplikasi
```

Hasil: `storage/app/guest-registration-qr.svg`. Isi domain dengan alamat deployment
sebenarnya dan buat ulang QR jika alamat berubah. Tidak memakai layanan QR eksternal
atau menambah dependency runtime Laravel.

## Tampilan form

Form terpusat dengan lebar maksimum 768px, dua kolom pada desktop dan satu kolom pada mobile. Background hanya pada form registrasi; halaman jadwal tetap menggunakan latar aplikasi biasa. Aset gambar dan prompt tercatat di [GUEST_BACKGROUND.md](GUEST_BACKGROUND.md). NIP berupa string untuk mempertahankan nol awal, opsional bagi tamu yang tidak memilikinya. Tujuan wajib maksimal 2.000 karakter.
