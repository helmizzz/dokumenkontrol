SRD.md (System Requirement Document)
1. System ArchitectureSistem dibangun menggunakan arsitektur web berbasis klien-server (Monolithic Client-Server Approach) yang dioptimalkan untuk kinerja cepat tanpa ketergantungan framework berat:Backend Engine: Native PHP (Mengeksekusi kendali sesi, keamanan, operasi I/O file, dan pemrosesan kueri database).Frontend Engine: Vanilla JavaScript & CSS Grid/Flexbox (Membangun antarmuka responsif dan menangani interactivity grafik serta PDF viewer).Database: MySQL (Menangani penyimpanan data relasional dengan indeks pada kolom pencarian).
2. Database Schema Design (MySQL)Berikut rancangan struktur tabel dan relasinya untuk mendukung integritas data sistem:  
  +---------------+        +-------------------+        +---------------+
  |     roles     |        |    departments    |        |     years     |
  +---------------+        +-------------------+        +---------------+
  | PK | id       |<--+    | PK | id           |<--+    | PK | id       |
  +---------------+   |    +-------------------+   |    +---------------+
                      |                            |    
  +---------------+   |    +-------------------+   |    +---------------+
  |     users     |---|    |     documents     |---|    | activity_logs |
  +---------------+        +-------------------+        +---------------+
  | PK | id       |<--+    | PK | id           |        | PK | id       |
  | FK | role_id  |   |    | FK | year_id      |        | FK | user_id  |
  | FK | dept_id  |   |    | FK | dept_id      |        +---------------+
  +---------------+   |    | FK | uploaded_by  |
                      |    +-------------------+
  +-------------------+              ^
  |  document_access  |              |
  +-------------------+              |
  | PK | id           |              |
  | FK | document_id  |--------------+
  | FK | user_id      |
  +-------------------+
2.1 Master Tables
Tabel: roles
Kolom,Tipe Data,Atribut,Keterangan
id,INT,"PK, Auto Increment",ID unik role
role_name,VARCHAR(50),NOT NULL,"Superadmin, Admin, User"
Tabel: departments
Kolom,Tipe Data,Atribut,Keterangan
id,INT,"PK, Auto Increment",ID unik departemen
dept_name,VARCHAR(100),NOT NULL,"Nama departemen (ex: IT, HRD)"
Tabel: years
Kolom,Tipe Data,Atribut,Keterangan
id,INT,"PK, Auto Increment",ID unik master tahun
year_value,INT(4),"NOT NULL, UNIQUE","Nilai tahun (ex: 2025, 2026)"
Tabel: users
Kolom,Tipe Data,Atribut,Keterangan
id,INT,"PK, Auto Increment",ID unik pengguna
username,VARCHAR(50),"NOT NULL, UNIQUE",Kredensial login
password,VARCHAR(255),NOT NULL,Hash password (bcrypt)
role_id,INT,FK -> roles.id,Penentu hak akses level
dept_id,INT,FK -> departments.id,Afiliasi unit kerja
2.2 Transaction & Audit Tables
Tabel: documents
Kolom,Tipe Data,Atribut,Keterangan
id,INT,"PK, Auto Increment",ID unik dokumen
doc_number,VARCHAR(100),"NOT NULL, UNIQUE",Nomor resmi dokumen perusahaan
title,VARCHAR(255),NOT NULL,Judul arsip dokumen
file_path,VARCHAR(255),NOT NULL,Path absolut berkas di server
is_public,TINYINT(1),DEFAULT 0,"1 = Publik lintas dept, 0 = Terkunci"
year_id,INT,FK -> years.id,Parameter filter tahun
month_value,TINYINT,NOT NULL,Angka bulan 1-12
dept_id,INT,FK -> departments.id,Departemen pemilik asal
uploaded_by,INT,FK -> users.id,Aktor pengunggah
created_at,TIMESTAMP,DEFAULT CURRENT_TIMESTAMP,Penanda waktu buat
updated_at,TIMESTAMP,DEFAULT NULL ON UPDATE,Penanda waktu ubah
Tabel: document_access
Kolom,Tipe Data,Atribut,Keterangan
id,INT,"PK, Auto Increment",ID unik pemetaan
document_id,INT,FK -> documents.id,"ID berkas terkait (Private)"
user_id,INT,FK -> users.id,"Pengguna yang diberi izin akses khusus"
Tabel: activity_logs
Kolom,Tipe Data,Atribut,Keterangan
id,BIGINT,"PK, Auto Increment",ID log
user_id,INT,FK -> users.id,Pelaku perubahan
action_type,VARCHAR(10),NOT NULL,"CREATE, UPDATE, atau DELETE"
module,VARCHAR(50),NOT NULL,Entitas yang diubah
description,TEXT,NOT NULL,Deskripsi naratif operasi
created_at,TIMESTAMP,DEFAULT CURRENT_TIMESTAMP,Waktu kejadian

