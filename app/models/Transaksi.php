<?php

function getTransaksiList(
  $page = 1,
  $limit = 10,
  $search = null,
  $start = null,
  $end = null,
  $metode = null,
  $user = null,
  $status = null,
  $tahap = null,
  $sumber = null,
  $includeStageCounts = false
) {
  $conn = db();
  $page = max(1, (int)$page);
  $limit = max(1, min(100, (int)$limit));
  $offset = ($page - 1) * $limit;

  $conditions = [];
  $params = [];
  $types = '';

  if ($search) {
    $conditions[] = "(t.kode_transaksi LIKE ? OR EXISTS (
      SELECT 1 FROM detail_transaksi d
      LEFT JOIN produk p ON d.kode_produk = p.kode_produk
      WHERE d.kode_transaksi = t.kode_transaksi AND p.nama_produk LIKE ?
    ))";
    $like = "%{$search}%";
    $params[] = $like; $params[] = $like; $types .= 'ss';
  }

  if ($start && $end) {
    $conditions[] = "t.tanggal_transaksi BETWEEN ? AND ?";
    $params[] = $start . ' 00:00:00'; $params[] = $end . ' 23:59:59'; $types .= 'ss';
  } elseif ($start) {
    $conditions[] = "t.tanggal_transaksi >= ?";
    $params[] = $start . ' 00:00:00'; $types .= 's';
  } elseif ($end) {
    $conditions[] = "t.tanggal_transaksi <= ?";
    $params[] = $end . ' 23:59:59'; $types .= 's';
  }

  if ($metode) {
    $conditions[] = "t.metode_bayar = ?";
    $params[] = $metode; $types .= 's';
  }

  if ($user) {
    $conditions[] = "t.id_pengguna = ?";
    $params[] = (int)$user; $types .= 'i';
  }

  if ($sumber) { $conditions[]='t.sumber_transaksi = ?'; $params[]=$sumber; $types.='s'; }
  // Ringkasan penjualan memakai filter umum, tanpa tahap/status pesanan.
  $sumConditions=$conditions; $sumParams=$params; $sumTypes=$types;

  if ($status) {
    $statuses = array_values(array_filter(array_map('trim', explode(',', $status))));
    if ($statuses) {
      $conditions[] = 't.status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
      foreach ($statuses as $st) { $params[] = $st; $types .= 's'; }
    }
  }

  $stageConditions=[
      'menunggu_pembayaran'=>"t.status='pending' AND t.status_bayar IN ('belum_bayar','ditolak')",
      'menunggu_verifikasi'=>"t.status='pending' AND t.status_bayar='menunggu_verifikasi'",
      'diproses'=>"(t.status='diproses' OR (t.status='pending' AND t.status_bayar='lunas'))",
      'dikirim'=>"t.status='dikirim'", 'selesai'=>"t.status='selesai'", 'dibatalkan'=>"t.status='dibatalkan'"
  ];
  // Jumlah per tab memakai filter yang sama, tanpa tahap dan tanpa paginasi.
  $stageCounts = [];
  if ($includeStageCounts) {
    $countWhere = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $countColumns = [];
    foreach ($stageConditions as $stage => $condition) {
      $countColumns[] = "COALESCE(SUM(CASE WHEN ($condition) THEN 1 ELSE 0 END), 0) AS $stage";
    }
    $stmt = $conn->prepare('SELECT ' . implode(', ', $countColumns) . " FROM transaksi t $countWhere");
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stageCounts = array_map('intval', $stmt->get_result()->fetch_assoc());
    $stmt->close();
  }

  if ($tahap) {
    if (isset($stageConditions[$tahap])) $conditions[]='(' . $stageConditions[$tahap] . ')';
  }

  $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

  $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM transaksi t $where");
  if ($params) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  $sqlData = "SELECT t.*, u.nama AS user, u.role AS user_role, u.no_hp AS user_phone
    FROM transaksi t LEFT JOIN pengguna u ON t.id_pengguna = u.id_pengguna
    $where ORDER BY t.tanggal_transaksi DESC LIMIT ? OFFSET ?";
  $dataParams = [...$params, $limit, $offset];
  $dataTypes = $types . 'ii';
  $stmt = $conn->prepare($sqlData);
  $stmt->bind_param($dataTypes, ...$dataParams);
  $stmt->execute();
  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) $data[] = $row;
  $stmt->close();

  array_unshift($sumConditions, "t.status = 'selesai'");
  $sumWhere = 'WHERE ' . implode(' AND ', $sumConditions);
  $stmt = $conn->prepare("SELECT SUM(t.total_pokok) AS pokok, SUM(t.total_harga) AS jual, SUM(t.total_harga - t.total_pokok) AS laba FROM transaksi t $sumWhere");
  if ($sumParams) $stmt->bind_param($sumTypes, ...$sumParams);
  $stmt->execute();
  $totalSummary = $stmt->get_result()->fetch_assoc() ?? ['pokok'=>0,'jual'=>0,'laba'=>0];
  $stmt->close();

  return [$data, $total, $totalSummary, $stageCounts];
}


