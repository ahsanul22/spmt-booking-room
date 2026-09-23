# Panduan Frontend Tim

Panduan aktif untuk pekerjaan frontend melalui CLI maupun integrasi HTML dari AI web. Baca bersama `../AGENTS.md`, konteks, progress, dan keputusan proyek. Kerjakan hanya halaman yang ditugaskan; pembagian anggota tim mengikuti penugasan pengguna.

## Referensi utama

Buka `/schedule` (pegawai/PIC) atau `/admin/schedule` (admin) setelah login. Pelajari file berikut sebelum mendesain:

| File | Fungsi |
| --- | --- |
| `resources/views/shared/schedule.blade.php` | Komposisi konten, card, tombol, panel agenda, empty state |
| `resources/views/layouts/schedule.blade.php` | Sidebar, header akun, navigasi mobile, footer |
| `resources/views/components/schedule-icon.blade.php` | Ikon SVG lokal |
| `resources/css/app.css` | Entry Tailwind, komponen navigasi, fokus keyboard |
| `resources/js/schedule.js` | Interaksi kalender dan menu mobile |
| `tailwind.config.js` | Sumber token warna dan font |

Ikuti gaya visual referensi, bukan isi kalendernya. Layout jadwal saat ini mempunyai judul, breadcrumb, dan menu aktif khusus jadwal. Jangan langsung mewariskannya pada halaman lain tanpa menyesuaikan bagian tersebut. Jika perlu dipakai bersama, ekstrak/parameterkan bagian yang diperlukan dalam lingkup task, pertahankan tampilan jadwal, dan catat file bersama yang berubah. Tidak perlu migrasi semua halaman sekaligus.

## Palet wajib

| Token | Hex | Penggunaan |
| --- | --- | --- |
| primary | `#216EAD` | Tombol utama, aksi, penanda aktif |
| primaryDark | `#0E336A` | Sidebar, judul, panel penekanan |
| secondary | `#5EA9D8` | Aksen, ikon dekoratif |
| secondaryLight | `#AFCFE4` | Latar aksen lembut, border pilihan |
| background | `#F2F4F5` | Latar halaman |
| surface | `#FFFFFF` | Card, header, form |
| muted | `#A2A2A6` | Elemen nonaktif/dekoratif; hindari teks kecil penting di atas putih |
| text | `#222424` | Teks utama |
| danger | `#D4302C` | Error, aksi destruktif |

Gunakan `bg-primary`, `text-primaryDark`, `bg-surface`, `border-secondaryLight`, dan token lainnya, bukan hex berulang. Neutral slate yang sudah dipakai referensi boleh untuk border dan teks pendukung. Status harus memiliki label teks, bukan warna saja. Periksa kontras teks; opacity dan warna aksen tidak otomatis aman untuk semua ukuran teks.

## Gaya dan komponen

- Font mengikuti `font-sans` existing (Segoe UI, Arial, sans-serif); jangan menambah font CDN.
- Gunakan header surface dan latar background. Sidebar primaryDark berlaku untuk PIC/admin; role user memakai navbar `layouts.user` sesuai DEC-024. Referensi admin/jadwal adalah gaya visual, sedangkan susunan konten mengikuti tugas role: approval untuk PIC, pencarian dan booking pribadi untuk user. Halaman mempunyai satu h1, deskripsi singkat, serta aksi utama yang relevan dengan role.
- Ikuti skala referensi: konten `p-5 lg:p-9`, jarak `gap-4`/`gap-6`, card `rounded-2xl`, tombol `rounded-lg`/`rounded-xl`, border halus dan `shadow-sm` secukupnya.
- Gunakan komponen/partial untuk form, tabel, badge, dan detail yang berulang. Periksa pemakai komponen sebelum mengubahnya agar halaman lain tetap berfungsi.
- Sidebar PIC/admin pada mobile dapat dibuka/tutup. Navbar user membungkus dan tetap terlihat pada mobile tanpa JavaScript. Form menyesuaikan lebar layar; tabel/kalender lebar boleh scroll di dalam container, bukan membuat seluruh halaman melebar.
- Sediakan label input, fokus keyboard terlihat, aria-label tombol ikon, dan teks error yang berhubungan dengan input. Beri state hover/focus/disabled yang jelas.
- Semua tombol harus menjalankan aksi, navigasi, atau memiliki status belum tersedia yang jelas. Jangan menampilkan keberhasilan palsu lewat JavaScript.

