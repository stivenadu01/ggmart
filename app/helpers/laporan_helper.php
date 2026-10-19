<?php

function getRekapTransaksiHarian($tanggal, $metode = '')
{
  $conn = db();
  $sql = "SELECT kode_transaksi, tanggal_transaksi, metode_bayar FROM transaksi WHERE DATE(tanggal_transaksi)=DATE(?) AND status='selesai'";
  if ($metode !== '') {
    $sql .= " AND metode_bayar = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $tanggal, $metode);
  } else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $tanggal);
  }
  $stmt->execute();
  $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $data;
}

function getRekapTransaksiBulanan($bulan, $metode = '')
{
  $conn = db();
  $sql = "SELECT DATE(t.tanggal_transaksi) AS tanggal, COUNT(DISTINCT t.kode_transaksi) AS jumlah_transaksi,
    SUM(d.jumlah) AS total_produk, SUM(d.harga_pokok*d.jumlah) AS total_pokok, SUM(d.harga_satuan*d.jumlah) AS total_jual
    FROM transaksi t JOIN detail_transaksi d ON t.kode_transaksi=d.kode_transaksi
    WHERE DATE_FORMAT(t.tanggal_transaksi,'%Y-%m')=? AND t.status='selesai'";
  if ($metode !== '') {
    $sql .= " AND t.metode_bayar=?";
    $stmt=$conn->prepare($sql." GROUP BY DATE(t.tanggal_transaksi)");
    $stmt->bind_param('ss',$bulan,$metode);
  } else {
    $stmt=$conn->prepare($sql." GROUP BY DATE(t.tanggal_transaksi)");
    $stmt->bind_param('s',$bulan);
  }
  $stmt->execute(); $data=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $data;
}

function getRekapTransaksiTahunan($tahun, $metode = '')
{
  $conn = db();
  $tahun = (int)$tahun;
  $sql = "SELECT DATE_FORMAT(t.tanggal_transaksi,'%M') AS bulan, COUNT(DISTINCT t.kode_transaksi) AS jumlah_transaksi,
    SUM(d.jumlah) AS total_produk, SUM(d.harga_pokok*d.jumlah) AS total_pokok, SUM(d.harga_satuan*d.jumlah) AS total_jual
    FROM transaksi t JOIN detail_transaksi d ON t.kode_transaksi=d.kode_transaksi
    WHERE YEAR(t.tanggal_transaksi)=? AND t.status='selesai'";
  if ($metode !== '') {
    $stmt=$conn->prepare($sql." AND t.metode_bayar=? GROUP BY MONTH(t.tanggal_transaksi), bulan ORDER BY MONTH(t.tanggal_transaksi)");
    $stmt->bind_param('is',$tahun,$metode);
  } else {
    $stmt=$conn->prepare($sql." GROUP BY MONTH(t.tanggal_transaksi), bulan ORDER BY MONTH(t.tanggal_transaksi)");
    $stmt->bind_param('i',$tahun);
  }
  $stmt->execute(); $data=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $data;
}

function findDetailTransaksi($kode_transaksi)
{
  $stmt=db()->prepare("SELECT dt.*, p.nama_produk FROM detail_transaksi dt LEFT JOIN produk p ON p.kode_produk=dt.kode_produk WHERE dt.kode_transaksi=?");
  $stmt->bind_param('s',$kode_transaksi); $stmt->execute(); $data=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $data;
}

function getLaporanStokProduk($kode_produk = '', $tglMulai = '', $tglSelesai = '')
{
  $conn=db(); $where=[]; $params=[]; $types='';
  if ($kode_produk !== '') { $where[]='m.kode_produk=?'; $params[]=$kode_produk; $types.='s'; }
  if ($tglMulai !== '') { $where[]='DATE(m.tanggal)>=?'; $params[]=$tglMulai; $types.='s'; }
  if ($tglSelesai !== '') { $where[]='DATE(m.tanggal)<=?'; $params[]=$tglSelesai; $types.='s'; }
  $whereSql=$where ? 'WHERE '.implode(' AND ',$where) : '';
  $stmt=$conn->prepare("SELECT m.*, p.satuan_dasar, p.nama_produk FROM mutasi_stok m JOIN produk p ON p.kode_produk=m.kode_produk $whereSql ORDER BY m.tanggal ASC");
  if ($params) $stmt->bind_param($types,...$params);
  $stmt->execute(); $data=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $data;
}

function nf($number)
{
  return number_format($number, 0, ',', '.');
}

function date_indo($format = 'j F Y', $tanggal = null)
{
  // Tentukan timestamp
  if (is_null($tanggal)) {
    $timestamp = time(); // default: waktu sekarang
  } elseif (is_numeric($tanggal)) {
    $timestamp = (int)$tanggal; // kalau sudah timestamp
  } elseif (is_string($tanggal)) {
    $timestamp = strtotime($tanggal);
    if ($timestamp === false) $timestamp = time(); // fallback
  } elseif ($tanggal instanceof DateTime) {
    $timestamp = $tanggal->getTimestamp(); // dukung objek DateTime
  } else {
    $timestamp = time();
  }

  // Daftar nama hari dan bulan Indonesia
  $hari_indo = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
  ];

  $bulan_indo = [
    'January' => 'Januari',
    'February' => 'Februari',
    'March' => 'Maret',
    'April' => 'April',
    'May' => 'Mei',
    'June' => 'Juni',
    'July' => 'Juli',
    'August' => 'Agustus',
    'September' => 'September',
    'October' => 'Oktober',
    'November' => 'November',
    'December' => 'Desember'
  ];

  // Format tanggal sesuai input
  $hasil = date($format, $timestamp);

  // Ganti nama hari & bulan Inggris ke Indonesia
  $hasil = strtr($hasil, $hari_indo);
  $hasil = strtr($hasil, $bulan_indo);

  return $hasil;
}
