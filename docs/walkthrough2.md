# Ringkasan Eksekusi Step 2 (Unggah Dokumen)

Fase "Unggah Dokumen" telah selesai dikerjakan! Pintu masuk utama data sekarang sudah aman dan interaktif, lengkap dengan manipulasi DOM (*Native* JS) serta rekam jejak (*Audit Trail*).

## Perubahan yang Dilakukan
1. **Perekaman Audit Trail (Log)**: Membuat `core/logger.php` sebagai fungsi *helper* `write_audit_log` untuk memonitor aktivitas Create.
2. **UI Interaktif Dua Kolom (`upload.php`)**:
   - Kolom kiri untuk meta data dan *File Dropzone* PDF.
   - Kolom kanan khusus untuk kontrol akses (Public / Private). Daftar centang departemen (*checkboxes*) akan otomatis *slide down* jika "Private" dipilih.
   - Menggunakan JS Native untuk validasi ekstensi (`.pdf`), maksimal ukuran (10 MB), efek *drag & drop*, dan *loading state* pada tombol "Submit".
3. **Prosesor Backend yang Tangguh (`upload_proc.php`)**:
   - Skrip yang menangani pemindahan *file* ke folder rahasia (`storage/secure_docs`).
   - Menyisipkan rekaman aktivitas (*Audit Trail*) secara diam-diam (*pasif*).
   - Penggunaan Transaksi PDO (`beginTransaction` & `commit`) agar database dan *file* tetap sinkron jika salah satunya gagal (misal gagal mencatat hak akses *private*).

## Cara Menguji / Verifikasi Manual

> [!TIP]
> **Langkah Pengujian**
> 1. Klik menu **Unggah Dokumen** di *sidebar* kiri.
> 2. **Uji Validasi Error**: Cobalah *Drag and Drop* sebuah gambar (.png/.jpg) ke dalam kotak unggah, atau unggah file berukuran di atas 10MB. Browser seharusnya akan memunculkan *alert* peringatan penolakan.
> 3. **Uji UI Akses Terbatas (Private)**: Klik tombol *radio* "Private (Akses Terbatas)", maka kotak centang pengguna (*users*) di departemen Anda akan muncul secara dinamis.
> 4. **Uji Simpan Data (Success)**: Isi Nomor Dokumen, Judul, Tahun, dan Bulan. Masukkan sebuah file PDF asli. Klik "Unggah Sekarang".
> 5. Setelah *loading* sebentar, Anda akan otomatis terlempar ke halaman **Repositori Dokumen** dan PDF yang baru saja diunggah akan muncul di baris nomor satu pada tabel!

Setelah Anda mencoba mengunggah PDF, Anda juga bisa mencoba mengklik logo "Mata" (View) di tabel Repositori untuk membuktikan bahwa integrasi dari **Step 1** dan **Step 2** sudah tersambung dengan sempurna (Modal PDF Viewer akan merender file tersebut).

Silakan lakukan pengetesan secara menyeluruh. Jika siap melanjutkan, silakan beri instruksi untuk maju ke **Step 3 (Master Data)**!
