<?php
models('Transaksi');
models('DetailTransaksi');
models('Produk');

include INCLUDES_PATH . "fungsi_rekap.php";


// ================= PARAMETER ===================
$tipe   = $_GET['tipe'] ?? 'harian';
$tanggal = $_GET['tanggal'] ?? null;
$bulan   = $_GET['bulan'] ?? null;
$tahun   = $_GET['tahun'] ?? null;
$metode  = $_GET['metode'] ?? null;
$search  = $_GET['search'] ?? null;

// ================= SETUP PDF ===================
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$rh = 4.5; //row height
$font = 'Arial'; // font
$fs = 7; // font size
$hfs = 8; // header font size

if ($tipe === 'harian' && $tanggal) {
  $periode = date('d F Y', strtotime($tanggal));
} elseif ($tipe === 'bulanan' && $bulan) {
  $periode = date('F Y', strtotime($bulan));
} elseif ($tipe === 'tahunan' && $tahun) {
  $periode = $tahun;
} else {
  $periode = '';
}


// HEADER
$pdf->SetFont($font, 'B', $hfs);
$pdf->Cell(0, $rh, 'Laporan Penjualan GGMART', 0, 1, 'C');
$pdf->SetFont($font, '', $hfs);
$pdf->Cell(0, $rh, 'Dicetak pada: ' . date('l, d F Y'), 0, 1, 'C');
$pdf->Cell(0, $rh, "Periode: " . $periode, 0, 1, 'C');

$pdf->Ln($rh);


// === MODE HARIAN (DETAIL) ============================
if ($tipe === 'harian') {
  [$transaksiList,, $summary] = getTransaksiList(1, 9999, $search, $tanggal, $tanggal, $metode);

  // === HEADER UTAMA ===
  $pdf->SetFont($font, 'B', $fs);
  $pdf->Cell(28, $rh * 2, 'Kode Transaksi', 1, 0, 'C');
  $pdf->Cell(16, $rh * 2, 'Waktu', 1, 0, 'C');
  $pdf->Cell(12, $rh * 2, 'Metode', 1, 0, 'C');
  $x = $pdf->GetX();
  $pdf->Cell(134, $rh, 'Detail Transaksi', 1, 0, 'C');
  $pdf->Ln($rh);
  $pdf->SetX($x);
  $pdf->Cell(39, $rh, 'Produk', 1, 0, 'C');
  $pdf->Cell(7, $rh, 'Jml', 1, 0, 'C');
  $pdf->Cell(16, $rh, 'Pokok (Rp)', 1, 0, 'C');
  $pdf->Cell(17, $rh, 'Jual (Rp)', 1, 0, 'C');
  $pdf->Cell(18, $rh, 'T.Pokok (Rp)', 1, 0, 'C');
  $pdf->Cell(18, $rh, 'T.Jual (Rp)', 1, 0, 'C');
  $pdf->Cell(19, $rh, 'T.Laba (Rp)', 1, 1, 'C');

  // === ISI DATA ===
  $pdf->SetFont($font, '', $fs);
  foreach ($transaksiList as $trx) {
    $detailList = getDetailTransaksi($trx['kode_transaksi']);
    $rowCount = count($detailList);
    $totalHeight = $rh * $rowCount;

    // Posisi awal transaksi
    $startY = $pdf->GetY();
    $startX = 10; // margin kiri default FPDF
    $xRight = $startX + 28 + 16 + 12; // posisi mulai kolom "produk"

    // Kolom kiri (merge)
    $pdf->MultiCell(28, $totalHeight, $trx['kode_transaksi'], 1, 'C');
    $pdf->SetXY($startX + 28, $startY);
    $pdf->MultiCell(16, $totalHeight, date('H:i:s', strtotime($trx['tanggal_transaksi'])), 1, 'C');
    $pdf->SetXY($startX + 44, $startY);
    $pdf->MultiCell(12, $totalHeight, ucfirst($trx['metode_bayar']), 1, 'C');

    // Kembali ke kolom kanan
    $pdf->SetXY($xRight, $startY);

    // Loop detail produk
    foreach ($detailList as $d) {
      $pokok = (float)($d['harga_pokok'] ?? 0);
      $satuan = (float)($d['harga_satuan'] ?? 0);
      $sub_pokok = (float)($d['subtotal_pokok'] ?? 0);
      $subtotal = (float)($d['subtotal'] ?? 0);
      $laba = $subtotal - $sub_pokok;

      // Cetak kolom kanan
      $pdf->Cell(39, $rh, $d['nama_produk'], 1);
      $pdf->Cell(7, $rh, $d['jumlah'], 1, 0, 'C');
      $pdf->Cell(16, $rh,  number_format($pokok, 0, ',', '.'), 1, 0, 'R');
      $pdf->Cell(17, $rh,  number_format($satuan, 0, ',', '.'), 1, 0, 'R');
      $pdf->Cell(18, $rh, number_format($sub_pokok, 0, ',', '.'), 1, 0, 'R');
      $pdf->Cell(18, $rh, number_format($subtotal, 0, ',', '.'), 1, 0, 'R');
      $pdf->Cell(19, $rh, number_format($laba, 0, ',', '.'), 1, 1, 'R');

      // Set posisi X kembali ke kolom kanan
      if ($d !== end($detailList)) {
        $pdf->SetX($xRight);
      }
    }
  }

  // === TOTAL ===
  $pdf->SetFont($font, 'B', $fs);
  $pdf->Cell(135, $rh, 'TOTAL', 1, 0, 'L');
  $pdf->Cell(18, $rh, 'Rp ' . number_format((float)($summary['pokok'] ?? 0), 0, ',', '.'), 1, 0, 'R');
  $pdf->Cell(18, $rh, 'Rp ' . number_format((float)($summary['jual'] ?? 0), 0, ',', '.'), 1, 0, 'R');
  $pdf->Cell(19, $rh, 'Rp ' . number_format((float)($summary['laba'] ?? 0), 0, ',', '.'), 1, 1, 'R');
}



