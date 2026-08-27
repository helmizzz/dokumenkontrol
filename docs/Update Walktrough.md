# Update Walkthrough (Fase Pengembangan)

## Fase 1: Restricted Print & Download
- **Log Viewer:** Menambahkan pencatatan log (History Log) untuk admin setiap kali dokumen dibuka. (Sistem akan mencatat siapa yang membuka dokumen dan kapan dokumen tersebut dibuka. Catatan: Untuk menonaktifkan print & download sementara di-skip, cukup fokus pada Log Viewer saja).

## Fase 2: History File Log (Skenario Restore & Backup)
- Membuat tabel `document_revisions` di database.
- Membuat direktori khusus (contoh: `storage/document_revisions/`) agar update dokumen tidak menimpa file fisik yang asli.
- Memanfaatkan kolom `revision_number` yang sudah ada di tabel `documents` untuk mengelola versi dokumen.
- Menampilkan list file dari history documents pada antarmuka, meliputi informasi: kapan dirubah, siapa yang merubah, dan versinya.
- Fitur restore: dapat me-restore file lama sesuai versi yang telah diupdate (menjadikan versi lama sebagai versi aktif).
- Fitur download: admin/user berwenang dapat mendownload file yang sudah di-backup.
- Aturan Dokumen: Dokumen harus memiliki minimal revisi 1, tidak dapat mengupdate dokumen tanpa adanya unggahan revisi baru.

## Fase 3: Watermark Viewer PDF
- Menambahkan watermark pada saat view PDF menggunakan Opsi 1 (CSS Overlay Watermark di atas elemen viewer).

## Fase 4: Modify Access Right
- Pada form Edit Dokumen, Admin dapat memodifikasi *access right* dari file tersebut secara langsung.
- Admin dapat menambahkan/memilih *user spesifik* (di luar departemen asli dokumen) yang diizinkan untuk mengakses dokumen tersebut.
