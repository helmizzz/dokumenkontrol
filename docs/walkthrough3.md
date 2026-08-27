# Ringkasan Eksekusi Step 3 (Master Data)

Menu Master Data kini telah tersedia! Halaman ini memiliki peran krusial dalam mengatur struktur perusahaan dan batas tahun arsip dengan metode **Soft Delete** yang aman.

## Perubahan yang Dilakukan
1. **Pembaruan Skema Database (Migrasi)**: Membuat skrip `migrate_master.php` untuk menambah kolom `status` dan `dept_code` ke dalam *database* tanpa merusak data lama.
2. **Halaman Master Data (UI)**: Membuat antarmuka *tabbing* ringan menggunakan JS Native untuk memisahkan menu "Departemen" dan "Tahun". Menyertakan fitur tambah data, validasi HTML (Tahun 4 digit), dan tombol toggle Aktif/Nonaktif.
3. **Logika Prosesor (Backend)**: Membuat `master_proc.php` yang terintegrasi dengan Audit Trail untuk mencatat setiap pembuatan atau penutupan master data.
4. **Pembaruan Halaman Unggah**: Menyesuaikan kembali kueri di menu "Unggah Dokumen" agar Tahun yang sudah berstatus *Tutup (Closed)* tidak lagi muncul di *dropdown*.

## Cara Menguji / Verifikasi Manual

> [!IMPORTANT]
> **Langkah 1: Eksekusi Migrasi Database (Wajib!)**
> Sebelum membuka halaman Master Data, Anda **wajib** mengeksekusi skrip migrasi dengan mengunjungi tautan berikut:
> `http://localhost/dokumenkontrol/public/migrate_master.php`
> Setelah selesai, sistem akan langsung melempar Anda ke halaman Master Data.

> [!TIP]
> **Langkah 2: Pengujian Fitur**
> 1. Cobalah tambahkan sebuah Departemen baru beserta kodenya.
> 2. Cobalah tambahkan Tahun Arsip baru (misalnya 2027).
> 3. Coba **Nonaktifkan** salah satu departemen, perhatikan *badge* indikatornya berubah menjadi abu-abu/merah.
> 4. Buka menu **Unggah Dokumen** di tab atau jendela baru. Cek pilihan dropdown Tahun. Kembali ke Master Data, coba **Tutup** tahun 2026. *Refresh* halaman Unggah Dokumen, dan pastikan 2026 menghilang dari pilihan!

Jika pengujian Anda sudah memuaskan, silakan kembali kemari dan perintahkan saya untuk melanjutkan ke **Step 4 (Manajemen User)**.
