Mari kita bedah menu Master Data. Berbicara mengenai pengelolaan tabel master di dalam aplikasi web native yang menggunakan relasi database ketat, ada satu komponen krusial yang harus ditambahkan: Status Aktif/Non-aktif (Logika Soft Delete).

Jika sebuah Departemen atau Tahun dihapus secara permanen (menggunakan perintah DELETE di SQL) padahal sudah ada dokumen yang terikat pada data tersebut, sistem akan mengalami error (pelanggaran Foreign Key) atau menghasilkan data yatim (orphaned data). Oleh karena itu, kita akan menambahkan atribut Status pada rancangan menu ini.

Hanya Superadmin yang memiliki akses ke menu ini. Menu ini bisa dibagi menjadi dua tab atau dua sub-menu agar antarmukanya tetap bersih.

1. Sub-menu: Master Departemen
Halaman ini digunakan untuk mendefinisikan struktur organisasi perusahaan.

[-]Tata Letak (UI Layout)
    [+]Area Atas: Tombol "+ Tambah Departemen" dan bilah pencarian cepat (quick search).
    [+]Tabel Data: Menampilkan daftar departemen.
No.,Kode Dept,Nama Departemen,Status,Aksi
1,HRD,Human Resources,🟢 Aktif,[Edit] [Nonaktifkan]
2,FIN,Finance & Accounting,🟢 Aktif,[Edit] [Nonaktifkan]
3,MKT,Marketing Lama,🔴 Nonaktif,[Edit] [Aktifkan]

[-]Logika & Interaksi (Frontend & Backend)
    [+]Kode Dept (Opsional tapi Direkomendasikan): Menambahkan singkatan (3-4 huruf) sangat berguna untuk pengkodean nomor dokumen otomatis nantinya (misal: SOP-HRD-001).
    [+]Logika Soft Delete (Nonaktifkan): Ketika Superadmin menekan "Nonaktifkan", sistem menjalankan UPDATE departments SET status = 0 WHERE id = ?.
    [+]Efek pada Sistem: Departemen yang berstatus "Nonaktif" tidak akan muncul lagi di dropdown pilihan saat Admin mengunggah dokumen baru atau saat menambah User baru. Namun, dokumen lama yang sudah terlanjur menggunakan departemen tersebut akan tetap aman dan bisa diakses.

2. Sub-menu: Master Tahun
Halaman ini digunakan untuk mengontrol tahun pembukuan atau tahun arsip dokumen.
[-]Tata Letak (UI Layout)
Sama seperti Departemen, menggunakan kombinasi tombol tambah dan tabel data.
No.,Tahun Arsip,Status Periode,Aksi
1,2026,🟢 Buka (Open),[Tutup Periode]
2,2025,🔴 Tutup (Closed),[Buka Periode]

[-]Logika & Interaksi (Frontend & Backend)
    [+]Validasi Input: Form tambah tahun hanya menerima angka 4 digit (validasi Native JS dan atribut HTML pattern="[0-9]{4}"). Sistem harus menolak jika tahun yang dimasukkan sudah ada di database (mencegah duplikasi).
    [+]Logika Tutup Periode: Jika sebuah tahun diubah statusnya menjadi "Tutup", maka tahun tersebut hilang dari pilihan dropdown saat mengunggah dokumen baru. Ini berfungsi untuk mengunci masa lalu, mencegah Admin mengunggah dokumen baru dan memundurkan tanggalnya ke tahun yang sudah ditutup secara sengaja (menjaga integritas data audit).
    [+]Efek pada Filter: Meskipun berstatus "Tutup", tahun tersebut tetap muncul di dropdown filter pada menu View Repository Dokumen, agar pengguna tetap bisa mencari arsip lama.
Kesimpulan untuk Master Data
Dengan penambahan fitur Status (Soft Delete), pengelolaan master data menjadi sangat aman untuk skala perusahaan jangka panjang, karena tidak akan merusak integritas database (MySQL) di belakang layar.

selesai sudah step ketiga, setelah ini melakukan step ke manajemen_user.md