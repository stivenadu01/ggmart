<?php
function notificationSession(): string
{
  if (empty($_SESSION['notifikasi_sesi'])) $_SESSION['notifikasi_sesi'] = bin2hex(random_bytes(16));
  return $_SESSION['notifikasi_sesi'];
}

// Disimpan dalam transaksi yang sama dengan riwayat. Rollback pesanan juga
// membatalkan notifikasi; riwayat_pesanan tetap menjadi sumber audit tunggal.
function queueOrderNotifications(int $history, string $code, string $event, string $note, ?int $actor): void
{
  $trx = orderRow($code);
  if (!$trx) return;
  $online = $trx['sumber_transaksi'] === 'online';
  $customer = $online && $trx['user_role'] === 'pelanggan';
  $recipients = [];
  $add = static function (int $id, string $role, string $title, string $body, bool $popup = true, ?string $url = null) use (&$recipients, $code, $actor): void {
    $url ??= $role === 'pelanggan' ? '/pembayaran/' . rawurlencode($code) : '/admin/transaksi/' . rawurlencode($code);
    $recipients[$id] = [$id, $role, $title, mb_substr($body, 0, 500), $url, $popup && $actor !== $id ? 1 : 0];
  };
  $toCustomer = static function (string $title, string $body, bool $popup = true) use ($customer, $trx, $add): void {
    if ($customer) $add((int)$trx['id_pengguna'], 'pelanggan', $title, $body, $popup);
  };
  $toAdmins = static function (string $title, string $body, bool $popup = true) use ($add): void {
    foreach (oq("SELECT id_pengguna FROM pengguna WHERE role='admin' AND is_verified=1")->fetch_all(MYSQLI_ASSOC) as $u) $add((int)$u['id_pengguna'], 'admin', $title, $body, $popup);
  };
  $toCashier = static function (string $title, string $body, bool $popup = true) use ($online, $trx, $add): void {
    if (!$online && $trx['user_role'] === 'kasir') $add((int)$trx['id_pengguna'], 'kasir', $title, $body, $popup);
  };
  $label = $code . ' · ' . ($trx['metode_pengiriman'] === 'ambil' ? 'Ambil di toko' : 'Pengiriman lokal');
  switch ($event) {
    case 'checkout':
      if (!$online) break;
      $method = ['tunai' => 'Tunai saat ambil', 'transfer' => 'Transfer manual', 'qris' => 'QRIS'][$trx['metode_bayar']] ?? 'Pembayaran';
      $toCustomer('Pesanan dibuat', $label . '. Pantau pesanan Anda di sini.', false);
      $toAdmins('Pesanan baru masuk', $label . ' · ' . $method . '.');
      break;
    case 'pembayaran_menunggu_verifikasi':
      $toCustomer('Menunggu verifikasi pembayaran', $code . '. Bukti transfer sudah diterima dan akan diperiksa admin.', false);
      $toAdmins('Bukti transfer perlu diverifikasi', $code . '. Periksa bukti transfer sebelum menerima pembayaran.');
      break;
    case 'pembayaran_ditolak':
      $body = $code . '. ' . $note . ' Unggah kembali bukti transfer yang benar.';
      $toCustomer('Bukti transfer ditolak', $body);
      $toCashier('Bukti transfer ditolak', $body);
      $toAdmins('Bukti transfer ditolak', $code . '. Bukti perlu diunggah ulang.', false);
      break;
    case 'pembayaran_lunas':
      if ($trx['metode_bayar'] === 'tunai') {
        $toCustomer('Pembayaran tunai diterima', $code . '. Pembayaran diterima saat pengambilan pesanan.', false);
      } else {
        $toCustomer('Pembayaran diterima, pesanan diproses', $code . '. Pembayaran terkonfirmasi. Pesanan mulai disiapkan.');
      }
      // Pembayaran tunai di kasir merupakan tindakan petugas sendiri.
      if ($online || $trx['metode_bayar'] !== 'tunai') $toAdmins('Pembayaran telah diterima', $label . ($online && $trx['metode_bayar'] !== 'tunai' ? '. Pesanan mulai disiapkan.' : '. Transaksi selesai.'));
      $toCashier('Pembayaran diterima', $code . '. Transaksi selesai. Struk sudah tersedia.');
      break;
    case 'diproses':
      // Lunas dan otomatis diproses menjadi satu pemberitahuan pembayaran.
      if ($trx['status_bayar'] === 'lunas') break;
      $toCustomer('Pesanan sedang diproses', $code . '. Pesanan mulai disiapkan. Bayar tunai saat ambil di toko.', false);
      break;
    case 'siap_diambil':
      $toCustomer('Pesanan siap diambil', $code . '. Silakan ambil pesanan Anda di GGmart.' . ($trx['status_bayar'] !== 'lunas' ? ' Pembayaran tunai dilakukan saat pengambilan.' : ''));
      if ($online && $trx['metode_bayar'] === 'tunai') {
        foreach (oq("SELECT id_pengguna FROM pengguna WHERE role='kasir' AND is_verified=1")->fetch_all(MYSQLI_ASSOC) as $u) $add((int)$u['id_pengguna'], 'kasir', 'Pesanan tunai siap diambil', $code . '. Layani pembayaran saat pelanggan mengambil pesanan.', true, '/admin/kasir?pesanan=1');
      }
      break;
    case 'dikirim':
      $toCustomer('Pesanan sedang dikirim', $code . '. Pesanan sudah diserahkan kepada pengantar.');
      break;
    case 'selesai':
      $toCustomer('Pesanan selesai', $code . '. Pesanan sudah diterima. Detail pesanan dan struk tersedia.');
      break;
    case 'pembayaran_kedaluwarsa':
      $toCustomer('Waktu pembayaran berakhir', $code . '. Pesanan dibatalkan karena pembayaran belum diterima.');
      if ($online) $toAdmins('Waktu pembayaran berakhir', $code . '. Pesanan dibatalkan otomatis.', false);
      $toCashier('Waktu pembayaran berakhir', $code . '. Transaksi belum dibayar dan telah dibatalkan.');
      break;
    case 'dibatalkan':
      if (in_array($trx['status_bayar'], ['kedaluwarsa', 'perlu_refund'], true)) break;
      $toCustomer('Pesanan dibatalkan', $code . '. ' . $note);
      if ($online) $toAdmins('Pesanan dibatalkan', $code . '. ' . $note);
      $toCashier('Transaksi dibatalkan', $code . '. ' . $note);
      break;
    case 'pembayaran_perlu_refund':
      $toCustomer('Pengembalian dana sedang ditangani', $code . '. Pesanan dibatalkan. Admin akan menangani pengembalian dana.');
      $toAdmins('Pengembalian dana perlu ditangani', $code . '. Pembayaran sudah diterima pada pesanan yang dibatalkan.');
      $toCashier('Pengembalian dana perlu ditangani', $code . '. Hubungi admin untuk penanganan pengembalian dana.');
      break;
    case 'pembayaran_dikembalikan':
      $toCustomer('Pengembalian dana dicatat', $code . '. Admin telah mencatat pengembalian dana. Lihat detail pesanan.');
      $toCashier('Pengembalian dana dicatat', $code . '. Pengembalian dana telah dicatat admin.');
      $toAdmins('Pengembalian dana dicatat', $code . '. Pengembalian dana telah dicatat.', false);
      break;
  }
  foreach ($recipients as [$id, $role, $title, $body, $url, $popup]) {
    oq('INSERT INTO notifikasi(id_riwayat,id_pengguna,peran,jenis,judul,pesan,tautan,popup) VALUES (?,?,?,?,?,?,?,?)', [$history, $id, $role, $event, $title, $body, $url, $popup]);
  }
}

// Satu pemeriksaan gateway pada satu waktu. Dipanggil oleh polling global dan
// script CLI; tidak bergantung pada halaman pembayaran pelanggan.
