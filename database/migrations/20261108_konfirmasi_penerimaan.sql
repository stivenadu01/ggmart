-- Revisi konfirmasi penerimaan pesanan pelanggan
-- Jalankan satu kali pada database GGMart sebelum memasang kode aplikasi.
ALTER TABLE transaksi
  MODIFY COLUMN status ENUM('pending','diproses','dikirim','telah_diserahkan','dibatalkan','selesai') DEFAULT 'pending',
  ADD COLUMN batas_konfirmasi DATETIME DEFAULT NULL AFTER tanggal_selesai;

-- Pesanan lama tidak diubah statusnya. Kolom tenggat hanya diisi saat admin
-- mencatat penyerahan barang setelah migrasi diterapkan.
