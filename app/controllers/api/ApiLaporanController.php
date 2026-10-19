<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ApiLaporanController
{
  public function __construct()
  {
    require_once ROOT_PATH . '/app/helpers/laporan_helper.php';
    require_once ROOT_PATH . '/app/helpers/style_excel.php';
  }

  // LAPORAN MUTASI STOK EXCEL
  public function mutasiStok()
  {
    model('Produk');
    model('MutasiStok');
    // ==== PARAMETER & INISIALISASI ====
    $kode_produk = query('produk');
    $tglMulai = query('mulai');
    $tglSelesai = query('selesai');

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
        $r['total_pokok'] = $r['harga_pokok'] * $r['jumlah'];
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

      $row++;
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
        $r['total_pokok'] =  $r['harga_pokok'] * $r['jumlah'];
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
        applyStyle($sheet, "D$row:G$row", ['left']);
        applyStyle($sheet, "E$row:F$row", ['right']);
        $row++;
      }
    }


    // ==== FORMAT ANGKA ====
    $sheet->getStyle("D2:F" . $sheet->getHighestRow())
      ->getNumberFormat()->setFormatCode('#,##0');

    // ==== PENUTUP / TANDA TANGAN PIMPINAN ====
    appendReportSignature($sheet, $row, 'F');

    // ==== AUTO SIZE & EXPORT ====
    foreach (range('A', $sheet->getHighestDataColumn()) as $col)
      $sheet->getColumnDimension($col)->setAutoSize(true);

    $filename = "Laporan_Mutasi_Stok";
    isset($produk['nama_produk']) ? $filename .= "_" . str_replace(' ', '_', $produk['nama_produk']) : '';
    $filename .= "_" . str_replace(['/', ' '], '_', $periode) . ".xlsx";


    if (ob_get_length()) ob_end_clean();
    ob_start();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function transaksi()
  {
    model('Transaksi');
    model('DetailTransaksi');
    // ==== PARAMETER & INISIALISASI ====
    $tipe = query('tipe') ?? 'harian';
    $metode = query('metode') ?? '';
    if (!in_array($tipe,['harian','bulanan','tahunan'],true) || !in_array($metode,['','tunai','transfer','qris'],true)) return response(['success'=>false,'message'=>'Filter laporan tidak valid.'],422);
    $periode = '';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Laporan");

    $title = "Laporan Transaksi " . ucfirst($tipe) . " GGMART";

    // ==== HEADER LAPORAN ====
    $row = 1;
    $mergeCol = ($tipe == 'harian') ? 'J' : 'F';
    $sheet->setCellValue("A$row", $title)->mergeCells("A$row:$mergeCol$row");
    applyStyle($sheet, "A$row:$mergeCol$row", ['bold', 'center']);
    $row++;

    $sheet->setCellValue("A$row", "Dicetak Pada: " . date_indo('l, j F Y'))->mergeCells("A$row:$mergeCol$row");
    applyStyle($sheet, "A$row:$mergeCol$row", ['center']);

    if ($metode) {
      $row++;
      $sheet->setCellValue("A$row", "Metode Pembayaran: " . strtoupper($metode))->mergeCells("A$row:$mergeCol$row");
      applyStyle($sheet, "A$row:$mergeCol$row", ['center']);
    }
    $row++;

    // ==== SWITCH PER TIPE ====
    switch ($tipe) {
      // ------------------ HARIAN ------------------
      case 'harian':
        $tanggal = query('tanggal') ?? date('Y-m-d');
        $data = getRekapTransaksiHarian($tanggal, $metode);
        $periode = date_indo('d F Y', $tanggal);

        $sheet->setCellValue("A$row", "Periode: " . $periode)->mergeCells("A$row:J$row");
        applyStyle($sheet, "A$row:J$row", ['center']);
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
        $startRowA = $row;
        foreach ($data as $trx) {
          $details = findDetailTransaksi($trx['kode_transaksi']);
          if (!$details) continue;
          $totalRow = $row + count($details) - 1;

          // Merge kolom identitas transaksi
          $sheet->setCellValue("A$row", $trx['kode_transaksi'])->mergeCells("A$row:A$totalRow");
          applyStyle($sheet, "A$row:A$totalRow", ['border', 'topLeft']);
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
              "=E$row*F$row",
              "=E$row*G$row",
              "=I$row-H$row"
            ], null, "D$row");

            applyStyle($sheet, "D$row:J$row", ['border']);
            applyStyle($sheet, "E$row", ['center']);
            applyStyle($sheet, "F$row:J$row", ['right']);
            $row++;
          }
        }
        $endRowA = $row - 1;

        // Baris total
        $sheet->setCellValue("A$row", 'TOTAL')->mergeCells("A$row:G$row");
        $sheet->setCellValue("H$row", $endRowA >= $startRowA ? "=SUM(H$startRowA:H$endRowA)" : 0);
        $sheet->setCellValue("I$row", $endRowA >= $startRowA ? "=SUM(I$startRowA:I$endRowA)" : 0);
        $sheet->setCellValue("J$row", $endRowA >= $startRowA ? "=SUM(J$startRowA:J$endRowA)" : 0);
        applyStyle($sheet, "A$row:G$row", ['bold', 'border', 'center']);
        applyStyle($sheet, "H$row:J$row", ['right', 'bold', 'border']);

        // ==== FORMAT ANGKA ====
        $sheet->getStyle("F7:J" . $sheet->getHighestRow())
          ->getNumberFormat()->setFormatCode('#,##0');
        break;

      // ------------------ BULANAN ------------------
      case 'bulanan':
        $bulan = query('bulan') ?? date('Y-m');
        $data = getRekapTransaksiBulanan($bulan, $metode);
        $periode = date_indo('F Y', $bulan);

        $sheet->setCellValue("A$row", "Periode: " . $periode)->mergeCells("A$row:F$row");
        applyStyle($sheet, "A$row", ['center']);
        $row += 2;

        $headers = ['Tanggal', 'Jml Transaksi', 'Produk Terjual (satuan)', 'Total Pokok (Rp)', 'Total Jual (Rp)', 'Total Laba (Rp)'];
        foreach (range('A', 'F') as $i => $col) $sheet->setCellValue("$col$row", $headers[$i]);
        applyStyle($sheet, "A$row:F$row", ['bold', 'center', 'border']);
        $row++;

        $sRow = $row;
        foreach ($data as $r) {
          $sheet->fromArray([
            date_indo('d/m/Y', $r['tanggal']),
            $r['jumlah_transaksi'],
            $r['total_produk'],
            $r['total_pokok'],
            $r['total_jual'],
            "=E$row-D$row"
          ], null, "A$row");

          applyStyle($sheet, "A$row:F$row", ['border']);
          applyStyle($sheet, "B$row:C$row", ['border', 'center']);
          applyStyle($sheet, "D$row:F$row", ['right', 'border']);
          $row++;
        }
        $eRow = $row - 1;

        $totals=['TOTAL'];
        foreach (range('B','F') as $col) $totals[]=$eRow>=$sRow ? "=SUM($col$sRow:$col$eRow)" : 0;
        $sheet->fromArray($totals, null, "A$row");
        applyStyle($sheet, "A$row:F$row", ['bold', 'border']);
        applyStyle($sheet, "B$row:C$row", ['bold', 'border', 'center']);
        applyStyle($sheet, "D$row:F$row", ['bold', 'right', 'border']);
        // ==== FORMAT ANGKA ====
        $sheet->getStyle("D6:F" . $sheet->getHighestRow())
          ->getNumberFormat()->setFormatCode('#,##0');
        break;

      // ------------------ TAHUNAN ------------------
      case 'tahunan':
        $tahun = query('tahun') ?? date('Y');
        $periode = $tahun;
        $data = getRekapTransaksiTahunan($tahun, $metode);

        $sheet->setCellValue("A$row", "Periode: Tahun " . $periode)->mergeCells("A$row:F$row");
        applyStyle($sheet, "A$row", ['center']);
        $row += 2;

        $headers = ['Bulan', 'Jml Transaksi', 'Produk Terjual (satuan)', 'Total Pokok (Rp)', 'Total Jual (Rp)', 'Total Laba (Rp)'];
        foreach (range('A', 'F') as $i => $col) $sheet->setCellValue("$col$row", $headers[$i]);
        applyStyle($sheet, "A$row:F$row", ['bold', 'center', 'border']);
        $row++;

        $sRow = $row;
        foreach ($data as $r) {
          $sheet->fromArray([
            $r['bulan'],
            $r['jumlah_transaksi'],
            $r['total_produk'],
            $r['total_pokok'],
            $r['total_jual'],
            "=E$row-D$row"
          ], null, "A$row");

          applyStyle($sheet, "A$row", ['border']);
          applyStyle($sheet, "B$row:C$row", ['border', 'center']);
          applyStyle($sheet, "D$row:F$row", ['right', 'border']);
          $row++;
        }
        $eRow = $row - 1;

        $totals=['TOTAL'];
        foreach (range('B','F') as $col) $totals[]=$eRow>=$sRow ? "=SUM($col$sRow:$col$eRow)" : 0;
        $sheet->fromArray($totals, null, "A$row");
        applyStyle($sheet, "A$row", ['bold', 'border']);
        applyStyle($sheet, "B$row:C$row", ['bold', 'border', 'center']);
        applyStyle($sheet, "D$row:F$row", ['bold', 'right', 'border']);
        // ==== FORMAT ANGKA ====
        $sheet->getStyle("D6:F" . $sheet->getHighestRow())
          ->getNumberFormat()->setFormatCode('#,##0');
        break;
    }


    // ==== PENUTUP / TANDA TANGAN PIMPINAN ====
    appendReportSignature($sheet, $row, $mergeCol);

    // ==== AUTO SIZE & EXPORT ====
    foreach (range('A', $sheet->getHighestDataColumn()) as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

    // Ongkir dicatat sekali per pesanan, terpisah dari penjualan dan laba barang.
    $shippingSheet=$spreadsheet->createSheet();
    $shippingSheet->setTitle('Pengiriman');
    $shippingSheet->setCellValue('A1','Rekap Pengiriman GGmart')->mergeCells('A1:G1');
    $shippingSheet->setCellValue('A2','Periode: ' . $periode)->mergeCells('A2:G2');
    $shippingSheet->setCellValue('A3','Ongkir terpisah dari laba barang; hanya transaksi selesai.')->mergeCells('A3:G3');
    applyStyle($shippingSheet,'A1:G1',['bold','center']);
    $shippingSheet->fromArray(['Kode transaksi','Tanggal','Metode pembayaran','Pengambilan','Kelurahan','Ongkir (Rp)','Total pembayaran (Rp)'],null,'A5');
    applyStyle($shippingSheet,'A5:G5',['bold','border','center']);
    $periodValue=$tipe==='harian' ? $tanggal : ($tipe==='bulanan' ? $bulan : $tahun);
    $filter=$tipe==='harian' ? 'DATE(tanggal_transaksi)=DATE(?)' : ($tipe==='bulanan' ? "DATE_FORMAT(tanggal_transaksi,'%Y-%m')=?" : 'YEAR(tanggal_transaksi)=?');
    $sql="SELECT kode_transaksi,tanggal_transaksi,metode_bayar,metode_pengiriman,kelurahan,ongkir,total_tagihan FROM transaksi WHERE status='selesai' AND $filter";
    $parameters=[$periodValue];
    if ($metode!=='') { $sql.=' AND metode_bayar=?'; $parameters[]=$metode; }
    $shippingRows=oq($sql . ' ORDER BY tanggal_transaksi,kode_transaksi',$parameters)->fetch_all(MYSQLI_ASSOC);
    $shippingRow=6; $shippingTotal=0; $paymentTotal=0;
    foreach ($shippingRows as $trx) {
      $shippingSheet->fromArray([$trx['kode_transaksi'],date_indo('d/m/Y H:i',$trx['tanggal_transaksi']),ucfirst($trx['metode_bayar']),$trx['metode_pengiriman']==='kirim' ? 'Pengiriman lokal' : 'Ambil di tempat',$trx['kelurahan'] ?: '-',(float)$trx['ongkir'],(float)$trx['total_tagihan']],null,"A$shippingRow");
      applyStyle($shippingSheet,"A$shippingRow:G$shippingRow",['border']);
      $shippingTotal+=(float)$trx['ongkir']; $paymentTotal+=(float)$trx['total_tagihan']; $shippingRow++;
    }
    $shippingSheet->setCellValue("A$shippingRow",'TOTAL')->mergeCells("A$shippingRow:E$shippingRow");
    $shippingSheet->setCellValue("F$shippingRow",$shippingTotal); $shippingSheet->setCellValue("G$shippingRow",$paymentTotal);
    applyStyle($shippingSheet,"A$shippingRow:G$shippingRow",['bold','border']);
    $shippingSheet->getStyle("F6:G$shippingRow")->getNumberFormat()->setFormatCode('#,##0');
    foreach (range('A','G') as $col) $shippingSheet->getColumnDimension($col)->setAutoSize(true);
    $spreadsheet->setActiveSheetIndex(0);

    $filename = "Laporan_Transaksi_";
    if ($metode) $filename .= ucfirst($metode) . '_';
    $filename .= ucfirst($tipe) . "_" . str_replace(' ', '_', $periode) . ".xlsx";


    if (ob_get_length()) ob_end_clean();
    ob_start();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }
}
