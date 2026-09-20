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