// === MODE BULANAN ====================================
elseif ($tipe === 'bulanan') {
  $start = date('Y-m', strtotime($bulan)) . '-01';
  $end   = date('Y-m-t', strtotime($start));
  $rekap = getRekapPerHari($start, $end, $metode);

  $pdf->SetFont($font, 'B', $fs);
  $pdf->Cell(30, $rh, 'Tanggal', 1, 0, 'C');
  $pdf->Cell(25, $rh, 'Transaksi', 1, 0, 'C');
  $pdf->Cell(30, $rh, 'Produk Terjual (Rp)', 1, 0, 'C');
  $pdf->Cell(30, $rh, 'Total Pokok (Rp)', 1, 0, 'C');
  $pdf->Cell(35, $rh, 'Total Penjualan (Rp)', 1, 0, 'C');
  $pdf->Cell(30, $rh, 'Total Laba (Rp)', 1, 1, 'C');

  $pdf->SetFont($font, '', $fs);
  $totalTrx = $totalProduk = $totalPokok = $totalJual = $totalLaba = 0;

  foreach ($rekap as $r) {
    $pdf->Cell(30, $rh, date('d/m/Y', strtotime($r['tanggal'])), 1);
    $pdf->Cell(25, $rh, $r['transaksi'], 1, 0, 'C');
    $pdf->Cell(30, $rh, $r['produk'], 1, 0, 'C');
    $pdf->Cell(30, $rh, number_format($r['pokok'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(35, $rh, number_format($r['jual'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(30, $rh, number_format($r['laba'], 0, ',', '.'), 1, 1, 'R');

    $totalTrx += $r['transaksi'];
    $totalProduk += $r['produk'];
    $totalPokok += $r['pokok'];
    $totalJual  += $r['jual'];
    $totalLaba  += $r['laba'];
  }

  // TOTAL BAWAH
  $pdf->SetFont($font, 'B', $fs);
  $pdf->Cell(30, $rh, 'TOTAL', 1);
  $pdf->Cell(25, $rh, $totalTrx, 1, 0, 'C');
  $pdf->Cell(30, $rh, $totalProduk, 1, 0, 'C');
  $pdf->Cell(30, $rh, 'Rp ' . number_format($totalPokok, 0, ',', '.'), 1, 0, 'R');
  $pdf->Cell(35, $rh, 'Rp ' . number_format($totalJual, 0, ',', '.'), 1, 0, 'R');
  $pdf->Cell(30, $rh, 'Rp ' . number_format($totalLaba, 0, ',', '.'), 1, 1, 'R');
}

// === MODE TAHUNAN (PERBAIKAN: TAMBAH TRANSAKSI & PRODUK) =====================
elseif ($tipe === 'tahunan') {
  $rekap = getRekapPerBulan($tahun, $metode);

  $pdf->SetFont($font, 'B', $fs);
  $pdf->Cell(30, $rh, 'Bulan', 1, 0, 'C');
  $pdf->Cell(25, $rh, 'Transaksi', 1, 0, 'C');
  $pdf->Cell(30, $rh, 'Produk Terjual (Rp)', 1, 0, 'C');
  $pdf->Cell(30, $rh, 'Total Pokok (Rp)', 1, 0, 'C');
  $pdf->Cell(35, $rh, 'Total Penjualan (Rp)', 1, 0, 'C');
  $pdf->Cell(30, $rh, 'Total Laba (Rp)', 1, 1, 'C');

  $pdf->SetFont($font, '', $fs);
  $totalTrx = $totalProduk = $totalPokok = $totalJual = $totalLaba = 0;

  foreach ($rekap as $r) {
    $pdf->Cell(30, $rh, ucfirst($r['bulan']), 1);
    $pdf->Cell(25, $rh, $r['transaksi'], 1, 0, 'C');
    $pdf->Cell(30, $rh, $r['produk'], 1, 0, 'C');
    $pdf->Cell(30, $rh, number_format($r['pokok'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(35, $rh, number_format($r['jual'], 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(30, $rh, number_format($r['laba'], 0, ',', '.'), 1, 1, 'R');

    $totalTrx += $r['transaksi'];
    $totalProduk += $r['produk'];
    $totalPokok += $r['pokok'];
    $totalJual  += $r['jual'];
    $totalLaba  += $r['laba'];
  }

  // TOTAL AKHIR
  $pdf->SetFont($font, 'B', $fs);
  $pdf->Cell(30, $rh, 'TOTAL', 1);
  $pdf->Cell(25, $rh, $totalTrx, 1, 0, 'C');
  $pdf->Cell(30, $rh, $totalProduk, 1, 0, 'C');
  $pdf->Cell(30, $rh, 'Rp ' . number_format($totalPokok, 0, ',', '.'), 1, 0, 'R');
  $pdf->Cell(35, $rh, 'Rp ' . number_format($totalJual, 0, ',', '.'), 1, 0, 'R');
  $pdf->Cell(30, $rh, 'Rp ' . number_format($totalLaba, 0, ',', '.'), 1, 1, 'R');
}

$pdf->Output('I', "Laporan_Penjualan_{$tipe}_GGMART_" . str_replace(' ', '_', $periode) . ".pdf");
