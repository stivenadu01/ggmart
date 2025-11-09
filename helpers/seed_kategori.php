<?php
// Seeder kategori GG-Mart
require_once __DIR__ . '/../config/bootstrap.php';

$kategori = [
  ['nama_kategori' => 'Bahan Pokok', 'deskripsi' => 'Berisi berbagai kebutuhan pokok seperti beras, gula, minyak goreng, dan tepung.'],
  ['nama_kategori' => 'Minuman', 'deskripsi' => 'Kategori minuman kemasan dan serbuk, termasuk air mineral, kopi, teh, dan jus.'],
  ['nama_kategori' => 'Snack Dan Cemilan', 'deskripsi' => 'Aneka snack, keripik, biskuit, dan makanan ringan khas daerah.'],
  ['nama_kategori' => 'Bumbu Dapur', 'deskripsi' => 'Berbagai rempah dan bumbu masakan seperti garam, merica, bawang kering, dan saus.'],
  ['nama_kategori' => 'Kebutuhan Rumah Tangga', 'deskripsi' => 'Barang rumah tangga seperti sabun, detergen, tisu, dan perlengkapan kebersihan.'],
  ['nama_kategori' => 'Sayur dan Buah', 'deskripsi' => 'Produk segar dari petani lokal, seperti sayuran, buah-buahan, dan hasil kebun.'],
  ['nama_kategori' => 'Daging dan Ikan', 'deskripsi' => 'Daging segar, ikan, dan produk olahan seperti sosis atau nugget.'],
  ['nama_kategori' => 'Produk Susu dan Olahan', 'deskripsi' => 'Susu, keju, yogurt, telur ayam kampung, dan olahan lainya.'],
  ['nama_kategori' => 'Roti dan Kue', 'deskripsi' => 'Aneka roti, kue basah, kue kering, dan hasil produksi UMKM gereja.'],
  ['nama_kategori' => 'Kerajinan & Tenun', 'deskripsi' => 'Kain tenun, anyaman, dan berbagai hasil kerajinan tangan jemaat atau masyarakat lokal.']
];

try {
  $inserted = 0;
  foreach ($kategori as $k) {
    // Cek apakah kategori sudah ada berdasarkan nama
    $check = $conn->prepare("SELECT COUNT(*) FROM kategori WHERE nama_kategori = ?");
    $check->bind_param("s", $k['nama_kategori']);
    $check->execute();
    $check->bind_result($exists);
    $check->fetch();
    $check->close();

    if ($exists == 0) {
      $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
      $stmt->bind_param("ss", $k['nama_kategori'], $k['deskripsi']);
      $stmt->execute();
      $stmt->close();
      $inserted++;
    }
  }

  echo "✅ Seeder kategori selesai. ($inserted kategori baru ditambahkan)\n";
} catch (Exception $e) {
  echo "❌ Gagal menyimpan data kategori: " . $e->getMessage() . "\n";
}