function findTransaksi($kode_transaksi)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT t.*, u.nama AS user, u.role AS user_role, u.no_hp AS user_phone FROM transaksi t LEFT JOIN pengguna u ON t.id_pengguna=u.id_pengguna WHERE kode_transaksi = ?");
  $stmt->bind_param("s", $kode_transaksi);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $res;
}

function findTransaksiForUpdate($kode_transaksi)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT t.*, u.nama AS user, u.role AS user_role, u.no_hp AS user_phone FROM transaksi t LEFT JOIN pengguna u ON t.id_pengguna=u.id_pengguna WHERE t.kode_transaksi = ? FOR UPDATE");
  $stmt->bind_param("s", $kode_transaksi);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $res;
}

function tambahTransaksi($data)
{
  $conn = db();
  $sql = "INSERT INTO transaksi (kode_transaksi, id_pengguna, total_harga, total_pokok, status, metode_bayar)
          VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "siddss",
    $data['kode_transaksi'],
    $data['id_pengguna'],
    $data['total_harga'],
    $data['total_pokok'],
    $data['status'],
    $data['metode_bayar']
  );
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}

function hapusTransaksi($kode_transaksi)
{
  $conn = db();
  $stmt = $conn->prepare("DELETE FROM transaksi WHERE kode_transaksi = ?");
  $stmt->bind_param("s", $kode_transaksi);
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}

function updateStatusTransaksi($kode_transaksi, $status, $total_pokok = null)
{
  $conn = db();
  if ($total_pokok === null) {
    $stmt = $conn->prepare("UPDATE transaksi SET status = ? WHERE kode_transaksi = ?");
    $stmt->bind_param("ss", $status, $kode_transaksi);
  } else {
    $stmt = $conn->prepare("UPDATE transaksi SET status = ?, total_pokok = ? WHERE kode_transaksi = ?");
    $stmt->bind_param("sds", $status, $total_pokok, $kode_transaksi);
  }
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}





// DASHBOARD
function scalarPrepared(string $sql, string $types = '', array $params = [], string $field = 'total')
{
  $conn = db();
  $stmt = $conn->prepare($sql);
  if ($params) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $row[$field] ?? 0;
}

function sumPenjualanByDate($date)
{
  return scalarPrepared("SELECT SUM(total_harga) AS total FROM transaksi WHERE DATE(tanggal_transaksi) = ? AND status='selesai'", 's', [$date]);
}
function sumLabaByDate($date)
{
  return scalarPrepared("SELECT SUM((dt.harga_satuan - dt.harga_pokok) * dt.jumlah) AS laba FROM detail_transaksi dt JOIN transaksi t ON t.kode_transaksi=dt.kode_transaksi WHERE DATE(t.tanggal_transaksi)=? AND t.status='selesai'", 's', [$date], 'laba');
}
function countTransaksiByDate($date)
{
  return scalarPrepared("SELECT COUNT(*) AS total FROM transaksi WHERE DATE(tanggal_transaksi)=? AND status='selesai'", 's', [$date]);
}
function getPenjualanTrend7Hari()
{
  $stmt = db()->prepare("SELECT DATE(tanggal_transaksi) AS tanggal, SUM(total_harga) AS penjualan, SUM(total_harga-total_pokok) AS laba FROM transaksi WHERE tanggal_transaksi >= DATE(NOW() - INTERVAL 6 DAY) AND status='selesai' GROUP BY DATE(tanggal_transaksi) ORDER BY tanggal ASC");
  $stmt->execute(); $data=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $data;
}
function sumOmzetByMonth($month)
{
  return scalarPrepared("SELECT SUM(total_harga) AS total FROM transaksi WHERE DATE_FORMAT(tanggal_transaksi,'%Y-%m')=? AND status='selesai'", 's', [$month]);
}
function sumLabaByMonth($month)
{
  return scalarPrepared("SELECT SUM(total_harga-total_pokok) AS laba FROM transaksi WHERE DATE_FORMAT(tanggal_transaksi,'%Y-%m')=? AND status='selesai'", 's', [$month], 'laba');
}
function getJamRamaiTransaksi()
{
  $stmt = db()->prepare("SELECT HOUR(tanggal_transaksi) AS jam, COUNT(*) AS total FROM transaksi WHERE status='selesai' GROUP BY HOUR(tanggal_transaksi) ORDER BY total DESC");
  $stmt->execute(); $data=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $data;
}
