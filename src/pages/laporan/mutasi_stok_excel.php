<?php
page_require(['admin', 'manager']);
require_once INCLUDES_PATH . '/fungsi_rekap.php';
require INCLUDES_PATH . '/style_excel.php';
models('Produk');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ==== PARAMETER & INISIALISASI ====
$kode_produk = $_GET['produk'] ?? '';
$tglMulai = $_GET['mulai'] ?? '';
$tglSelesai = $_GET['selesai'] ?? '';

$produk = findProduk($kode_produk) ?? null;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Mutasi Stok Produk");

$title = "Laporan Mutasi Stok Produk GGMART";
$row = 1;

// ==== HEADER LAPORAN ====
$sheet->setCellValue("A$row", $title)->mergeCells("A$row:G$row");
applyStyle($sheet, "A$row", ['bold', 'center']);
$row++;

if ($produk) {
  $sheet->setCellValue("A$row", "Kode Produk: " . $produk['kode_produk'])->mergeCells("A$row:G$row");
  applyStyle($sheet, "A$row", ['center']);
  $row++;

  $sheet->setCellValue("A$row", "Nama Produk: " . $produk['nama_produk'] . " (" . ucfirst($produk['satuan_dasar']) . ")")->mergeCells("A$row:G$row");
  applyStyle($sheet, "A$row", ['center']);
  $row++;
}

// periode
$periode = 'Awal s/d ' . date_indo('d/m/Y');
if ($tglMulai && $tglSelesai) {
  $periode = date_indo('d/m/Y', $tglMulai) . " s/d " . date_indo('d/m/Y', $tglSelesai);
} elseif ($tglMulai) {
  $periode = date_indo('d/m/Y', $tglMulai) . " s/d " . date_indo('d/m/Y');
} elseif ($tglSelesai) {
  $periode = "Awal s/d " . date_indo('d/m/Y', $tglSelesai);
}

$sheet->setCellValue("A$row", "Periode: " . $periode)->mergeCells("A$row:G$row");
applyStyle($sheet, "A$row", ['center']);
$row++;

$sheet->setCellValue("A$row", "Dicetak Pada: " . date_indo('l, j F Y'))->mergeCells("A$row:G$row");
applyStyle($sheet, "A$row", ['center']);
$row += 2;


// ==== DATA ====
$data = getLaporanStokProduk($kode_produk, $tglMulai, $tglSelesai);

if ($produk) {
  // === Jika 1 produk dipilih ===
  $headers = ['Tanggal', 'Jenis', 'Jumlah', 'Harga Pokok (Rp)', 'Total Pokok (Rp)', 'Keterangan'];
  $cols = range('A', 'F');
  foreach ($cols as $i => $col) {
    $sheet->setCellValue("$col$row", $headers[$i]);
  }
  applyStyle($sheet, "A$row:F$row", ['bold', 'center', 'border']);
  $row++;

  $jumlah_masuk = $jumlah_keluar = $rp_masuk = $rp_keluar = 0;

  foreach ($data as $r) {
    $sheet->fromArray([
      date_indo('d/m/Y', $r['tanggal']),
      ucfirst($r['type']),
      $r['jumlah'],
      $r['harga_pokok'],
      $r['total_pokok'],
      $r['keterangan']
    ], null, "A$row");

    applyStyle($sheet, "A$row:F$row", ['border']);
    applyStyle($sheet, "A$row:B$row", ['center']);
    applyStyle($sheet, "C$row:C$row", ['center']);
    applyStyle($sheet, "D$row:E$row", ['right']);
    applyStyle($sheet, "F$row", ['left']);

    if ($r['type'] == 'masuk') {
      $jumlah_masuk += $r['jumlah'];
      $rp_masuk += $r['total_pokok'];
    } elseif ($r['type'] == 'keluar') {
      $jumlah_keluar += $r['jumlah'];
      $rp_keluar += $r['total_pokok'];
    }

    $row++;
  }

  // === Total ===
  $sheet->setCellValue("A$row", "TOTAL MASUK")->mergeCells("A$row:B$row");
  $sheet->setCellValue("C$row", $jumlah_masuk . ' ' . $produk['satuan_dasar']);
  $sheet->setCellValue("D$row", $rp_masuk);
  applyStyle($sheet, "A$row:D$row", ['bold', 'border']);
  $row++;

  $sheet->setCellValue("A$row", "TOTAL KELUAR")->mergeCells("A$row:B$row");
  $sheet->setCellValue("C$row", $jumlah_keluar . ' ' . $produk['satuan_dasar']);
  $sheet->setCellValue("D$row", $rp_keluar);
  applyStyle($sheet, "A$row:D$row", ['bold', 'border']);
} else {
  // === Jika semua produk ===
  $headers = ['Tanggal', 'Produk', 'Jenis', 'Jumlah', 'Harga Pokok (Rp)', 'Total Pokok (Rp)', 'Keterangan'];
  $cols = range('A', 'G');
  foreach ($cols as $i => $col) {
    $sheet->setCellValue("$col$row", $headers[$i]);
  }
  applyStyle($sheet, "A$row:G$row", ['bold', 'center', 'border']);
  $row++;

  foreach ($data as $r) {
    $sheet->fromArray([
      date_indo('d/m/Y', $r['tanggal']),
      $r['nama_produk'],
      ucfirst($r['type']),
      $r['jumlah'] . ' ' . $r['satuan_dasar'],
      $r['harga_pokok'],
      $r['total_pokok'],
      $r['keterangan']
    ], null, "A$row");

    applyStyle($sheet, "A$row:G$row", ['border']);
    applyStyle($sheet, "A$row:C$row", ['center']);
    applyStyle($sheet, "E$row:F$row", ['right']);
    applyStyle($sheet, "D$row:G$row", ['left']);
    $row++;
  }
}


// ==== FORMAT ANGKA ====
$sheet->getStyle("D2:F" . $sheet->getHighestRow())
  ->getNumberFormat()->setFormatCode('#,##0');

// ==== AUTO SIZE & EXPORT ====
foreach (range('A', $sheet->getHighestDataColumn()) as $col)
  $sheet->getColumnDimension($col)->setAutoSize(true);

$filename = "Laporan_Mutasi_Stok";
isset($produk['nama_produk']) ? $filename .= "_" . str_replace(' ', '_', $produk['nama_produk']) : '';
$filename .= "_" . str_replace(['/', ' '], '_', $periode) . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
