<?php
models('Transaksi');
models('DetailTransaksi');
models('Produk');
include INCLUDES_PATH . "fungsi_rekap.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// ================= PARAMETER ===================
$tipe   = $_GET['tipe'] ?? 'harian';
$tanggal = $_GET['tanggal'] ?? null;
$bulan   = $_GET['bulan'] ?? null;
$tahun   = $_GET['tahun'] ?? null;
$metode  = $_GET['metode'] ?? null;
$search  = $_GET['search'] ?? null;


if ($tipe === 'harian' && $tanggal) {
  $periode = date('d F Y', strtotime($tanggal));
} elseif ($tipe === 'bulanan' && $bulan) {
  $periode = date('F Y', strtotime($bulan));
} elseif ($tipe === 'tahunan' && $tahun) {
  $periode = $tahun;
} else {
  $periode = '';
}



// ================= INISIALISASI ================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Laporan Penjualan GGMART");

// ================= HEADER LAPORAN ==============
$row = 1;
$sheet->setCellValue("A{$row}", "Laporan Penjualan GGMART");
$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row++;

$sheet->setCellValue("A{$row}", "Dicetak pada: " . date('l, d F Y'));
$sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row++;

$sheet->setCellValue("A{$row}", "Periode: " . $periode);
$sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row += 2;