2.3 User Interface Layer
Antarmuka disajikan dalam dua persona utama (Superadmin/Admin dan User) untuk mengoptimalkan pengalaman pengguna (UX) sesuai hak akses yang dimiliki.Desain Layout Struktur:Navigasi Dashboard: Sidebar vertikal (kiri) yang menampilkan menu dinamis berupa ikon dan label teks. Menu hanya menampilkan modul yang sesuai dengan hak akses (misal: Role & User hanya muncul untuk Superadmin).Visualisasi Data: Penggunaan komponen Graph (Bar Chart & Line Chart) pada halaman Dashboard untuk visualisasi tren dokumen dan distribusi departemen yang responsif terhadap ukuran layar.
Keluarga Huruf & Palet Warna:Typography: Menggunakan sans-serif modern seperti Roboto atau Open Sans untuk keterbacaan optimal pada layar digital.Palet Warna: Mengadopsi skema warna profesional dengan latar belakang abu-abu muda (bg-gray-50), teks hitam solid (text-gray-900), aksen hijau mint (border-emerald-500), dan highlight biru untuk elemen interaktif (border-blue-500).

2.4 Implementation Design (Technical Blueprint for Development)
Dokumen ini adalah cetak biru teknis yang memandu pengembang dalam membangun Document Control System (DCS) menggunakan teknologi yang telah ditentukan. Desain ini memprioritaskan keamanan (melindungi file PDF dari akses publik), efisiensi loading page, serta penanganan data temporal yang akurat.

2.5 Directory Structure Blueprint
/document-control-system
│
├── /config
│   └── database.php          # Koneksi PDO Database
│
├── /core
│   ├── auth_middleware.php   # Pemeriksaan sesi & batasan role
│   └── logger.php            # Helper otomatis penulisan log MySQL
│
├── /storage
│   └── /secure_docs          # Penyimpanan PDF (Proteksi via .htaccess)
│
├── /public
│   ├── /assets
│   │   ├── /css              # Lembar gaya tata letak grid & flex
│   │   └── /js               # Koding visualisasi & integrasi viewer
│   ├── index.php             # Gerbang login utama
│   └── dashboard.php         # Halaman utama & modul grafik
│
└── /modules
    ├── /document
    │   ├── upload_proc.php   # Backend pemrosesan & validasi file PDF
    │   ├── view_stream.php   # Streamer file aman (anti-direct link)
    │   └── access_control.php# Pengaturan checkbox hak akses per user
    └── /logs
        └── log_view.php      # Tabel data log (Superadmin & Admin)

2.6 Core Code Boilerplates
2.6.1 Proteksi Direktori Penyimpanan (/storage/secure_docs/.htaccess)
Konfigurasi berikut diletakkan di dalam folder penyimpanan dokumen agar berkas tidak bisa ditembak langsung via URL:

Code
Apache Configuration (.htaccess)

# Mencegah akses langsung ke direktori storage
Options -Indexes

# Memblokir semua request langsung ke file PDF di folder ini
<FilesMatch "\.(pdf)$">
    Order allow,deny
    Deny from all
</FilesMatch>

2.6.2 Helper Perekaman Log Modifikasi Data (/core/logger.php)
Fungsi global ini dipanggil di setiap blok kode pengolahan data pasca kueri utama sukses berjalan:

<?php
function write_audit_log($pdo, $user_id, $action_type, $module, $description) {
    // Memastikan hanya mencatat modifikasi data (CREATE, UPDATE, DELETE)
    $allowed_actions = ['CREATE', 'UPDATE', 'DELETE'];
    if (!in_array(strtoupper($action_type), $allowed_actions)) {
        return false;
    }

    $sql = "INSERT INTO activity_logs (user_id, action_type, module, description) 
            VALUES (:user_id, :action_type, :module, :description)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':user_id' => $user_id,
        ':action_type' => strtoupper($action_type),
        ':module' => $module,
        ':description' => $description
    ]);
}
?>

