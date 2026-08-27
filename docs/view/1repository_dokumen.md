1. Bagian Atas: Area Pencarian & Filter (Control Panel)
Bagian ini letaknya di paling atas agar pengguna bisa langsung mengerucutkan tumpukan dokumen yang ingin dicari. Menggunakan struktur tata letak grid akan membuat form pencarian ini terlihat rapi.
    [+]Bilah Pencarian Utama (Search Bar): Pencarian teks bebas yang mendeteksi "Nomor Dokumen" atau "Judul Dokumen".
    [+]Filter Dropdown Tahun: Mengambil data dari tabel years (misal: 2024, 2025, 2026).
    [+]Filter Dropdown Bulan: Pilihan statis bulan (Januari - Desember).
    [+]Filter Dropdown Departemen: (Hanya muncul jika yang login adalah Superadmin. Jika yang login Admin/User, filter ini otomatis terkunci pada departemen mereka sendiri atau disembunyikan).
    [+]Filter Sifat Dokumen: Radio button atau Dropdown untuk memfilter (Semua / Public / Private).

2. Bagian Tengah: Tabel Data Arsip (Scrollable Data Table)
Mengingat volume dokumen seiring waktu akan bertambah banyak, menggunakan scrollable data table dengan baris yang padat (compact) sangat disarankan agar pengguna tidak perlu terlalu sering berpindah halaman (walaupun pagination tetap harus ada).

Berikut adalah referensi kolom-kolom standar yang wajib ada di dalam tabel tersebut:
No.,Nomor Dokumen,Judul Dokumen,Departemen Asal,Terbit,Sifat Akses,Aksi
1,SOP-HRD-001,Standar Cuti Tahunan,HRD,Jan 2026,🟢 Public,[👁️ View]
2,FIN-REP-089,Laporan Audit Internal,Finance,Feb 2026,🔴 Private,[👁️ View]
Catatan Kolom:
    []Terbit: Merupakan gabungan dari data Bulan dan Tahun.
    []Sifat Akses: Diberi indikator warna (badge) agar pengguna tahu apakah itu dokumen publik atau khusus internal mereka.
    []Aksi: Tempat tombol interaksi diletakkan.

3. Bagian Aksi: Interaksi Pengguna (Action Buttons)
Pada kolom "Aksi" di dalam tabel, tombol yang ditampilkan bergantung pada hak akses (Role) orang yang sedang membuka halaman tersebut:
    [+]Untuk Level User: Hanya ada 1 tombol, yaitu "View" (Ikon Mata). Ketika diklik, tombol ini akan memicu modal/pop-up di tengah layar yang berisi Document Viewer (PDF dirender langsung di browser menggunakan Native JS, tanpa berpindah halaman).
    [+]Untuk Level Admin & Superadmin: Ada tambahan opsi (bisa berupa ikon titik tiga / dropdown action):
        []"View" (Melihat isi PDF)
        []"Metadata" (Melihat detail log siapa saja yang pernah mengedit dokumen ini)
        []"Edit Hak Akses" (Pintasan cepat jika admin ingin mencabut atau menambah user yang boleh melihat dokumen private ini)

ini adalah step pertama, membangun sesuai urutan dari menu, setelah ini melakukan step ke unggah_dokumen.md