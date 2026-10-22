# Revisi skema transaksi dan pengiriman

## Perubahan
- Data alamat dan status operasional pengiriman dipindahkan ke tabel `pengiriman`.
- `pengiriman.kode_transaksi` unik dan menjadi foreign key ke `transaksi.kode_transaksi`.
- `transaksi` menyimpan data transaksi/pembayaran, termasuk `total_tagihan`.
- `status_bayar` dan `sumber_transaksi` diubah menjadi ENUM; `metode_pengiriman` menjadi ENUM pada tabel `pengiriman`; `siap_diambil` menjadi TINYINT(1).
- Checkout menyimpan transaksi dan pengiriman secara terpisah; pembacaan detail/list menggabungkan data pengiriman.
- Dump `ggmart.sql` diperbarui untuk skema baru dan data contoh dimasukkan ke kedua tabel.

## Database yang sudah terpasang (migrasi)
1. Buat backup penuh database.
2. Uji pada salinan database dahulu.
3. Jalankan `app/database/migrations/20261022_split_transaksi_pengiriman.sql` satu kali pada database lama.
4. Setelah migrasi, deploy file aplikasi dari ZIP ini.
5. Periksa transaksi lama dan baru, checkout ambil/kirim, siap diambil, proses kirim, pembayaran, struk, dan laporan.

Migrasi menyalin atribut pengiriman ke tabel baru sebelum menghapus kolom lama. Jangan jalankan migrasi ini pada database yang sudah memakai skema baru, karena kolom lama sudah tidak ada.

## Catatan tipe data
ENUM `status_bayar` menggunakan nilai: `belum_bayar`, `menunggu_verifikasi`, `lunas`, `ditolak`, `kedaluwarsa`, `perlu_refund`, `dikembalikan`. Pastikan seluruh status yang dipakai oleh instalasi lokal cocok dengan daftar ini sebelum migrasi.
