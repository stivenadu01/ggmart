# GGmart

PHP 8.2 atau lebih baru, MySQL/MariaDB, dan ekstensi `gd`, `zip`, `mysqli`, `curl`, `fileinfo`, `mbstring`, serta XML diperlukan. Gunakan PHP yang sama dengan Apache ketika menjalankan Composer.

## Pembayaran dan pengiriman

Pengaturan admin terdiri dari tab **Akun**, **Pembayaran**, dan **Pengiriman**. Rekening dan tarif disimpan terpisah. Tarif berupa rupiah bulat; format desimal dari database tetap diterima jika pecahannya nol. Pelanggan memeriksa keranjang dahulu, lalu menekan **Pesan Sekarang** untuk memilih pembayaran dan pengiriman. Untuk pengiriman lokal, pilih kecamatan sebelum kelurahan. Ongkir dan data penerima disimpan pada pesanan; perubahan tarif tidak mengubah pesanan lama.

Wilayah dan tarif operasional dibaca dari tabel `zona_pengiriman`. `scripts/data/tarif-awal.php` hanya mengisi tarif awal saat migrasi, sedangkan `scripts/data/kecamatan.php` mengisi pemetaan kecamatan. Pemetaan mengacu pada [Dukcapil Kota Kupang Semester I 2025](https://dukcapil.kupangkota.go.id/wp-content/uploads/dlm_uploads/2025/09/Data-Agregat-Kependudukan-Semester-I-Tahun-2025.pdf) dan BPS Kota Kupang.

Isi `.env` dengan Server Key akun Midtrans:

```dotenv
MIDTRANS_SERVER_KEY=isi_server_key_anda
MIDTRANS_IS_PRODUCTION=false
PAYMENT_EXPIRY_MINUTES=120
```

Jangan memasukkan Server Key ke JavaScript, Git, atau formulir pelanggan. Untuk Sandbox, simulasikan pembayaran melalui simulator resmi Midtrans. Atur Payment Notification URL pada dashboard Midtrans ke `https://alamat-publik/ggmart/api/pembayaran/notifikasi` (sesuaikan prefix jika tunnel menunjuk langsung ke GGmart). Notifikasi masuk ke localhost membutuhkan tunnel atau hosting yang dapat diakses internet. Halaman QRIS membuat kode otomatis dan memeriksa Get Status setiap 5 detik; pemeriksaan tetap bekerja tanpa webhook masuk. Jika jaringan gagal, pemeriksaan dicoba kembali setelah 15 detik. Integrasi menggunakan Core API QRIS: https://docs.midtrans.com/reference/qris dan https://docs.midtrans.com/docs/https-notification-webhooks.

Transfer manual diperiksa admin berdasarkan bukti dan uang yang masuk ke rekening. Status lunas dipisahkan dari status pesanan. Barang dianggap selesai setelah diterima pelanggan. Pembatalan transaksi lunas memerlukan pengembalian dana melalui kanal asal, lalu dicatat admin; aplikasi tidak otomatis mengirim refund.

Tab pelanggan: **Semua**, **Menunggu Pembayaran → Menunggu Verifikasi → Diproses → Dikirim → Selesai**, serta **Dibatalkan**. Tahap verifikasi hanya berlaku untuk bukti transfer. Transfer yang diterima admin dan QRIS yang dikonfirmasi Midtrans otomatis masuk Diproses. Admin menandai pesanan pengiriman sebagai Dikirim sebelum Selesai. Pesanan ambil di tempat ditandai Siap diambil dalam tab Diproses sebelum Selesai. Pesanan selesai dan dibatalkan tampil di riwayat admin. Halaman Pesanan hanya memuat transaksi online; badge tiap tab menghitung seluruh pesanan sesuai filter.

Tunai tersedia bagi pelanggan yang memilih ambil di tempat. Pesanan langsung Diproses dengan status belum dibayar dan tanpa batas waktu pembayaran prabayar. Admin menandainya Siap diambil. Saat pelanggan mengambil barang, admin/kasir menekan **Terima tunai & serahkan** atau **Terima Tunai & Cetak** setelah uang benar-benar diterima. Penerimaan uang, penyelesaian, dan pencatatan FIFO berlangsung dalam satu transaksi database. Pelanggan dapat membatalkan sendiri sebelum pesanan siap diambil; setelah siap, pembatalan ditangani admin. Tunai untuk pengiriman ditolak server.

Informasi pembayaran tampil sebelum informasi pengambilan/pengiriman. **Riwayat pesanan** mencatat checkout, unggah/tolak/terima bukti, pembayaran, penyiapan, siap diambil, pengiriman, penyelesaian, pembatalan, dan pengembalian dana beserta waktu serta petugas bila tersedia. Pembaruan menyalin riwayat pembayaran lama dan waktu pengiriman/selesai yang diketahui tanpa mengarang waktu tahapan yang belum pernah dicatat. Dropdown form dan filter memakai komponen GGmart yang mendukung keyboard, pencarian untuk daftar panjang, dan pilihan yang bergantung pada data sebelumnya.

Kasir menyediakan tunai, transfer manual, dan QRIS sesuai ketersediaan konfigurasi toko. Tunai langsung selesai; transfer selesai setelah kasir mengunggah bukti dan admin memverifikasi uang masuk; QRIS selesai setelah konfirmasi gateway. **Simpan & Cetak** membawa pilihan cetak sampai pembayaran selesai, membuka struk, lalu kembali ke kasir setelah dialog cetak ditutup atau dibatalkan. **Simpan Saja** pada tunai memperbarui stok tampilan dan mengosongkan keranjang; pada transfer/QRIS membuka pembayaran dan kembali otomatis setelah lunas. Transfer yang masih menunggu admin bisa dilanjutkan dari antrean pembayaran kasir. Badge **Pesanan Masuk** membuka ringkasan pesanan pelanggan dan penerimaan tunai untuk pesanan yang siap diambil. Kasir tidak diberi akses ke HPP pelanggan atau bukti pembayaran milik orang lain.

Struk tersedia di `/struk/{kode}` hanya setelah transaksi lunas dan selesai; dapat dicetak ulang dari detail transaksi atau disimpan sebagai PDF melalui dialog cetak. Struk memuat produk, jumlah, harga, ongkir bila ada, total, dan metode pembayaran tanpa HPP. QRIS online otomatis Diproses setelah bayar, lalu tetap mengikuti penyiapan/pengiriman. Tidak ada tombol Hubungi Pelanggan dalam pembayaran kasir. Mode Sandbox tetap mengikuti `.env`; pembayaran Sandbox menggunakan simulator dan tidak dapat dibayar dengan aplikasi bank biasa.

Stok dicadangkan saat checkout. Pesanan transfer belum dibayar dan QRIS yang belum dibuat kedaluwarsa sesuai batas pembayaran dan cadangannya dilepas saat aplikasi diakses. QRIS yang sudah dibuat menunggu konfirmasi gateway melalui webhook atau tombol pemeriksaan agar pembayaran berhasil yang terlambat diberitahukan tidak dibatalkan keliru. Untuk rekonsiliasi berkala di hosting/Windows Task Scheduler, jalankan `D:\xampp\php\php.exe D:\xampp\htdocs\ggmart\scripts\expire-orders.php` setiap menit. Bukti yang menunggu pemeriksaan tetap mencadangkan stok sampai admin memutuskan. Penjualan memakai FIFO dan mencatat mutasi keluar saat pesanan selesai. Stok produk menampilkan jumlah yang dapat dipesan (stok fisik dikurangi reservasi).

## Riwayat terpadu

Seluruh kejadian disimpan di satu tabel `riwayat_pesanan`. Kolom `kategori` membedakan kejadian pesanan dan pembayaran; `status_bayar` mencatat status pembayaran pada kejadian terkait. Status terbaru tetap berada di `transaksi`. Kunci kejadian mencegah pencatatan ganda dan tetap mengizinkan unggah ulang bukti sebagai kejadian berbeda.

Untuk memperbarui database dengan tabel riwayat pembayaran lama, jalankan `D:/xampp/php/php.exe scripts/unify-history.php` untuk backup dan pemindahan. Setelah pemeriksaan, jalankan `D:/xampp/php/php.exe scripts/unify-history.php --finalize`. Script mencocokkan waktu, catatan, pelaku, status, dan kode pesanan sebelum menghapus tabel lama. Pengulangan migrasi tidak menggandakan catatan. Migrasi instalasi biasa juga menggunakan struktur terpadu ini.

Uji: `D:/xampp/php/php.exe tests/history-integration.php`, `D:/xampp/php/php.exe tests/payment-integration.php`, `D:/xampp/php/php.exe tests/checkout-regression.php`, dan `node tests/stock-pagination-ui.js`. Uji pemindahan data dijalankan saat tabel lama masih tersedia; fixture dihapus atau dibatalkan melalui rollback.

## Notifikasi pesanan

Klik ikon lonceng untuk membuka pemberitahuan dan menandai pesan sebagai dibaca. Tombol **Aktifkan notifikasi browser** meminta izin hanya ketika diklik; pilihan aktif/nonaktif berlaku untuk akun di perangkat tersebut. Notifikasi tetap tersimpan bila izin browser ditolak. Navbar mobile pada halaman sekunder tetap hanya memiliki tombol kembali; lonceng tersedia di navbar utama.

Pelanggan menerima perkembangan pesanan, verifikasi transfer, pembayaran, kesiapan pengambilan, pengiriman, penyelesaian, pembatalan, dan pengembalian dana. Admin menerima pesanan online baru, bukti transfer yang perlu diverifikasi, pembayaran diterima, pembatalan, dan kebutuhan pengembalian dana. Kasir menerima hasil verifikasi/pembayaran transaksi miliknya serta pesanan tunai yang siap diambil. Pimpinan tidak menerima notifikasi operasional. Aksi sendiri tidak menghasilkan popup; pembayaran terkonfirmasi dan otomatis diproses digabung menjadi satu pemberitahuan.

Tabel `notifikasi` mengacu pada `riwayat_pesanan` dan menyimpan penerima serta status dibaca; `notifikasi_kirim` mencegah popup ganda antar-tab pada perangkat sama. Kedua tabel tidak menjadi riwayat bisnis baru. Migrasi `D:/xampp/php/php.exe scripts/notifications.php` membuat backup sebelum memperbarui skema, tanpa mengirim ulang riwayat lama. Instalasi melalui migrasi utama juga membuat struktur notifikasi.

Tahap ini memeriksa perubahan setiap sekitar 10 detik selama GGmart terbuka. Browser dapat menunda tab latar. Setelah seluruh tab GGmart ditutup, pemberitahuan baru tetap berada di pusat notifikasi tetapi tidak menghasilkan popup sampai aplikasi dibuka; Web Push belum dipasang. Popup memerlukan izin dan alamat aman (HTTPS atau localhost). Service worker dipakai untuk menampilkan dan membuka notifikasi, tanpa cache halaman atau data pembayaran.

Polling global memeriksa QRIS yang masih menunggu konfirmasi, maksimal satu pembayaran per permintaan dan sekali per menit per pembayaran, sehingga halaman pembayaran pelanggan tidak harus terbuka. Untuk pemeriksaan saat tidak ada pengguna membuka GGmart, tetap jalankan `scripts/expire-orders.php` setiap menit melalui scheduler atau gunakan webhook Midtrans pada URL publik. Hanya respons gateway yang sah mengubah pembayaran menjadi lunas.

Uji: `D:/xampp/php/php.exe tests/notifications-integration.php` dan `node tests/notifications-ui.js`. Fixture memakai pengguna yang sudah tersedia dan dibersihkan setelah pengujian.

## Instalasi dan pembaruan

```powershell
D:\xampp\php\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install
D:\xampp\php\php.exe scripts/migrate.php
npm run build
```

Migrasi membuat backup database di `storage/backups`. Untuk database baru, impor `data.sql`, atur `.env`, lalu jalankan migrasi. Script `scripts/seed-transactions.php` mengisi 10 transaksi dan 10 mutasi tanpa menambah pengguna. Script tidak menambahkan data lagi bila `storage/seed-receipt.json` sudah tersedia. Data contoh menggunakan database aktif sesuai kebutuhan presentasi; jangan menjalankan seed pada database produksi yang memiliki transaksi nyata.

Untuk memperbarui instalasi yang sudah memiliki tabel pembayaran/pengiriman, jalankan `D:\xampp\php\php.exe scripts/upgrade-checkout.php`. Script membuat backup, menambahkan kecamatan, asal transaksi, dan riwayat pesanan, serta memperluas status pengiriman tanpa mengubah tarif atau menambah data contoh. Pesanan online lama yang sudah lunas dan masih pending dipindah ke Diproses. Script aman dijalankan ulang. Pembaruan ini sudah dijalankan pada workspace ini.

Pimpinan hanya memantau data dan mengekspor laporan. Endpoint ekspor ditolak untuk admin. Pengelolaan produk, kategori, stok, pembayaran, dan tarif dilakukan admin. Laporan penjualan dan laba kotor menggunakan subtotal barang; penerimaan ongkir ditampilkan terpisah.

## Pemeriksaan

```powershell
D:\xampp\php\php.exe tests/order-integration.php
D:\xampp\php\php.exe tests/http-integration.php
D:\xampp\php\php.exe tests/payment-integration.php
D:\xampp\php\php.exe tests/settings-integration.php
D:\xampp\php\php.exe tests/checkout-regression.php
node tests/checkout-ui.js
```

Pengujian memulihkan rekening dan tarif setelah pemeriksaan; data transaksi dan produk sementara dibersihkan tanpa menambah akun. Pemeriksaan FIFO menggunakan rollback. `checkout-regression.php` menguji respons gateway di batas service (termasuk settlement berulang dan expiry), sedangkan `checkout-ui.js` menguji otomatisasi tampilan/redirect menggunakan respons API simulasi. Pengujian tersebut tidak menggantikan pengujian pembayaran dengan simulator Sandbox.

Dengan Server Key Sandbox di `.env`, `D:\xampp\php\php.exe tests/midtrans-integration.php` menguji pembuatan QRIS, gambar, Get Status, dan pembatalan gateway. Pembayaran berhasil diuji melalui [simulator Midtrans](https://simulator.sandbox.midtrans.com/v2/qris/index); simulator dapat mengembalikan galat layanan. Pengujian lokal sementara dibersihkan setelah selesai.

Untuk pengujian interaktif, `tests/midtrans-integration.php --keep --counter` menyiapkan transaksi kasir Sandbox sementara dan menampilkan URL gambar QRIS. Buka halaman pembayaran kode yang tercatat di `storage/qris-test-receipt.json`, lalu pakai URL gambar pada simulator resmi. Setelah simulator menampilkan PAID, halaman kasir mendeteksi lunas dan kembali otomatis. Jalankan `tests/midtrans-integration.php --finish` untuk verifikasi dan pembersihan, atau `--expire` untuk membatalkan pengujian yang tidak dibayar. Pengujian simulator berhasil beserta redirect kasir telah diverifikasi pada pembaruan ini.
