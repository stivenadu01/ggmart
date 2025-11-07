<?php


// ============================================
// Fungsi untuk transaksi
function getRekapTransaksiHarian($tanggal, $metode = '')
{
  global $conn;
  $tanggal = $conn->escape_string($tanggal);
  $metode = $conn->escape_string($metode);
  $query = "
        SELECT 
        kode_transaksi,
        tanggal_transaksi,
        metode_bayar
        FROM transaksi
        WHERE DATE(tanggal_transaksi) = DATE(?)
    ";

  if (!empty($metode)) {
    $query .= " AND metode_bayar = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $tanggal, $metode);
  } else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $tanggal);
  }

  $stmt->execute();
  $res = $stmt->get_result();
  $data = [];
  while ($r = $res->fetch_assoc()) {
    $data[] = $r;
  }
  return $data;
}

function getRekapTransaksiBulanan($bulan, $metode = '')
{
  global $conn;
  $bulan = $conn->escape_string($bulan);
  $metode = $conn->escape_string($metode);
  $q = "
        SELECT 
            DATE(t.tanggal_transaksi) AS tanggal,
            COUNT(DISTINCT t.kode_transaksi) AS jumlah_transaksi,
            SUM(d.jumlah) AS total_produk,
            SUM(d.subtotal_pokok) AS total_pokok,
            SUM(d.subtotal) AS total_jual,
            SUM(d.subtotal - d.subtotal_pokok) AS total_laba
        FROM transaksi t
        JOIN detail_transaksi d ON t.kode_transaksi = d.kode_transaksi
        WHERE DATE_FORMAT(t.tanggal_transaksi, '%Y-%m') = '$bulan'
    ";

  if (!empty($metode)) {
    $q .= " AND t.metode_bayar = '$metode'";
  }
  $q .= " GROUP BY DATE(t.tanggal_transaksi)";
  $res = $conn->query($q);
  $data = [];
  while ($r = $res->fetch_assoc()) {
    $data[] = $r;
  }
  return $data;
}

function getRekapTransaksiTahunan($tahun, $metode = '')
{
  global $conn;
  $tahun = $conn->escape_string($tahun);
  $metode = $conn->escape_string($metode);

  $q = "
        SELECT 
            DATE_FORMAT(t.tanggal_transaksi, '%M') AS bulan,
            COUNT(DISTINCT t.kode_transaksi) AS jumlah_transaksi,
            SUM(d.jumlah) AS total_produk,
            SUM(d.subtotal_pokok) AS total_pokok,
            SUM(d.subtotal) AS total_jual,
            SUM(d.subtotal - d.subtotal_pokok) AS total_laba
        FROM transaksi t
        JOIN detail_transaksi d ON t.kode_transaksi = d.kode_transaksi
        WHERE YEAR(t.tanggal_transaksi) = '$tahun'
    ";

  if (!empty($metode)) {
    $q .= " AND t.metode_bayar = '$metode'";
  }

  $q .= " GROUP BY bulan";

  $res = $conn->query($q);
  $data = [];
  while ($r = $res->fetch_assoc()) {
    $data[] = $r;
  }

  return $data;
}


function findDetailTransaksi($kode_transaksi)
{
  global $conn;
  $kode_transaksi = $conn->escape_string($kode_transaksi);
  $res = $conn->query("
    SELECT dt.*, p.nama_produk
    FROM detail_transaksi dt 
    LEFT JOIN produk p ON p.kode_produk = dt.kode_produk  
    WHERE dt.kode_transaksi = '$kode_transaksi'
  ");


  $data = [];
  while ($r = $res->fetch_assoc()) {
    $data[] = $r;
  }
  return $data;
}

// ============================================
// Fungsi untuk mutasi stok
function getLaporanStokProduk($kode_produk = '', $tglMulai = '', $tglSelesai = '')
{
  global $conn;
  $where = [];
  if ($kode_produk) $where[] = "m.kode_produk = '" . $conn->escape_string($kode_produk) . "'";
  if ($tglMulai) $where[] = "DATE(m.tanggal) >= '" . $conn->escape_string($tglMulai) . "'";
  if ($tglSelesai) $where[] = "DATE(m.tanggal) <= '" . $conn->escape_string($tglSelesai) . "'";

  $whereSql = $where ? "WHERE " . implode(' AND ', $where) : '';

  $q = "
        SELECT m.*, p.satuan_dasar
        FROM mutasi_stok m JOIN produk p ON p.kode_produk = m.kode_produk
        $whereSql
        ORDER BY m.tanggal ASC
    ";

  $res = $conn->query($q);
  $data = [];
  while ($r = $res->fetch_assoc()) {
    $data[] = $r;
  }
  return $data;
}

function nf($number)
{
  return number_format($number, 0, ',', '.');
}
