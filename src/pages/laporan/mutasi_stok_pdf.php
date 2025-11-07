<?php
page_require(['admin', 'manager']);
require_once INCLUDES_PATH . '/fungsi_rekap.php';
models('Produk');

$kode_produk = $_GET['produk'] ?? '';
$tglMulai = $_GET['mulai'] ?? '';
$tglSelesai = $_GET['selesai'] ?? '';

$produk = findProduk($kode_produk) ?? '';
$lh = 6; // line height
$fs = 8; // font size
$font = 'Arial';



$title = "Laporan Mutasi Stok Produk GGMART";
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetTitle($title);
$pdf->AddPage();
$pdf->SetFont($font, 'B', $fs + 2);
$pdf->Cell(0, $lh, $title, 0, 1, 'C');
$pdf->SetFont($font, '', $fs + 1);

if ($produk) {
  $pdf->Cell(0, $lh, "Kode Produk: " . $produk['kode_produk'], 0, 1, 'C');
  $pdf->Cell(0, $lh, "Nama Produk: " . $produk['nama_produk'] . " (" . ucfirst($produk['satuan_dasar']) . ")", 0, 1, 'C');
}

$periode = 'Awal s/d ' . date_indo('d/m/Y');
if ($tglMulai && $tglSelesai) {
  $periode = date_indo('d/m/Y', $tglMulai) . " s/d " . date_indo('d/m/Y', $tglSelesai);
} elseif ($tglMulai) {
  $periode = date_indo('d/m/Y', $tglMulai) . "s/d " . date_indo('d/m/Y');
} elseif ($tglSelesai) {
  $periode = "Awal s/d " . date_indo('d/m/Y', $tglSelesai);
}
$pdf->Cell(0, $lh, "Periode: " . $periode, 0, 1, 'C');
$pdf->Cell(0, $lh, "Dicetak Pada: " . date_indo('l, j F Y'), 0, 1, 'C');
$pdf->Ln();



// Ambil data
$data = getLaporanStokProduk($kode_produk, $tglMulai, $tglSelesai);

if ($produk) {
  // Header
  $pdf->SetFont($font, 'B', $fs);
  $pdf->Cell(30, $lh, 'Tanggal', 1, 0, 'C');
  $pdf->Cell(20, $lh, 'Jenis', 1, 0, 'C');
  $pdf->Cell(20, $lh, 'Jumlah', 1, 0, 'C');
  $pdf->Cell(30, $lh, 'Harga Pokok (Rp)', 1, 0, 'C');
  $pdf->Cell(30, $lh, 'Total Pokok (Rp)', 1, 0, 'C');
  $pdf->Cell(60, $lh, 'Keterangan', 1, 1, 'C');
  $pdf->SetFont($font, '', $fs);

  $rp_masuk = $jumlah_masuk = $rp_keluar = $jumlah_keluar = 0;
  foreach ($data as $r) {
    $pdf->Cell(30, $lh, date_indo('d/m/Y', $r['tanggal']), 1);
    $pdf->Cell(20, $lh, ucfirst($r['type']), 1, 0, 'C');
    $pdf->Cell(20, $lh, $r['jumlah'], 1, 0, 'C');
    $pdf->Cell(30, $lh, nf($r['harga_pokok']), 1, 0, 'R');
    $pdf->Cell(30, $lh, nf($r['total_pokok']), 1, 0, 'R');
    $pdf->Cell(60, $lh, $r['keterangan'], 1, 1, 'L');

    if ($r['type'] == 'masuk') {
      $jumlah_masuk += $r['jumlah'];
      $rp_masuk += $r['total_pokok'];
    } elseif ($r['type'] == 'keluar') {
      $jumlah_keluar += $r['jumlah'];
      $rp_keluar += $r['total_pokok'];
    }
  }
  // Total
  $pdf->Ln();
  $pdf->SetFont($font, 'B', $fs + 0.5);
  $pdf->Cell(190, $lh, "Total Masuk: " . $jumlah_masuk . ' ' . $produk['satuan_dasar'] . " (Rp " . nf($rp_masuk) . ")", 0, 1, 'L');
  $pdf->Cell(190, $lh, "Total Keluar: " . $jumlah_keluar . ' ' . $produk['satuan_dasar'] . " (Rp " . nf($rp_keluar) . ")", 0, 1, 'L');
} else {
  // Header
  $pdf->SetFont($font, 'B', $fs);
  $pdf->Cell(20, $lh, 'Tanggal', 1, 0, 'C');
  $pdf->Cell(40, $lh, 'Produk', 1, 0, 'C');
  $pdf->Cell(15, $lh, 'Jenis', 1, 0, 'C');
  $pdf->Cell(15, $lh, 'Jumlah', 1, 0, 'C');
  $pdf->Cell(20, $lh, 'Satuan', 1, 0, 'C');
  $pdf->Cell(25, $lh, 'Harga Pokok', 1, 0, 'C');
  $pdf->Cell(25, $lh, 'Total Pokok', 1, 0, 'C');
  $pdf->Cell(30, $lh, 'Keterangan', 1, 1, 'C');
  $pdf->SetFont($font, '', $fs);

  foreach ($data as $r) {
    $pdf->Cell(20, $lh, date_indo('d/m/Y', $r['tanggal']), 1);
    $pdf->Cell(40, $lh, $r['nama_produk'], 1);
    $pdf->Cell(15, $lh, ucfirst($r['type']), 1, 0, 'C');
    $pdf->Cell(15, $lh, $r['jumlah'], 1, 0, 'C');
    $pdf->Cell(20, $lh, $r['satuan_dasar'], 1, 0, 'C');
    $pdf->Cell(25, $lh, nf($r['harga_pokok']), 1, 0, 'R');
    $pdf->Cell(25, $lh, nf($r['total_pokok']), 1, 0, 'R');
    $pdf->Cell(30, $lh, $r['keterangan'], 1, 1, 'L');
  }
}


$filename = "Laporan_Mutasi_Stok";
isset($produk['nama_produk']) ? $filename .= '_' . str_replace(' ', '_', $produk['nama_produk']) : '';
$periode ? $filename .= '_' . str_replace('/', '_', $periode) : '';
$filename .= ".pdf";
$pdf->Output('I', $filename);
