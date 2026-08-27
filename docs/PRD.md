PRD.md (Product Requirement Document)
1. Objective & Scope
Dokumen ini mendefinisikan kebutuhan produk untuk Document Control System (DCS) perusahaan. Sistem ini dirancang untuk mendigitalisasi, mengamankan, dan mengorganisasi dokumen berbasis teks khusus dalam format PDF. Sistem ini berfokus pada efisiensi penelusuran dokumen melalui klasifikasi berbasis departemen dan tahun, serta pembatasan hak akses yang ketat antar-pengguna tanpa melalui proses persetujuan yang rumit (instant activation).

2. User Roles & Personas
Sistem ini wajib memfasilitasi tiga tingkatan hak akses pengguna (Role-Based Access Control):

Superadmin: Pemegang otoritas tertinggi sistem. Bertanggung jawab atas pengelolaan konfigurasi global termasuk manajemen role, data master departemen, akun pengguna, serta pengawasan seluruh aktivitas sistem melalui log viewer.

Admin: Pengelola dokumen perwakilan dari departemen tertentu. Memiliki hak untuk mengunggah, memperbarui, dan menghapus dokumen, serta mengatur hak akses private dokumen kepada sesama pengguna di dalam departemen yang sama.

User: Personel operasional perusahaan. Hanya memiliki hak untuk mencari, memfilter, dan membaca dokumen PDF melalui viewer bawaan sistem sesuai dengan izin khusus atau sifat dokumen (public).

3. Functional Requirements
3.1 Manajemen Akses & Identitas (IAM)
FR-IAM-01: Sistem harus menyediakan fitur Login dan Logout berbasis session yang aman.

FR-IAM-02 (CRUD Role Akses): Superadmin dapat menambah, melihat, memperbarui, dan menghapus tipe role beserta pemetaan hak akses menunya.

FR-IAM-03 (CRUD User): Superadmin dapat mendaftarkan, memperbarui, memblokir, atau menghapus data pengguna dengan mengaitkannya ke Role ID dan Departemen ID.

3.2 Manajemen Master Data
FR-MST-01 (CRUD Departemen): Superadmin dapat mengelola data master departemen yang ada di perusahaan.

FR-MST-02 (CRUD Tahun): Superadmin dapat mengelola data master tahun yang valid untuk membatasi atau mengelompokkan masa aktif arsip dokumen.

3.3 Manajemen Dokumen & Hak Akses
FR-DOC-01 (CRUD Dokumen): Admin dan Superadmin dapat mengunggah dokumen baru. Dokumen yang diunggah wajib divalidasi dan hanya diizinkan dalam format PDF. Proses penyimpanan langsung mengaktifkan dokumen secara instan tanpa alur approval.

FR-DOC-02 (Sifat Dokumen): Saat proses unggah, dokumen wajib didefinisikan sebagai Public atau Private.

FR-DOC-03 (CRUD Dokumen Access): Jika dokumen bersifat Private, Admin/Superadmin dapat mengelola checkbox daftar pengguna dari departemen yang sama yang berhak melihat dokumen tersebut. Jika bersifat Public, dokumen otomatis dapat diakses lintas departemen tanpa entri data akses tambahan.

3.4 Pencarian, Filtrasi, & Visualisasi
FR-VIS-01 (Document Viewer): Sistem menyediakan pemutar internal untuk membaca berkas PDF langsung di dalam browser pengguna (tanpa memicu unduhan berkas otomatis).

FR-VIS-02 (Filter Waktu): Pengguna dapat memfilter repositori dokumen berdasarkan kombinasi elemen Tahun (mengambil dari master tahun) dan Bulan.

FR-VIS-03 (Grafik Jumlah Dokumen): Halaman dashboard menampilkan visualisasi grafik statistik volume dokumen terunggah berdasarkan rentang waktu tertentu atau klaster departemen.

3.5 Jejak Audit (Audit Trail)
FR-AUD-01 (Log Viewer): Sistem secara otomatis merekam setiap aktivitas perubahan data (operasi Create, Update, Delete saja) yang dilakukan oleh pengguna mana pun terhadap data dokumen maupun data master. Aktivitas membaca (View) tidak direkam untuk menghemat ruang penyimpanan.

4. Non-Functional Requirements & Constraints
Security Constraint: Berkas PDF tidak boleh diletakkan di direktori publik yang dapat diakses langsung via URL browser dengan tebakan nama file.

Format Restriction: Penolakan tegas di tingkat server (backend validation) terhadap seluruh ekstensi berkas non-PDF (misal: .docx, .png, .xlsx).