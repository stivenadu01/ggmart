<?php
page_require(['admin', 'manager']);
require_once INCLUDES_PATH . '/fungsi_rekap.php';

$tipe = $_GET['tipe'] ?? 'harian';
$metode = $_GET['metode'] ?? '';


$lh = 6; //line height
$fs = 8; //font size
$font = 'Arial';

$title = "Laporan Transaksi " . ucfirst($tipe) . " GGMART";
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetTitle($title);
$pdf->AddPage();
$pdf->SetFont($font, 'B', $fs + 2);
$pdf->Cell(0, $lh, $title, 0, 1, 'C');
$pdf->SetFont($font, '', $fs + 1);
$pdf->Cell(0, $lh, "Dicetak Pada: " . date_indo('l, j F Y'), 0, 1, 'C');
if ($metode) {
  $pdf->Cell(0, $lh, "Metode Pembayaran: " . strtoupper($metode), 0, 1, 'C');
}

switch ($tipe) {
  case 'harian':
    $tanggal = $_GET['tanggal'] ?? date('d F Y');
    $data = getRekapTransaksiHarian($tanggal, $metode);
    $periode = date_indo('d F Y', $tanggal);
    $pdf->Cell(0, $lh, "Periode: " . $periode, 0, 1, 'C');
    $pdf->Ln();

    // Header
    $pdf->SetFont($font, 'B', $fs);
    $pdf->Cell(27, $lh * 2, 'Kode Transaksi', 1, 0, 'C');
    $pdf->Cell(13, $lh * 2, 'Waktu', 1, 0, 'C');
    $pdf->Cell(12, $lh * 2, 'Metode', 1, 0, 'C');
    $x = $pdf->GetX();
    $pdf->Cell(138, $lh, 'Detail Transaksi', 1, 0, 'C');
    $pdf->Ln($lh);


    $pdf->SetX($x);
    $pdf->Cell(36, $lh, 'Produk', 1, 0, 'C');
    $pdf->Cell(8, $lh, 'Qty', 1, 0, 'C');
    $pdf->Cell(17, $lh, 'Pokok (Rp)', 1, 0, 'C');
    $pdf->Cell(17, $lh, 'Jual (Rp)', 1, 0, 'C');
    $pdf->Cell(20, $lh, 'T.Pokok (Rp)', 1, 0, 'C');
    $pdf->Cell(20, $lh, 'T.Jual (Rp)', 1, 0, 'C');
    $pdf->Cell(20, $lh, 'T.Laba (Rp)', 1, 1, 'C');
    $pdf->SetFont($font, '', $fs);

    $totalPokok = $totalJual = $totalLaba = 0;
    foreach ($data as $trx) {
      $detail_transaksi = findDetailTransaksi($trx['kode_transaksi']);
      $totalLh = $lh * count($detail_transaksi);

      $pdf->Cell(27, $totalLh, $trx['kode_transaksi'], 1);
      $pdf->Cell(13, $totalLh, date_indo('H:i:s', $trx['tanggal_transaksi']), 1, 0, 'C');
      $pdf->Cell(12, $totalLh, ucfirst($trx['metode_bayar']), 1, 0, 'C');
      $x = $pdf->GetX();
      // detail transaksi
      foreach ($detail_transaksi as $dt) {
        $pdf->SetX($x);
        $pdf->Cell(36, $lh, $dt['nama_produk'], 1, 0, 'L');
        $pdf->Cell(8, $lh, $dt['jumlah'], 1, 0, 'C');
        $pdf->Cell(17, $lh, nf($dt['harga_pokok']), 1, 0, 'R');
        $pdf->Cell(17, $lh, nf($dt['harga_satuan']), 1, 0, 'R');
        $pdf->Cell(20, $lh, nf($dt['subtotal_pokok']), 1, 0, 'R');
        $pdf->Cell(20, $lh, nf($dt['subtotal']), 1, 0, 'R');
        $pdf->Cell(20, $lh, nf($dt['subtotal'] - $dt['subtotal_pokok']), 1, 1, 'R');

        // tambah hitung total
        $totalPokok += $dt['subtotal_pokok'];
        $totalJual += $dt['subtotal'];
        $totalLaba += $dt['subtotal'] - $dt['subtotal_pokok'];
      }
    }

    $pdf->SetFont($font, 'B', $fs + 0.5);
    $pdf->Cell(130, $lh, 'TOTAL', 1);
    $pdf->Cell(20, $lh, 'Rp ' . nf($totalPokok), 1, 0, 'R');
    $pdf->Cell(20, $lh, 'Rp ' . nf($totalJual), 1, 0, 'R');
    $pdf->Cell(20, $lh, 'Rp ' . nf($totalLaba), 1, 1, 'R');
    break;

  case 'bulanan':
    $bulan = $_GET['bulan'] ?? date('Y-m');
    $data = getRekapTransaksiBulanan($bulan, $metode);
    $periode = date_indo('F Y', $bulan);
    $pdf->Cell(0, $lh, "Periode: " . $periode, 0, 1, 'C');
    $pdf->Ln();

    $pdf->SetFont($font, 'B', $fs);
    $pdf->Cell(30, $lh, 'Tanggal', 1, 0, 'C');
    $pdf->Cell(25, $lh, 'Jml Transaksi', 1, 0, 'C');
    $pdf->Cell(33.75, $lh, "Produk Terjual (satuan)", 1, 0, 'C');
    $pdf->Cell(33.75, $lh, 'Total Pokok (Rp)', 1, 0, 'C');
    $pdf->Cell(33.75, $lh, 'Total Jual (Rp)', 1, 0, 'C');
    $pdf->Cell(33.75, $lh, 'Total Laba (Rp)', 1, 0, 'C');
    $pdf->Ln();

    $pdf->SetFont($font, '', $fs);
    $totalTransaksi = $totalProduk = $totalPokok = $totalJual = $totalLaba = 0;
    foreach ($data as $r) {
      $pdf->Cell(30, $lh, date_indo('d/m/Y', $r['tanggal']), 1);
      $pdf->Cell(25, $lh, $r['jumlah_transaksi'], 1, 0, 'C');
      $pdf->Cell(33.75, $lh, $r['total_produk'], 1, 0, 'C');
      $pdf->Cell(33.75, $lh, nf($r['total_pokok']), 1, 0, 'R');
      $pdf->Cell(33.75, $lh, nf($r['total_jual']), 1, 0, 'R');
      $pdf->Cell(33.75, $lh, nf($r['total_laba']), 1, 1, 'R');

      $totalTransaksi += $r['jumlah_transaksi'];
      $totalProduk += $r['total_produk'];
      $totalPokok += $r['total_pokok'];
      $totalJual += $r['total_jual'];
      $totalLaba += $r['total_laba'];
    }

    $pdf->SetFont($font, 'B', $fs + 0.5);
    $pdf->Cell(30, $lh, 'TOTAL', 1);
    $pdf->Cell(25, $lh, $totalTransaksi, 1, 0, 'C');
    $pdf->Cell(33.75, $lh, $totalProduk, 1, 0, 'C');
    $pdf->Cell(33.75, $lh, 'Rp ' . nf($totalPokok), 1, 0, 'R');
    $pdf->Cell(33.75, $lh, 'Rp ' . nf($totalJual), 1, 0, 'R');
    $pdf->Cell(33.75, $lh, 'Rp ' . nf($totalLaba), 1, 1, 'R');
    break;

  case 'tahunan':
    $tahun = $_GET['tahun'] ?? date('Y');
    $data = getRekapTransaksiTahunan($tahun, $metode);
    $periode = $tahun;
    $pdf->Cell(0, $lh, "Periode: Tahun " . $periode, 0, 1, 'C');
    $pdf->Ln();


    $pdf->SetFont($font, 'B', $fs);
    $pdf->Cell(30, $lh, 'Bulan', 1, 0, 'C');
    $pdf->Cell(25, $lh, 'Jml Transaksi', 1, 0, 'C');
    $pdf->Cell(33.75, $lh, "Produk Terjual (satuan)", 1, 0, 'C');
    $pdf->Cell(33.75, $lh, 'Total Pokok (Rp)', 1, 0, 'C');
    $pdf->Cell(33.75, $lh, 'Total Jual (Rp)', 1, 0, 'C');
    $pdf->Cell(33.75, $lh, 'Total Laba (Rp)', 1, 0, 'C');
    $pdf->Ln();

    $pdf->SetFont($font, '', $fs);
    $totalTransaksi = $totalProduk = $totalPokok = $totalJual = $totalLaba = 0;
    foreach ($data as $r) {
      $pdf->Cell(30, $lh, $r['bulan'], 1);
      $pdf->Cell(25, $lh, $r['jumlah_transaksi'], 1, 0, 'C');
      $pdf->Cell(33.75, $lh, $r['total_produk'], 1, 0, 'C');
      $pdf->Cell(33.75, $lh, nf($r['total_pokok']), 1, 0, 'R');
      $pdf->Cell(33.75, $lh, nf($r['total_jual']), 1, 0, 'R');
      $pdf->Cell(33.75, $lh, nf($r['total_laba']), 1, 1, 'R');

      $totalTransaksi += $r['jumlah_transaksi'];
      $totalProduk += $r['total_produk'];
      $totalPokok += $r['total_pokok'];
      $totalJual += $r['total_jual'];
      $totalLaba += $r['total_laba'];
    }

    $pdf->SetFont($font, 'B', $fs + 0.5);
    $pdf->Cell(30, $lh, 'TOTAL', 1);
    $pdf->Cell(25, $lh, $totalTransaksi, 1, 0, 'C');
    $pdf->Cell(33.75, $lh, $totalProduk, 1, 0, 'C');
    $pdf->Cell(33.75, $lh, nf($totalPokok), 1, 0, 'R');
    $pdf->Cell(33.75, $lh, nf($totalJual), 1, 0, 'R');
    $pdf->Cell(33.75, $lh, nf($totalLaba), 1, 1, 'R');
    break;
}

$filename = "Laporan_Transaksi_";
if ($metode) $filename .= ucfirst($metode) . "_";
$filename .= ucfirst($tipe) . "_" . str_replace(' ', '_', $periode) . ".pdf";
$pdf->Output('I', $filename);
