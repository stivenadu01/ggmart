<?php
page_require(['admin', 'kasir', 'manager']);
models('Transaksi');
models('DetailTransaksi');
models('Produk');
models('User');

$kode = $_GET['k'] ?? 0;
$trx = findTransaksi($kode);

if (!$trx) {
  echo "<h1>Transaksi tidak ditemukan.</h1>";
  redirect_back('');
  exit;
}

$detail = getDetailTransaksi($kode);
$user = findUser($trx['id_user']);
$tanggal = date('d/m/Y H:i', strtotime($trx['tanggal_transaksi']));
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Struk Transaksi - GGMART</title>
  <style>
    @media print {
      @page {
        size: 75mm auto;
        margin: 5mm;
      }

      body {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: #000;
      }
    }

    body {
      width: 280px;
      margin: 0 auto;
      padding: 10px;
      font-family: 'Courier New', monospace;
    }

    h2,
    h3,
    p {
      text-align: center;
      margin: 0;
      padding: 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }

    td {
      padding: 2px 0;
    }

    .right {
      text-align: right;
    }

    .center {
      text-align: center;
    }

    .total {
      border-top: 1px dashed #000;
      border-bottom: 1px dashed #000;
      font-weight: bold;
    }
  </style>
</head>

<body onload="print()">

  <h2>GG MART</h2>
  <p>Jl. Perintis Kemerdekaan, Klp. Lima, Kec. Klp. Lima, Kota Kupang, Nusa Tenggara Tim. 85228.<br>Telp: (0380) 123456</p>
  <hr>

  <p><strong>Kode Transaksi:</strong> <?= $trx['kode_transaksi'] ?><br>
    <strong>Tanggal:</strong> <?= $tanggal ?><br>
    <strong>Kasir:</strong> <?= htmlspecialchars($user['nama']) ?><br>
    <strong>Metode:</strong> <?= htmlspecialchars($trx['metode_bayar']) ?>
  </p>
  <hr>
  <table>
    <tbody>
      <?php foreach ($detail as $d): ?>
        <tr>
          <td colspan="3"><?= htmlspecialchars($d['nama_produk']) ?></td>
        </tr>
        <tr>
          <td><?= $d['jumlah'] ?> x <?= number_format($d['harga_satuan'], 0, ',', '.') ?></td>
          <td class="right">=</td>
          <td class="right"><?= number_format($d['subtotal'], 0, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="total right">Total: Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></p>

  <?php if (!empty($trx['uang_diterima'])): ?>
    <p class="right">Tunai: Rp <?= number_format($trx['uang_diterima'], 0, ',', '.') ?><br>
      Kembali: Rp <?= number_format($trx['uang_diterima'] - $trx['total_harga'], 0, ',', '.') ?></p>
  <?php endif; ?>

  <hr>
  <p class="center">Terima kasih atas kunjungan Anda!<br>Selamat berbelanja kembali 🙏</p>

</body>
<script>
  // window.onload = () => {
  //   window.print();
  //   if (document.referrer) window.location.href = document.referrer;
  //   window.history.back();
  // }
  window.onafterprint = () => {
    if (document.referrer) window.location.href = document.referrer;
    // window.history.back();
    window.close();
  };
</script>

</html>