// ================== MODE HARIAN ===================
if ($tipe === 'harian') {
  // Merge Header
  $sheet->mergeCells("A1:J1");
  $sheet->mergeCells("A2:J2");
  $sheet->mergeCells("A3:J3");
  [$transaksiList,, $summary] = getTransaksiList(1, 9999, $search, $tanggal, $tanggal, $metode);

  // === HEADER TINGKAT 1 ===
  $sheet->mergeCells("A{$row}:A" . ($row + 1)); // Kode Transaksi
  $sheet->mergeCells("B{$row}:B" . ($row + 1)); // Waktu
  $sheet->mergeCells("C{$row}:C" . ($row + 1)); // Metode
  $sheet->mergeCells("D{$row}:J{$row}");        // Detail Transaksi

  $sheet->setCellValue("A{$row}", "Kode Transaksi");
  $sheet->setCellValue("B{$row}", "Waktu");
  $sheet->setCellValue("C{$row}", "Metode");
  $sheet->setCellValue("D{$row}", "Detail Transaksi");

  $sheet->getStyle("A{$row}:J{$row}")
    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
  $sheet->getStyle("A{$row}:J{$row}")
    ->getFont()->setBold(true);
  $sheet->getStyle("A{$row}:J{$row}")
    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

  // === HEADER TINGKAT 2 ===
  $row++;
  $headers2 = ['D' => 'Produk', 'E' => 'Jml', 'F' => 'Pokok (Rp)', 'G' => 'Jual (Rp)', 'H' => 'T.Pokok (Rp)', 'I' => 'T.Jual (Rp)', 'J' => 'T.Laba (Rp)'];
  foreach ($headers2 as $col => $val) {
    $sheet->setCellValue("{$col}{$row}", $val);
    $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
    $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("{$col}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
  }

  // Tambahkan border vertikal juga untuk kolom A–C baris kedua
  foreach (['A', 'B', 'C'] as $col) {
    $sheet->getStyle("{$col}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
  }

  $row++;
  // === ISI DATA ===
  foreach ($transaksiList as $trx) {
    $detailList = getDetailTransaksi($trx['kode_transaksi']);
    $rowStart = $row; // simpan posisi awal baris transaksi

    foreach ($detailList as $d) {
      $pokok = (float)($d['harga_pokok'] ?? 0);
      $jual = (float)($d['harga_satuan'] ?? 0);
      $sub_pokok = (float)($d['subtotal_pokok'] ?? 0);
      $subtotal = (float)($d['subtotal'] ?? 0);
      $laba = $subtotal - $sub_pokok;

      // Kolom detail item (D–J)
      $sheet->setCellValue("D{$row}", $d['nama_produk']);
      $sheet->setCellValue("E{$row}", $d['jumlah']);
      $sheet->setCellValue("F{$row}", $pokok);
      $sheet->setCellValue("G{$row}", $jual);
      $sheet->setCellValue("H{$row}", $sub_pokok);
      $sheet->setCellValue("I{$row}", $subtotal);
      $sheet->setCellValue("J{$row}", $laba);

      // Format angka kanan
      foreach (['F', 'G', 'H', 'I', 'J'] as $col) {
        $sheet->getStyle("{$col}{$row}")
          ->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("{$col}{$row}")
          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      }

      // Border baris ini
      $sheet->getStyle("D{$row}:J{$row}")
        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

      $row++;
    }

    // Hitung berapa baris untuk transaksi ini
    $rowEnd = $row - 1;

    // Merge kolom Kode, Waktu, Metode untuk transaksi ini
    $sheet->mergeCells("A{$rowStart}:A{$rowEnd}");
    $sheet->mergeCells("B{$rowStart}:B{$rowEnd}");
    $sheet->mergeCells("C{$rowStart}:C{$rowEnd}");

    // Isi nilai kolom transaksi (hanya sekali)
    $sheet->setCellValue("A{$rowStart}", $trx['kode_transaksi']);
    $sheet->setCellValue("B{$rowStart}", date('H:i:s', strtotime($trx['tanggal_transaksi'])));
    $sheet->setCellValue("C{$rowStart}", ucfirst($trx['metode_bayar']));

    // Rata tengah kolom kiri
    foreach (['A', 'B', 'C'] as $col) {
      $sheet->getStyle("{$col}{$rowStart}:{$col}{$rowEnd}")
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle("{$col}{$rowStart}:{$col}{$rowEnd}")
        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
  }


  // === TOTAL ===
  $sheet->setCellValue("A{$row}", "TOTAL");
  $sheet->mergeCells("A{$row}:G{$row}");
  $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
  $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

  $sheet->setCellValue("H{$row}", $summary['pokok'] ?? 0);
  $sheet->setCellValue("I{$row}", $summary['jual'] ?? 0);
  $sheet->setCellValue("J{$row}", $summary['laba'] ?? 0);
  $sheet->getStyle("H{$row}:J{$row}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
}


// ================== MODE BULANAN ===================
elseif ($tipe === 'bulanan') {
  $sheet->mergeCells("A1:F1");
  $sheet->mergeCells("A2:F2");
  $sheet->mergeCells("A3:F3");
  $start = date('Y-m', strtotime($bulan)) . '-01';
  $end = date('Y-m-t', strtotime($start));
  $rekap = getRekapPerHari($start, $end, $metode);

  $headers = ['Tanggal', 'Transaksi', 'Produk Terjual (Rp)', 'Total Pokok (Rp)', 'Total Penjualan (Rp)', 'Total Laba (Rp)'];
  $colLetters = ['A', 'B', 'C', 'D', 'E', 'F'];

  foreach ($headers as $i => $val) {
    $sheet->setCellValue("{$colLetters[$i]}{$row}", $val);
    $sheet->getStyle("{$colLetters[$i]}{$row}")->getFont()->setBold(true);
    $sheet->getStyle("{$colLetters[$i]}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("{$colLetters[$i]}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  }
  $row++;

  $totalTrx = $totalProduk = $totalPokok = $totalJual = $totalLaba = 0;

  foreach ($rekap as $r) {
    $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($r['tanggal'])));
    $sheet->setCellValue("B{$row}", $r['transaksi']);
    $sheet->setCellValue("C{$row}", $r['produk']);
    $sheet->setCellValue("D{$row}", $r['pokok']);
    $sheet->setCellValue("E{$row}", $r['jual']);
    $sheet->setCellValue("F{$row}", $r['laba']);
    $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle("D{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $totalTrx += $r['transaksi'];
    $totalProduk += $r['produk'];
    $totalPokok += $r['pokok'];
    $totalJual += $r['jual'];
    $totalLaba += $r['laba'];
    $row++;
  }

  $sheet->setCellValue("A{$row}", "TOTAL");
  $sheet->setCellValue("B{$row}", $totalTrx);
  $sheet->setCellValue("C{$row}", $totalProduk);
  $sheet->setCellValue("D{$row}", $totalPokok);
  $sheet->setCellValue("E{$row}", $totalJual);
  $sheet->setCellValue("F{$row}", $totalLaba);
  $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
  $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
  $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
}

// ================== MODE TAHUNAN ===================
elseif ($tipe === 'tahunan') {
  $sheet->mergeCells("A1:F1");
  $sheet->mergeCells("A2:F2");
  $sheet->mergeCells("A3:F3");
  $rekap = getRekapPerBulan($tahun, $metode);
  $headers = ['Bulan', 'Transaksi', 'Produk Terjual (Rp)', 'Total Pokok (Rp)', 'Total Penjualan (Rp)', 'Total Laba (Rp)'];
  $colLetters = ['A', 'B', 'C', 'D', 'E', 'F'];

  foreach ($headers as $i => $val) {
    $sheet->setCellValue("{$colLetters[$i]}{$row}", $val);
    $sheet->getStyle("{$colLetters[$i]}{$row}")->getFont()->setBold(true);
    $sheet->getStyle("{$colLetters[$i]}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("{$colLetters[$i]}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  }
  $row++;

  $totalTrx = $totalProduk = $totalPokok = $totalJual = $totalLaba = 0;
  foreach ($rekap as $r) {
    $sheet->setCellValue("A{$row}", ucfirst($r['bulan']));
    $sheet->setCellValue("B{$row}", $r['transaksi']);
    $sheet->setCellValue("C{$row}", $r['produk']);
    $sheet->setCellValue("D{$row}", $r['pokok']);
    $sheet->setCellValue("E{$row}", $r['jual']);
    $sheet->setCellValue("F{$row}", $r['laba']);
    $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle("D{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $totalTrx += $r['transaksi'];
    $totalProduk += $r['produk'];
    $totalPokok += $r['pokok'];
    $totalJual += $r['jual'];
    $totalLaba += $r['laba'];
    $row++;
  }

  $sheet->setCellValue("A{$row}", "TOTAL");
  $sheet->setCellValue("B{$row}", $totalTrx);
  $sheet->setCellValue("C{$row}", $totalProduk);
  $sheet->setCellValue("D{$row}", $totalPokok);
  $sheet->setCellValue("E{$row}", $totalJual);
  $sheet->setCellValue("F{$row}", $totalLaba);
  $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
  $sheet->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
  $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
}


// ================== STYLING TAMBAHAN =================
foreach (range('A', 'K') as $col) {
  $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ================== OUTPUT FILE =====================
$filename = "Laporan_Penjualan_{$tipe}_GGMART_" . str_replace(' ', '_', $periode) . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
