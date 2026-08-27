Mari kita bedah menu terakhir dan yang paling krusial untuk keamanan serta transparansi aplikasi: Audit Trail (Log Viewer).
Dalam sistem manajemen dokumen, Audit Trail berfungsi sebagai "CCTV" yang merekam setiap perubahan data. Seperti kesepakatan kita sebelumnya, agar database tidak cepat penuh, sistem hanya akan merekam aktivitas modifikasi data (Create, Update, Delete), bukan aktivitas membaca (View).
Karena ini adalah data sensitif, akses ke menu ini idealnya hanya diberikan kepada Superadmin (atau Admin dengan batasan hanya bisa melihat log departemennya sendiri).
Berikut adalah rancangan detail untuk halaman Audit Trail:
1. Tata Letak (UI Layout) Utama
Halaman ini dirancang murni untuk analisis data. Antarmukanya harus fokus pada tabel pencarian yang luas dan mudah dibaca.
[-]Area Filter Canggih (Advanced Filtering)
Mengingat baris data log akan bertambah ribuan setiap bulannya, bilah pencarian biasa tidak akan cukup. Kita membutuhkan panel filter di bagian atas:
    [+]Filter Rentang Waktu (Date Range): Input Tanggal Mulai s/d Tanggal Akhir. Ini sangat penting untuk membatasi kueri (query) MySQL agar peladen tidak kelebihan beban (overload).
    [+]Filter Modul (Dropdown): Memilih log khusus untuk entitas tertentu (Misal: Semua, Dokumen, User, Departemen, Tahun, Akses).
    [+]Filter Aksi (Dropdown): Memilih jenis tindakan (Misal: Semua, CREATE, UPDATE, DELETE).
    [+]Bilah Pencarian (Search Bar): Untuk mencari spesifik nama/username pelaku, atau nomor dokumen spesifik.
[-]Tabel Data Jejak Audit
Data diurutkan dari yang paling baru (ORDER BY created_at DESC).
No.,Waktu Kejadian,Pelaku (Username),Aksi,Modul,Deskripsi Detail
1,03-Jul-2026 10:15:22,budi.hrd,🟢 CREATE,Dokumen,Budi mengunggah dokumen baru: SOP-HRD-005
2,03-Jul-2026 09:45:10,admin.sys,🟠 UPDATE,Akses,Menambahkan [siti.fin] ke daftar akses dokumen FIN-002
3,02-Jul-2026 16:30:00,joko.it,🔴 DELETE,User,Menonaktifkan akun user: andi.mkt

2. Aturan Emas Arsitektur Backend (Security Rules)
Membangun modul log berbeda dengan membangun modul CRUD lainnya. Ada beberapa aturan teknis yang wajib diterapkan di tingkat PHP dan MySQL:
[-]Sifat Imutabilitas (Read-Only): Tabel activity_logs di dalam MySQL tidak boleh memiliki tombol/fungsi UPDATE atau DELETE di antarmuka sistem. Bahkan seorang Superadmin pun tidak boleh bisa mengubah atau menghapus log melalui aplikasi. Halaman ini 100% hanya berisi fungsi SELECT.
[-]Perekaman Data Pasif (Helper Function): Seperti yang sudah dirancang pada Implementation.md, perekaman log tidak dilakukan secara manual oleh pengguna, melainkan ditembakkan secara otomatis oleh fungsi PHP (misal write_audit_log()) tepat setelah eksekusi kueri insert/update di modul lain berhasil dijalankan.
[-]Database Indexing: Karena tabel ini akan menjadi yang paling besar di database, tim developer wajib menambahkan Index pada kolom created_at dan module di struktur tabel MySQL. Tanpa indexing, pencarian log dalam rentang tanggal tertentu akan memakan waktu proses yang sangat lama ketika data sudah mencapai ratusan ribu baris.

Dengan selesainya bedah menu Audit Trail ini, maka cetak biru (blueprint) dari Document Control System (DCS) ini sudah utuh—mulai dari struktur database, flowchart, dokumen PRD/SRD, hingga rancangan tata letak tiap halamannya.