2.6.3 Validasi Unggah Berkas Ketat & Aktivitas Instan (/modules/document/upload_proc.php)
Proses validasi ekstensi di tingkat server untuk memastikan berkas yang masuk murni PDF:
<?php
require_once '../../config/database.php';
require_once '../../core/logger.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $doc_number = $_POST['doc_number'];
    $title = $_POST['title'];
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $year_id = $_POST['year_id'];
    $month_value = $_POST['month_value'];
    $dept_id = $_SESSION['dept_id']; // Otomatis sesuai departemen admin pengunggah
    $uploaded_by = $_SESSION['user_id'];

    // Pengecekan Berkas Berbasis File Mime Type
    $file = $_FILES['pdf_file'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        die("Eksekusi Ditolak: Sistem hanya menerima format berkas PDF standar.");
    }

    // Pembuatan nama file unik terenkripsi
    $filename = uniqid('DOC_', true) . '.pdf';
    $target_path = '../../storage/secure_docs/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Dokumen langsung disimpan dengan status aktif tanpa alur approval
        $sql = "INSERT INTO documents (doc_number, title, file_path, is_public, year_id, month_value, dept_id, uploaded_by) 
                VALUES (:doc_number, :title, :file_path, :is_public, :year_id, :month_value, :dept_id, :uploaded_by)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':doc_number' => $doc_number,
            ':title' => $title,
            ':file_path' => $filename,
            ':is_public' => $is_public,
            ':year_id' => $year_id,
            ':month_value' => $month_value,
            ':dept_id' => $dept_id,
            ':uploaded_by' => $uploaded_by
        ]);

        $new_doc_id = $pdo->lastInsertId();

        // Tulis ke Log Audit Trail
        write_audit_log($pdo, $uploaded_by, 'CREATE', 'Dokumen', "Mengunggah dokumen baru bernomor: $doc_number");

        // Jika bersifat private, arahkan ke modul penentuan akses user spesifik
        if ($is_public === 0) {
            header("Location: access_control.php?doc_id=" . $new_doc_id);
            exit;
        }

        header("Location: ../../public/dashboard.php?status=success");
        exit;
    }
}
?>

2.6.4 Jembatan Pemutar Berkas Aman (/modules/document/view_stream.php)
Berkas dibaca menggunakan perantara skrip PHP untuk memvalidasi hak sesi pengguna sebelum menampilkan data biner berkas PDF:

<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['file_id'])) {
    die("Akses Ditolak.");
}

$file_id = $_GET['file_id'];
$user_id = $_SESSION['user_id'];
$dept_id = $_SESSION['dept_id'];
$role_name = $_SESSION['role_name'];

// Kueri pengecekan hak baca dari database
$sql = "SELECT file_path, is_public, uploaded_by FROM documents WHERE id = :file_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':file_id' => $file_id]);
$doc = $stmt->fetch();

if (!$doc) {
    die("Berkas tidak ditemukan.");
}

$has_access = false;
if ($doc['is_public'] == 1 || $doc['uploaded_by'] == $user_id || $role_name === 'Superadmin') {
    $has_access = true;
} else {
    // Cek izin eksplisit pada tabel pivot private access
    $sql_check = "SELECT id FROM document_access WHERE document_id = :file_id AND user_id = :user_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':file_id' => $file_id, ':user_id' => $user_id]);
    if ($stmt_check->fetch()) {
        $has_access = true;
    }
}

if (!$has_access) {
    die("Anda tidak memiliki izin akses untuk melihat dokumen private ini.");
}

$full_path = '../../storage/secure_docs/' . $doc['file_path'];
if (file_exists($full_path)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="document.pdf"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    @readfile($full_path);
}
?>

2.6.5 Integrasi Penampil PDF Halaman Klien (/public/assets/js/viewer.js)
Menggunakan penampil berbasis objek HTML untuk merender berkas secara mulus langsung pada kontainer modal template dashboard tanpa memicu unduhan lokal:
function openDocumentViewer(fileId) {
    const modal = document.getElementById('pdfViewerModal');
    const iframeContainer = document.getElementById('pdfFrameContainer');
    
    // Memanggil jembatan streamer PHP, bukan menunjuk ke berkas fisik secara langsung
    const streamUrl = `../modules/document/view_stream.php?file_id=${fileId}`;
    
    // Render PDF menggunakan interaksi objek browser standar secara inline
    iframeContainer.innerHTML = `<object data="${streamUrl}" type="application/pdf" class="w-full h-full border-0">
        <p>Browser Anda tidak mendukung penampil PDF internal. Silakan hubungi admin.</p>
    </object>`;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDocumentViewer() {
    const modal = document.getElementById('pdfViewerModal');
    const iframeContainer = document.getElementById('pdfFrameContainer');
    iframeContainer.innerHTML = '';
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

