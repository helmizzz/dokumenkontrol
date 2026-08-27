menu Unggah Dokumen. Karena menu ini menjadi pintu masuk utama data ke dalam sistem, antarmukanya harus dirancang intuitif, meminimalisir human error, dan memanfaatkan DOM manipulasi menggunakan Vanilla JavaScript agar terasa responsif tanpa memuat ulang halaman (reload).

Hanya pengguna dengan role Superadmin dan Admin yang dapat mengakses halaman ini. Berikut adalah rancangan tata letak dan perilaku interaktif untuk halaman Unggah Dokumen:

1. Tata Letak (UI Layout)
Halaman ini idealnya dibagi menjadi dua kolom utama (CSS Grid/Flexbox) pada tampilan desktop agar pengguna tidak perlu scroll terlalu panjang ke bawah:

[-]Kolom Kiri: Metadata & File (Informasi Utama)
    [+]Input Nomor Dokumen: Kolom teks standar. Bisa ditambahkan validasi real-time via AJAX untuk mengecek apakah nomor dokumen sudah pernah dipakai sebelumnya.
    [+]Input Judul Dokumen: Kolom teks (wajib diisi).
    [+]Pilihan Waktu (Dropdown Tahun & Bulan):
        []Tahun otomatis mengambil dari tabel master years.
        []Bulan menggunakan daftar statis (Januari - Desember).
    [+]Area Unggah (File Dropzone):
        []Area visual berbentuk kotak putus-putus (dashed border) yang mendukung fitur Drag and Drop.
        []Hanya menerima ekstensi .pdf (Atribut HTML: accept="application/pdf").
        []Ketika file PDF dipilih, kotak ini akan berubah warna dan menampilkan nama file serta ukurannya.
[-]Kolom Kanan: Pengaturan Hak Akses (Access Control)
    [+]Pilihan Sifat Dokumen (Radio Buttons):
    🔘 Public (Bisa diakses seluruh departemen).
    🔘 Private (Akses terbatas).
    [+]Panel Akses Spesifik (Dinamis):
        []Area ini secara default disembunyikan (hidden) jika opsi "Public" dipilih.
        []Jika pengguna memilih "Private", area ini langsung muncul (slide down) menggunakan Native JS.
        []Isi panel ini adalah daftar kotak centang (Checkboxes) yang berisi nama-nama User yang berada di departemen yang sama dengan Admin yang sedang mengunggah.
        []Terdapat opsi "Pilih Semua" (Check All) untuk mempercepat tugas Admin jika ingin memberikan akses ke seluruh tim di departemennya.

2. Perilaku Sistem & Validasi (Frontend Logic)
Untuk memastikan data yang masuk sudah bersih sebelum dikirim ke backend PHP, halaman ini membutuhkan beberapa skrip Native JS:
    [+]Validasi Ekstensi Berkas: Jika pengguna mencoba mengunggah file .docx atau gambar, JS akan memblokirnya langsung di peramban (browser) dan memunculkan peringatan: "Format tidak didukung. Harap unggah file PDF."
    [+]Ukuran Berkas Maksimal: Menambahkan batas maksimal ukuran file (misalnya 10 MB atau 20 MB) di sisi frontend menggunakan file.size agar tidak membebani lalu lintas (traffic) peladen (server) saat proses unggah.
    [+]Tombol Submit (Simpan): Ketika ditekan, tombol ini akan berubah status menjadi disabled dan menampilkan teks "Mengunggah..." (Loading state) untuk mencegah Admin menekan tombol berkali-kali secara tidak sengaja (mencegah data ganda).
    
3. Alur Setelah Simpan Berhasil
Karena sistem ini tidak menggunakan mekanisme approval, begitu dokumen berhasil diunggah:
    [+]Dokumen langsung masuk ke repositori.
    [+]Pengguna langsung diarahkan kembali ke menu View Repository Dokumen dengan notifikasi pop-up (misal: Toast notification warna hijau) yang menyatakan "Dokumen [Nomor] Berhasil Diunggah".
Rancangan kolom interaktif untuk penentuan Public dan Private di atas akan sangat memudahkan Admin karena mereka tidak perlu pindah ke halaman lain untuk mengatur hak akses.

selesai sudah step kedua, setelah ini melakukan step ke master_data.md