<?php

function findMutasiStok($id_mutasi)
{
  global $conn;
  $stmt = $conn->prepare("SELECT * FROM mutasi_stok WHERE id_mutasi = ?");
  $stmt->bind_param("i", $id_mutasi);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $res;
}

function getMutasiStokList($page = 1, $limit = 10, $type = '', $search = '')
{
  global $conn;
  $offset = ($page - 1) * $limit;
  $where = [];

  if ($search !== '') {
    $safe = "%" . $conn->real_escape_string($search) . "%";
    $where[] = "(ms.nama_produk LIKE '$safe')";
  }

  if ($type !== '') {
    $safeType = $conn->real_escape_string($type);
    $where[] = "ms.type = '$safeType'";
  }

  $whereClause = count($where) ? "WHERE " . implode(' AND ', $where) : "";

  $total = $conn->query("SELECT COUNT(*) AS total FROM mutasi_stok ms $whereClause")->fetch_assoc()['total'];

  $sql = "
    SELECT ms.*, p.satuan_dasar, p.nama_produk as nama_dari_produk
    FROM mutasi_stok ms
    LEFT JOIN produk p ON ms.kode_produk = p.kode_produk
    $whereClause
    ORDER BY ms.tanggal DESC
    LIMIT $limit OFFSET $offset
  ";
  $res = $conn->query($sql);
  $data = [];
  while ($row = $res->fetch_assoc()) $data[] = $row;

  return [$data, $total];
}

function tambahMutasiStok($data)
{
  global $conn;
  $sql = "INSERT INTO mutasi_stok (kode_produk, nama_produk, type, jumlah, total_pokok, keterangan, harga_pokok, sisa_stok)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "sssidsdi",
    $data['kode_produk'],
    $data['nama_produk'],
    $data['type'],
    $data['jumlah'],
    $data['total_pokok'],
    $data['keterangan'],
    $data['harga_pokok'],
    $data['sisa_stok']
  );
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}

function hapusMutasiStok($id)
{
  global $conn;
  $stmt = $conn->prepare("DELETE FROM mutasi_stok WHERE id_mutasi = ?");
  $stmt->bind_param("i", $id);
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}


// tambahan
function getMutasiByProduk($kode)
{
  global $conn;
  $stmt = $conn->prepare("SELECT * FROM mutasi_stok WHERE kode_produk=? AND sisa_stok > 0 AND type='masuk' ORDER BY tanggal ASC");
  $stmt->bind_param('s', $kode);
  $stmt->execute();
  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }
  $stmt->close();
  return $data;
}

function ubahSisaStokMutasi($id, $stok_baru)
{
  global $conn;
  return $conn->query("UPDATE mutasi_stok SET sisa_stok='$stok_baru' WHERE id_mutasi='$id'");
}