## Kontrak Laravel yang harus dipertahankan

Master data admin sampai assignment PIC/unit sudah aktif. Baca controller, Form Request, route, dan Blade modul sebelum mengedit. Pertahankan `route()`, `name` field, method, `@csrf`, `@method`, `old()`, pesan validasi/flash, pilihan database, dan pagination. Password tidak boleh diprefill. Gunakan escaping Blade `{{ }}` untuk data pengguna.

Menu/aksi mengikuti `@can` existing; keamanan server tetap ditangani middleware/Gate. Jangan mengganti authorization dengan penyembunyian tombol di JavaScript. Jangan menambah query database di Blade atau mengubah schema, role, dan kebijakan bisnis untuk keperluan styling.

Booking, Approval, kalender dengan data nyata, dan dashboard statistik belum memiliki backend lengkap. Gunakan empty state dan penanda pratinjau, pertahankan aksi tulis yang belum tersedia sebagai nonaktif. Kalender kosong tidak berarti ruangan tersedia. Jangan membuat data bisnis contoh permanen, statistik palsu, atau endpoint baru hanya untuk mengisi desain.

## Alur kerja CLI

1. Baca dokumen konteks dan cek `git status`; identifikasi halaman, route, dan file yang ditugaskan. Jangan menimpa pekerjaan anggota lain.
2. Buka referensi Jadwal Ruangan. Cek kontrak data halaman tujuan dan komponen yang bisa digunakan ulang.
3. Gunakan dependency existing. Pada checkout baru yang belum punya node_modules, jalankan `npm ci`. Pastikan setup Laravel/PostgreSQL lokal sudah mengikuti dokumentasi proyek; jangan reset database bersama.
4. Jalankan `php artisan serve` dan `npm run dev` pada terminal terpisah. Buka halaman setelah login dengan role yang sesuai. Alternatif aset statis: `npm run build`.
5. Ubah Blade/Tailwind dan JavaScript seperlunya. Jika menerima HTML dari AI web, pindahkan isi ke view/partial, ganti URL statis dengan route Laravel, ganti data contoh dengan variabel, dan hapus CDN/framework yang tidak digunakan proyek.
6. Jalankan `npm run build`, `php artisan view:cache`, lalu `php artisan view:clear`. Jalankan test fitur modul yang terdampak; bila mengubah navigasi/layout umum, sertakan `php artisan test --filter=FrontendSkeletonTest` dan test authorization yang relevan. Jangan melemahkan test fungsional hanya agar desain lolos.
7. Periksa desktop/mobile, navigasi keyboard, empty state, error/old input, submit aktif, dan izin role. Catat jika browser atau test tidak dapat dijalankan, beserta alasan sebenarnya.
8. Update `docs/PROGRESS.md` dengan halaman/file yang berubah, URL untuk review, hasil verifikasi, dan bagian yang masih pratinjau. Bila diminta commit, kelompokkan per halaman/modul dan jangan sertakan .env, node_modules, atau public/build.

## Contoh instruksi untuk CLI

> Kerjakan frontend halaman [nama halaman] untuk role [role], pada file [path]. Baca AGENTS.md dan docs/FRONTEND_GUIDE.md. Ikuti desain Jadwal Ruangan dan palet tailwind.config.js. Pertahankan route, kontrak form, data Laravel, dan authorization existing. Gunakan komponen bersama bila sesuai; jangan mengimplementasikan backend tahap berikutnya. Uji halaman yang terdampak dan catat hasil di docs/PROGRESS.md.

Catatan tahap lama di `FRONTEND.md` dan `DECISIONS.md` merupakan riwayat. Untuk pekerjaan UI baru, aturan aktif pada panduan ini dan DEC-020 menggantikan batasan HTML sederhana untuk testing.
