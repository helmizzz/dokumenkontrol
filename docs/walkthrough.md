# Ringkasan Fase 1 (Inisialisasi & Login)

Sistem dasar dan fungsionalitas login telah selesai diimplementasikan. Berikut adalah apa saja yang telah dikerjakan dan cara Anda melakukan pengujian.

## Perubahan yang Dilakukan
1. **Struktur Direktori**: Berhasil membuat kerangka sesuai dengan arsitektur (config, core, public, storage).
2. **Database (SQLite)**: File [database.php](file:///c:/laragon/www/dokumenkontrol/config/database.php) sudah dikonfigurasi untuk terhubung ke file SQLite.
3. **Sistem Middleware**: File [auth_middleware.php](file:///c:/laragon/www/dokumenkontrol/core/auth_middleware.php) dibuat untuk memblokir akses jika pengguna belum login.
4. **Security (.htaccess)**: Diletakkan di [storage/secure_docs/.htaccess](file:///c:/laragon/www/dokumenkontrol/storage/secure_docs/.htaccess) untuk melindungi PDF.
5. **Autentikasi (UI & Proses)**: 
   - [index.php](file:///c:/laragon/www/dokumenkontrol/public/index.php): Halaman *login* responsif & bersih (Vanilla CSS).
   - [login_proc.php](file:///c:/laragon/www/dokumenkontrol/public/login_proc.php): Validasi sesi berbasis *hash password*.
   - [logout.php](file:///c:/laragon/www/dokumenkontrol/public/logout.php): Proses penghancuran sesi.
   - [dashboard.php](file:///c:/laragon/www/dokumenkontrol/public/dashboard.php): Tampilan awal (sementara) yang hanya muncul setelah login berhasil.

## Cara Menguji / Verifikasi Manual

> [!IMPORTANT]
> **Langkah 1: Inisialisasi Database**
> Buka browser Anda dan akses link berikut untuk membuat tabel dan akun Superadmin awal:
> `http://localhost/dokumenkontrol/init_db.php`
> *(Pastikan Laragon Apache Anda sedang menyala)*

> [!TIP]
> **Langkah 2: Uji Coba Login**
> Setelah *database* terbuat, akses halaman login di:
> `http://localhost/dokumenkontrol/public/index.php`
> 
> Gunakan kredensial *default*:
> - **Username**: `admin`
> - **Password**: `admin123`

Setelah login berhasil, Anda akan diarahkan ke `dashboard.php` dan akan melihat sesi Anda (sebagai Superadmin dari departemen IT). Anda bisa mencoba menekan tombol "Logout" untuk memastikan keamanannya berfungsi.
