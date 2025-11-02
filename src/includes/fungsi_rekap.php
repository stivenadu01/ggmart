<?php

// === FUNGSI REKAP TAMBAHAN ===========================
function getRekapPerHari($start, $end, $metode = null)
{
  global $conn;
  $data = [];
  $tanggal_list = getTanggalRange($start, $end);

  foreach ($tanggal_list as $tgl) {
    $filter_metode = $metode ? "AND metode_bayar = '$metode'" : "";

    // Total pokok dan jual per tanggal
    $trx_tgl = $conn->query("
      SELECT
      COUNT(*) as total_transaksi,
      SUM(total_pokok) as pokok,
      SUM(total_harga) as jual
      FROM transaksi
      WHERE DATE(tanggal_transaksi) = '$tgl' $filter_metode
    ")->fetch_assoc();

    // Total produk terjual per tanggal
    $trx_jumlah = $conn->query("
      SELECT
      SUM(dt.jumlah) as total_produk
      FROM transaksi t
      LEFT JOIN detail_transaksi dt
      ON t.kode_transaksi = dt.kode_transaksi
      WHERE DATE(t.tanggal_transaksi) = '$tgl' $filter_metode
    ")->fetch_assoc();

    $pokok = (float)($trx_tgl['pokok'] ?? 0);
    $jual = (float)($trx_tgl['jual'] ?? 0);
    $laba = $jual - $pokok;

    $data[] = [
      'tanggal' => $tgl,
      'transaksi' => (int)($trx_tgl['total_transaksi'] ?? 0),
      'produk' => (int)($trx_jumlah['total_produk'] ?? 0),
      'pokok' => $pokok,
      'jual' => $jual,
      'laba' => $laba
    ];
  }
  return $data;
}

function getRekapPerBulan($tahun, $metode = null)
{
  global $conn;
  $data = [];

  for ($i = 1; $i <= 12; $i++) {
    $start = "$tahun-" . str_pad($i, 2, '0', STR_PAD_LEFT) . "-01";
    $end = date('Y-m-t', strtotime($start));

    $filter_metode = $metode ? "AND metode_bayar = '$metode'" : "";

    $trx_bulan = $conn->query("
      SELECT
      COUNT(*) as total_transaksi,
      SUM(total_pokok) as pokok,
      SUM(total_harga) as jual
      FROM transaksi
      WHERE DATE(tanggal_transaksi) BETWEEN '$start' AND '$end'
      $filter_metode
    ")->fetch_assoc();

    // Total produk terjual per tanggal
    $trx_jumlah = $conn->query("
      SELECT
      SUM(dt.jumlah) as total_produk
      FROM transaksi t
      LEFT JOIN detail_transaksi dt
      ON t.kode_transaksi = dt.kode_transaksi
      WHERE DATE(t.tanggal_transaksi) BETWEEN '$start' AND '$end'
      $filter_metode
    ")->fetch_assoc();
    $pokok = (float)($trx_bulan['pokok'] ?? 0);
    $jual = (float)($trx_bulan['jual'] ?? 0);
    $laba = $jual - $pokok;

    $data[] = [
      'bulan' => date('F', mktime(0, 0, 0, $i, 10)),
      'transaksi' => (int)($trx_bulan['total_transaksi'] ?? 0),
      'produk' => (int)($trx_jumlah['total_produk'] ?? 0),
      'pokok' => $pokok,
      'jual' => $jual,
      'laba' => $laba
    ];
  }

  return $data;
}



function getTanggalRange($start, $end)
{
  $dates = [];
  $current = strtotime($start);
  $last = strtotime($end);
  while ($current <= $last) {
    $dates[] = date('Y-m-d', $current);
    $current = strtotime("+1 day", $current);
  }
  return $dates;
}
