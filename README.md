# Document Control System (DCS)

Document Control System (DCS) adalah sebuah sistem manajemen dan repositori dokumen internal yang dibangun menggunakan PHP *native* dan PDO. Sistem ini dirancang untuk memudahkan perusahaan (seperti Berdikari Meubel Nusantara) dalam mengelola, melacak, dan membagikan dokumen antar departemen dan pengguna dengan kontrol akses yang ketat.

## 🚀 Fitur Utama

- **Manajemen Pengguna & Peran (Role-Based Access Control)**:
  Sistem ini mendukung peran seperti `Superadmin`, `Admin`, dan `User`, dengan batasan akses yang disesuaikan per peran.
- **Manajemen Departemen**: 
  Pengelompokan pengguna dan dokumen berdasarkan departemen masing-masing.
- **Repositori Dokumen**: 
  Unggah, simpan, dan kelola dokumen secara terpusat dengan dukungan pencarian berdasarkan tahun, departemen, dan nomor/judul dokumen.
- **Kontrol Akses Dokumen (Granular Access)**:
  - Dokumen *Public* (Dapat dilihat oleh semua pengguna yang login).
  - Dokumen *Private* (Dibatasi).
  - Hak akses spesifik untuk departemen tertentu (`document_dept_access`).
  - Hak akses spesifik untuk user tertentu (`document_access`).
- **Riwayat Revisi Dokumen**:
  Melacak setiap pembaruan atau revisi pada dokumen.
- **Pembagian Dokumen (Share)**:
  Fitur untuk membagikan tautan dokumen baik untuk pihak internal maupun eksternal.
- **Recycle Bin (Soft Delete)**:
  Dokumen yang dihapus tidak langsung hilang permanen, melainkan masuk ke *Recycle Bin* terlebih dahulu.
- **Log Aktivitas**:
  Perekaman aktivitas pengguna secara otomatis (login, unggah dokumen, hapus dokumen, dsb).

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 8.x (*Native* / Tanpa Framework)
- **Database**: MySQL / MariaDB (via PDO). Mendukung juga SQLite untuk lingkungan *development*.
- **Frontend**: HTML5, Vanilla CSS, Vanilla JavaScript.

## 📁 Struktur Direktori Utama

- `config/` - Pengaturan aplikasi, termasuk konfigurasi koneksi database (`database.php`).
- `core/` - File inti sistem seperti middleware autentikasi dan pencatat log.
- `modules/` - Pemisahan logika aplikasi (User, Master, Department, Document).
- `public/` - Folder utama (*DocumentRoot*) yang berisi antarmuka pengguna, *entry point* (`index.php`), dan skrip migrasi.
- `storage/` - Folder untuk menyimpan file dokumen yang diunggah serta file database SQLite (jika digunakan).

## ⚙️ Persyaratan Sistem

- Web Server (Apache/Nginx/Laragon/XAMPP).
- PHP 8.0 atau yang lebih baru (dengan ekstensi `pdo_mysql` diaktifkan).
- MySQL 5.7+ atau MariaDB.

## 🚀 Panduan Instalasi (Setup)

1. **Clone atau Letakkan File Project**
   Letakkan folder `dokumenkontrol` ini ke dalam folder Document Root web server Anda (misal: `c:\laragon\www\` atau `C:\xampp\htdocs\`).

2. **Pengaturan Database**
   Secara default, sistem akan mencoba koneksi ke MySQL `localhost` (root, tanpa password) dengan nama database `documentcontrol`. 
   Jika konfigurasi Anda berbeda, sesuaikan *environment variables* atau edit langsung di file `config/database.php`.

3. **Inisialisasi Database (Setup Awal)**
   Jika database masih kosong, jalankan skrip inisialisasi awal. Buka *browser* Anda dan kunjungi:
   ```text
   http://localhost/dokumenkontrol/init2_db.php
   ```
   *(Sistem akan otomatis membuat struktur tabel dasar).*

4. **Menjalankan Migrasi Tambahan**
   Untuk memastikan seluruh fitur baru (seperti *recycle bin*, akses departemen, dan riwayat dokumen) berjalan lancar, Anda **harus** menjalankan skrip migrasi berikut melalui *browser*:
   - `http://localhost/dokumenkontrol/public/migrate_status_column.php`
   - `http://localhost/dokumenkontrol/public/migrate_dept_access.php`
   - `http://localhost/dokumenkontrol/public/migrate_share.php`
   - `http://localhost/dokumenkontrol/public/migrate_history.php`

5. **Login Pertama Kali**
   Setelah instalasi selesai, buka:
   ```text
   http://localhost/dokumenkontrol/public/
   ```
   Silakan login menggunakan akun Superadmin *default* yang telah ter-generate (jika di-set di file init) atau buat user secara manual di database.

## 📝 Catatan Tambahan
Pastikan folder `storage/` memiliki izin (*permissions*) *Read* dan *Write* agar proses unggah file tidak terhambat.
