Mari kita bedah menu Manajemen User. Halaman ini adalah "jembatan" yang menghubungkan data personel perusahaan dengan aturan Hak Akses (Role) dan Master Departemen yang telah kita susun sebelumnya.

Mengingat tingginya tingkat perputaran karyawan (seperti mutasi departemen atau resign), pengelolaan database pengguna tidak boleh menggunakan sistem hapus permanen (DELETE), melainkan harus menggunakan penonaktifan status untuk menjaga jejak sejarah pengunggah dokumen lama.

Berikut adalah rancangan detail untuk halaman Manajemen User:

[-]Tata Letak Utama (Daftar Pengguna)
Halaman ini dikendalikan penuh oleh Superadmin. Tata letaknya menggunakan tabel data dengan fitur pencarian cepat untuk mempermudah pelacakan akun karyawan.
No.,Nama Lengkap,Username,Departemen,Level Akses,Status,Aksi
1,Budi Santoso,budi.hrd,HRD,Admin,🟢 Aktif,[Edit] [Reset Pass]
2,Siti Aminah,siti.fin,Finance,User,🟢 Aktif,[Edit] [Reset Pass]
3,Andi Wijaya,andi.mkt,Marketing,User,🔴 Nonaktif,[Edit] [Aktifkan]

2. Form Pembuatan & Edit Akun
Ketika Superadmin menekan tombol "+ Tambah User" atau "Edit", sebuah modal atau form halaman baru akan muncul. Berikut adalah susunan field input beserta logikanya:

[-]Nama Lengkap: Input teks standar untuk nama tampilan karyawan.
[-]Username: Input teks tanpa spasi. Harus dilengkapi validasi real-time (menggunakan Native JS dan AJAX) untuk memastikan username tersebut belum terdaftar di database.
[-]Password Default (Hanya saat Tambah Baru): Daripada mengetik manual, sistem disarankan menggunakan password default (misal: Perusahaan123!) atau tombol "Generate Password" otomatis. Nantinya, password ini langsung dienkripsi menggunakan fungsi password_hash() bawaan PHP sebelum masuk ke database.
[-]Pilih Role (Dropdown): Menampilkan pilihan dari tabel roles (Superadmin, Admin, User).
[-]Pilih Departemen (Dropdown): Menampilkan daftar departemen dari tabel departments yang berstatus Aktif.

Status Akun (Radio Button): 🟢 Aktif / 🔴 Nonaktif.

3. Interaksi Dinamis Form (Logika Frontend)
Untuk membuat antarmuka lebih cerdas dan meminimalisir kesalahan input dari Superadmin, kita bisa menambahkan sedikit manipulasi Document Object Model (DOM) menggunakan JavaScript:
[-]Logika Pengecualian Superadmin:
Jika di form pembuatan akun Superadmin memilih Role "Superadmin", maka dropdown Departemen otomatis disembunyikan atau diubah nilainya menjadi "Semua Departemen" (Global Access). Hal ini karena Superadmin memiliki otoritas melintasi batas departemen.

[-]Logika Mutasi Karyawan (Edit):
Jika seorang karyawan pindah departemen, Superadmin cukup mengedit profilnya dan mengganti pilihan Departemennya. Perubahan ini secara otomatis akan mencabut hak aksesnya dari dokumen private di departemen lama, dan memberinya akses ke departemen baru.

4. Fitur Keamanan: Reset Password & Penonaktifan
[-]Tombol Reset Password: Terletak di tabel utama (hanya bisa diklik oleh Superadmin). Jika karyawan lupa password, tombol ini akan memicu prompt konfirmasi, lalu sistem akan mengembalikan password karyawan tersebut ke password default (dan mencatatnya di Log Aktivitas).

[-]Logika Karyawan Resign (Nonaktif): Jika Budi resign, Superadmin cukup mengubah statusnya menjadi "Nonaktif". Budi tidak akan bisa login lagi, tetapi nama Budi akan tetap tercetak abadi di semua dokumen yang pernah ia unggah di masa lalu (kolom uploaded_by tidak menjadi error/null).

Dengan rancangan ini, kontrol terhadap keluar masuknya personel perusahaan menjadi sangat rapi dan tidak merusak tatanan arsip.

selesai sudah step keempat, setelah ini melakukan step ke audit_trail.md