# Backup dan restore TJKT Check

## Menu admin

Buka **Pemeliharaan > Backup**. Menu menampilkan file ZIP di `storage/app/backups`, waktu file, ukuran, dan hasil pengujian terakhir. Tombol **Buat Backup** mencadangkan database dan unggahan tanpa `.env`. Tombol **Upload Backup** menerima ZIP maksimal 12 MB, memeriksanya, lalu menambahkannya ke daftar tanpa mengganti data aktif. Batas PHP `upload_max_filesize`/`post_max_size` juga harus memadai. Klik **Unduh**, **Uji Restore**, atau **Hasil** pada baris cadangan.

Akses mengikuti akses panel admin. Saat ini aplikasi tidak memiliki pemisahan peran, sehingga semua akun yang bisa masuk panel admin dapat mengakses cadangan, termasuk arsip berisi `.env`. Pengunjung tanpa login ditolak. Unduhan tidak memakai direktori publik.

Status pengujian disimpan di file pendamping `.report.json`. Jika isi ZIP berubah, status sebelumnya tidak berlaku lagi. Pengujian melalui CLI pada folder backup yang sama juga memperbarui status di menu. Arsip besar sebaiknya diuji melalui CLI agar tidak terkena batas waktu request web. Pengujian berulang menyimpan folder hasil baru; pantau ruang disk.

Semua request web memakai kunci file bersama. Backup/restore mengambil kunci eksklusif; jika request lain masih berjalan, operasi ditolak untuk dicoba ulang. Worker, scheduler, proses CLI, dan penulis eksternal harus dihentikan sebelum operasi: mereka tidak otomatis mengikuti kunci request web. Form meminta konfirmasi kondisi ini. Implementasi ditujukan untuk satu server dengan filesystem lokal, bukan banyak server berbagi database.

**Pulihkan Data** tersedia setelah arsip lulus pemeriksaan. Modal menampilkan ringkasan, meminta persetujuan penghentian worker dan mengetik `PULIHKAN`. File diperiksa ulang dan versi migrasi wajib sama. Sistem membuat backup kondisi terbaru terlebih dahulu, memulihkan unggahan dan database, lalu mengeluarkan pengguna agar login menggunakan akun hasil backup. `.env` dalam arsip tidak diterapkan otomatis. Siapkan APP_KEY yang sesuai secara terpisah jika pindah server.

Ekstensi PHP `sqlite3` diperlukan untuk pemulihan penuh. Pemulihan memakai SQLite backup API agar transaksi database dan WAL ditangani SQLite. Folder unggahan harus berada di lokasi standar `storage/app/public` dan `storage/app/private`. Direktori lama dipertahankan di folder saudara bernama `.before-restore-UUID`, bukan di web publik. Tidak ada penghapusan cadangan otomatis.

## Isi backup

- Snapshot database SQLite dari koneksi aplikasi yang sedang dikonfigurasi, termasuk nilai dan riwayat.
- Semua file disk lokal `public` dan `local` (termasuk PDF materi dan file tersembunyi).
- `manifest.json`: waktu backup, jumlah baris per tabel, ukuran dan SHA-256 setiap file.
- `.env` hanya jika memakai `--include-env`. Simpan backup ini sebagai rahasia; ZIP tidak dienkripsi.

Kode aplikasi tetap disimpan melalui Git. Backup ini tidak menyimpan kode, vendor, konfigurasi Nginx/PHP, maupun konfigurasi sistem operasi. Simpan versi commit aplikasi yang sesuai bersama cadangan. APP_KEY dan konfigurasi server juga perlu cadangan aman.

## Debian (root, pengguna PHP www-data)

Jalankan setelah commit/push/pull perubahan kode. Tidak ada migrasi baru.

```bash
cd /var/www/tjkt-check
install -d -o www-data -g www-data -m 700 storage/app/backups storage/app/restore-tests
runuser -u www-data -- php artisan down --retry=60
runuser -u www-data -- php artisan backup:create --include-env
runuser -u www-data -- php artisan up
```

Hentikan worker/scheduler atau penulis database lain sebelum backup, tunggu request yang masih berjalan selesai. Mode pemeliharaan memblokir request web baru, bukan seluruh proses CLI. Aktifkan kembali worker setelah selesai. Jika backup gagal, periksa pesan kesalahan dan tetap jalankan `up` agar aplikasi tidak tertinggal dalam mode pemeliharaan. Jangan gunakan `&&` untuk mengaitkan backup dan `up`.

Perintah menampilkan path ZIP. Gunakan path tersebut untuk pengujian (ganti NAMA-BACKUP):

```bash
runuser -u www-data -- php artisan backup:restore-test /var/www/tjkt-check/storage/app/backups/NAMA-BACKUP.zip
ls -lh storage/app/backups/*.zip
```

## Yang diperiksa saat uji restore

File dipulihkan ke `storage/app/restore-tests/UUID` yang baru. Database aktif dan unggahan asli tidak disentuh. Perintah memeriksa path arsip, ukuran, checksum seluruh file, integritas SQLite, foreign key, tabel wajib, dan jumlah baris. Hasilnya disimpan sebagai `restore-report.json` tanpa isi data pribadi.

Batas ukuran hasil ekstrak 2 GB dan manifest 2 MB. Arsip format lama tanpa manifest ditolak; backup lama tidak dihapus. Folder hasil uji disimpan agar dapat ditinjau. Belum ada penghapusan otomatis atau jadwal backup otomatis.

Checksum mendeteksi kerusakan, bukan membuktikan identitas pengirim. Uji hanya backup dari sumber tepercaya. Salin backup ke tempat aman di luar server; satu salinan pada server yang sama belum melindungi dari kerusakan server.

## Jika pemulihan terputus

Selama penggantian data, aplikasi masuk mode pemeliharaan. Jika operasi selesai atau rollback berhasil, mode ini dilepas otomatis. Jika proses mati/timeout atau rollback unggahan gagal, aplikasi tetap offline. Jangan langsung menjalankan `up`: periksa log, backup pengaman terbaru, folder hasil uji, dan direktori `.before-restore-*` terlebih dahulu. Pemulihan database dan unggahan bukan satu transaksi filesystem yang tahan listrik mati. Untuk arsip besar, jangan menjalankan pemulihan web sebelum memastikan batas waktu PHP/server dan ruang disk cukup.

Perubahan ini tidak menjalankan restore pada database produksi secara otomatis. Restore penuh harus dipilih dan dikonfirmasi admin.

## Lokal Windows

```powershell
php artisan down
php artisan backup:create
php artisan up
php artisan backup:restore-test "C:\path\ke\backup.zip"
```

Perintah backup dan uji restore tidak menghapus data aktif. Tombol **Pulihkan Data** memang mengganti kondisi data aktif dengan kondisi backup yang dipilih.
