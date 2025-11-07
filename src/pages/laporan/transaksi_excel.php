<?php
page_require(['admin', 'manager']);
require_once INCLUDES_PATH . '/fungsi_rekap.php';
require INCLUDES_PATH . '/style_excel.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ==== PARAMETER & INISIALISASI ====
$tipe = $_GET['tipe'] ?? 'harian';
$metode = $_GET['metode'] ?? '';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Laporan");

$title = "Laporan Transaksi " . ucfirst($tipe) . " GGMART";

// ==== HEADER LAPORAN ====
$row = 1;
$mergeCol = ($tipe == 'harian') ? 'J' : 'F';
$sheet->setCellValue("A$row", $title)->mergeCells("A$row:$mergeCol$row");
applyStyle($sheet, "A$row", ['bold', 'center']);
$row++;

$sheet->setCellValue("A$row", "Dicetak Pada: " . date_indo('l, j F Y'))->mergeCells("A$row:$mergeCol$row");
applyStyle($sheet, "A$row", ['center']);

if ($metode) {
  $row++;
  $sheet->setCellValue("A$row", "Metode Pembayaran: " . strtoupper($metode))->mergeCells("A$row:$mergeCol$row");
  applyStyle($sheet, "A$row", ['center']);
}
$row++;

// ==== SWITCH PER TIPE ====
switch ($tipe) {

  // ------------------ HARIAN ------------------
  case 'harian':
    $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
    $data = getRekapTransaksiHarian($tanggal, $metode);
    $periode = date_indo('d F Y', $tanggal);

    $sheet->setCellValue("A$row", "Periode: " . $periode)->mergeCells("A$row:J$row");
    applyStyle($sheet, "A$row", ['center']);
    $row += 2;

    // Header utama
    $sheet->setCellValue("A$row", 'Kode Transaksi')->mergeCells("A$row:A" . ($row + 1));
    $sheet->setCellValue("B$row", 'Waktu')->mergeCells("B$row:B" . ($row + 1));
    $sheet->setCellValue("C$row", 'Metode')->mergeCells("C$row:C" . ($row + 1));
    $sheet->setCellValue("D$row", 'Detail Transaksi')->mergeCells("D$row:J$row");
    applyStyle($sheet, "A$row:J$row", ['bold', 'center', 'border']);
    $row++;

    // Subheader
    $sheet->fromArray(['Produk', 'Qty', 'Pokok(Rp)', 'Jual(Rp)', 'T.Pokok(Rp)', 'T.Jual(Rp)', 'T.Laba(Rp)'], null, "D$row");
    applyStyle($sheet, "A$row:J$row", ['bold', 'center', 'border']);
    $row++;

    // Data transaksi
    $totalPokok = $totalJual = $totalLaba = 0;
    foreach ($data as $trx) {
      $details = findDetailTransaksi($trx['kode_transaksi']);
      $totalRow = $row + count($details) - 1;

      // Merge kolom identitas transaksi
      $sheet->setCellValue("A$row", $trx['kode_transaksi'])->mergeCells("A$row:A$totalRow");
      applyStyle($sheet, "A$row", ['border', 'topLeft']);
      $sheet->setCellValue("B$row", date_indo('H:i:s', strtotime($trx['tanggal_transaksi'])))->mergeCells("B$row:B$totalRow");
      $sheet->setCellValue("C$row", ucfirst($trx['metode_bayar']))->mergeCells("C$row:C$totalRow");
      applyStyle($sheet, "B$row:C$totalRow", ['border', 'topCenter']);

      // Isi detail
      foreach ($details as $dt) {
        $sheet->fromArray([
          $dt['nama_produk'],
          $dt['jumlah'],
          $dt['harga_pokok'],
          $dt['harga_satuan'],
          $dt['subtotal_pokok'],
          $dt['subtotal'],
          $dt['subtotal'] - $dt['subtotal_pokok']
        ], null, "D$row");

        applyStyle($sheet, "D$row:J$row", ['border']);
        applyStyle($sheet, "E$row", ['center']);
        applyStyle($sheet, "F$row:J$row", ['right']);
        $totalPokok += $dt['subtotal_pokok'];
        $totalJual  += $dt['subtotal'];
        $totalLaba  += $dt['subtotal'] - $dt['subtotal_pokok'];
        $row++;
      }
    }

    // Baris total
    $sheet->setCellValue("A$row", 'TOTAL')->mergeCells("A$row:G$row");
    $sheet->setCellValue("H$row", $totalPokok);
    $sheet->setCellValue("I$row", $totalJual);
    $sheet->setCellValue("J$row", $totalLaba);
    applyStyle($sheet, "A$row:J$row", ['bold', 'border']);
    applyStyle($sheet, "H$row:J$row", ['right']);
    break;

  // ------------------ BULANAN ------------------
  case 'bulanan':
    $bulan = $_GET['bulan'] ?? date('Y-m');
    $data = getRekapTransaksiBulanan($bulan, $metode);
    $periode = date_indo('F Y', $bulan);

    $sheet->setCellValue("A$row", "Periode: " . $periode)->mergeCells("A$row:F$row");
    applyStyle($sheet, "A$row", ['center']);
    $row += 2;

    $headers = ['Tanggal', 'Jml Transaksi', 'Produk Terjual (satuan)', 'Total Pokok (Rp)', 'Total Jual (Rp)', 'Total Laba (Rp)'];
    foreach (range('A', 'F') as $i => $col) $sheet->setCellValue("$col$row", $headers[$i]);
    applyStyle($sheet, "A$row:F$row", ['bold', 'center', 'border']);
    $row++;

    $total = ['jumlah_transaksi' => 0, 'produk' => 0, 'pokok' => 0, 'jual' => 0, 'laba' => 0];
    foreach ($data as $r) {
      $sheet->fromArray([
        date_indo('d/m/Y', $r['tanggal']),
        $r['jumlah_transaksi'],
        $r['total_produk'],
        $r['total_pokok'],
        $r['total_jual'],
        $r['total_laba']
      ], null, "A$row");

      applyStyle($sheet, "A$row", ['border']);
      applyStyle($sheet, "B$row:C$row", ['border', 'center']);
      applyStyle($sheet, "D$row:F$row", ['right', 'border']);
      foreach ($total as $k => $_) $total[$k] += $r['total_' . $k] ?? $r[$k];
      $row++;
    }

    $sheet->fromArray(['TOTAL', $total['jumlah_transaksi'], $total['produk'], $total['pokok'], $total['jual'], $total['laba']], null, "A$row");
    applyStyle($sheet, "A$row", ['bold', 'border']);
    applyStyle($sheet, "B$row:C$row", ['bold', 'border', 'center']);
    applyStyle($sheet, "D$row:F$row", ['bold', 'right', 'border']);
    break;

  // ------------------ TAHUNAN ------------------
  case 'tahunan':
    $tahun = $_GET['tahun'] ?? date('Y');
    $periode = $tahun;
    $data = getRekapTransaksiTahunan($tahun, $metode);

    $sheet->setCellValue("A$row", "Periode: Tahun " . $periode)->mergeCells("A$row:F$row");
    applyStyle($sheet, "A$row", ['center']);
    $row += 2;

    $headers = ['Bulan', 'Jml Transaksi', 'Produk Terjual (satuan)', 'Total Pokok (Rp)', 'Total Jual (Rp)', 'Total Laba (Rp)'];
    foreach (range('A', 'F') as $i => $col) $sheet->setCellValue("$col$row", $headers[$i]);
    applyStyle($sheet, "A$row:F$row", ['bold', 'center', 'border']);
    $row++;

    $total = ['jumlah_transaksi' => 0, 'produk' => 0, 'pokok' => 0, 'jual' => 0, 'laba' => 0];
    foreach ($data as $r) {
      $sheet->fromArray([
        $r['bulan'],
        $r['jumlah_transaksi'],
        $r['total_produk'],
        $r['total_pokok'],
        $r['total_jual'],
        $r['total_laba']
      ], null, "A$row");

      applyStyle($sheet, "A$row", ['border']);
      applyStyle($sheet, "B$row:C$row", ['border', 'center']);
      applyStyle($sheet, "D$row:F$row", ['right', 'border']);
      $total['jumlah_transaksi'] += $r['jumlah_transaksi'] ?? 0;
      foreach ($total as $k => $_) $total[$k] += $r['total_' . $k] ?? $r[$k];
      $row++;
    }

    $sheet->fromArray(['TOTAL', $total['jumlah_transaksi'], $total['produk'],  $total['pokok'],  $total['jual'],  $total['laba']], null, "A$row");
    applyStyle($sheet, "A$row", ['bold', 'border']);
    applyStyle($sheet, "B$row:C$row", ['bold', 'border', 'center']);
    applyStyle($sheet, "D$row:F$row", ['bold', 'right', 'border']);
    break;
}

// ==== FORMAT ANGKA ====
$sheet->getStyle("D2:J" . $sheet->getHighestRow())
  ->getNumberFormat()->setFormatCode('#,##0');


// ==== AUTO SIZE & EXPORT ====
foreach (range('A', $sheet->getHighestDataColumn()) as $col)
  $sheet->getColumnDimension($col)->setAutoSize(true);

$filename = "Laporan_Transaksi_";
if ($metode) $filename .= ucfirst($metode) . '_';
$filename .= ucfirst($tipe) . "_" . str_replace(' ', '_', $periode) . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
