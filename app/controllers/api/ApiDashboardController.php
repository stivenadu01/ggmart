<?php

class ApiDashboardController
{

  public function __construct()
  {
    model('Transaksi');
    model('Produk');
    model('User');
  }

  public function summary()
  {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // === DATA HARI INI ===
    $penjualan_hari_ini = sumPenjualanByDate($today);
    $laba_hari_ini = sumLabaByDate($today);
    $trx_hari_ini = countTransaksiByDate($today);

    // === DATA KEMARIN ===
    $penjualan_kemarin = sumPenjualanByDate($yesterday);
    $laba_kemarin = sumLabaByDate($yesterday);
    $trx_kemarin = countTransaksiByDate($yesterday);

    // === GROWTH FUNCTION ===
    $growth = function ($today, $yesterday) {
      if ($yesterday == 0) return $today > 0 ? 100 : 0;
      return round((($today - $yesterday) / $yesterday) * 100, 2);
    };

    $data = [
      'total_produk' => countAllProduk(),

      'transaksi_hari_ini' => $trx_hari_ini,
      'penjualan_hari_ini' => $penjualan_hari_ini,
      'laba_hari_ini' => $laba_hari_ini,

      // 🔥 GROWTH
      'growth' => [
        'penjualan' => $growth($penjualan_hari_ini, $penjualan_kemarin),
        'laba' => $growth($laba_hari_ini, $laba_kemarin),
        'transaksi' => $growth($trx_hari_ini, $trx_kemarin),
      ]
    ];

    return response([
      'success' => true,
      'data' => $data
    ]);
  }

  public function analytics()
  {
    $today = date('Y-m-d');
    $start7 = date('Y-m-d', strtotime('-6 days'));
    $start30 = date('Y-m-d', strtotime('-29 days'));

    // =========================
    // 📊 PENJUALAN TREND (7 HARI)
    // =========================
    $trend = getPenjualanTrend7Hari();

    // =========================
    // 💰 LABA + OMZET BULAN INI
    // =========================
    $bulan_ini = date('Y-m');
    $bulan_lalu = date('Y-m', strtotime('-1 month'));

    // omzet & laba bulan ini
    $omzet_bulan_ini = sumOmzetByMonth($bulan_ini);
    $laba_bulan_ini = sumLabaByMonth($bulan_ini);

    // omzet & laba bulan lalu
    $omzet_bulan_lalu = sumOmzetByMonth($bulan_lalu);
    $laba_bulan_lalu = sumLabaByMonth($bulan_lalu);

    // growth function
    $growth = function ($now, $prev) {
      if ($prev == 0) return $now > 0 ? 100 : 0;
      return round((($now - $prev) / $prev) * 100, 2);
    };

    // =========================
    // 🏆 TOP PRODUK TERLARIS
    // =========================
    $top_produk = getTopProdukTerlaris(5);

    // =========================
    // 🐌 PRODUK LAMBAT LAKU
    // =========================
    $slow_produk = getProdukSlowMoving(5);

    // =========================
    // ⚠️ PRODUK HAMPIR HABIS
    // =========================
    $low_stock = getProdukHampirHabis(10);

    // =========================
    // 💎 PRODUK PALING UNTUNG
    // =========================
    $profit_produk = getProdukPalingUntung(5);

    // =========================
    // 🕒 JAM RAMAI TRANSAKSI
    // =========================
    $jam_ramai = getJamRamaiTransaksi();

    // =========================
    // 👥 USER PALING SERING BELANJA
    // =========================
    $top_user = getTopUserBelanja(5);

    return response([
      'success' => true,
      'data' => [
        'trend' => $trend,
        'monthly' => [
          'omzet_bulan_ini' => $omzet_bulan_ini,
          'laba_bulan_ini' => $laba_bulan_ini,

          'omzet_growth' => $growth($omzet_bulan_ini, $omzet_bulan_lalu),
          'laba_growth' => $growth($laba_bulan_ini, $laba_bulan_lalu),
        ],
        'top_produk' => $top_produk,
        'slow_produk' => $slow_produk,
        'low_stock' => $low_stock,
        'profit_produk' => $profit_produk,
        'jam_ramai' => $jam_ramai,
        'top_user' => $top_user
      ]
    ]);
  }
}
