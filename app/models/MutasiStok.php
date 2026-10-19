<?php

function findMutasiStok($id)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT * FROM mutasi_stok WHERE id_mutasi = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $res;
}

function getMutasiStokList($page = 1, $limit = 10, $type = '', $search = '')
{
  $conn = db();
  $offset = ($page - 1) * $limit;

  $where = [];
  $params = [];
  $types = '';

  if ($search !== '') {
    $where[] = "(p.nama_produk LIKE ?)";
    $params[] = "%$search%";
    $types .= 's';
  }

  if ($type !== '') {
    $where[] = "ms.type = ?";
    $params[] = $type;
    $types .= 's';
  }

  $whereSql = $where ? "WHERE " . implode(' AND ', $where) : "";

  // COUNT
  $sqlCount = "SELECT COUNT(*) as total FROM mutasi_stok ms 
               LEFT JOIN produk p ON ms.kode_produk = p.kode_produk
               $whereSql";

  $stmt = $conn->prepare($sqlCount);
  if ($params) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $total = $stmt->get_result()->fetch_assoc()['total'];
  $stmt->close();

  // DATA
  $sql = "
    SELECT ms.*, p.nama_produk, p.satuan_dasar
    FROM mutasi_stok ms
    LEFT JOIN produk p ON ms.kode_produk = p.kode_produk
    $whereSql
    ORDER BY ms.tanggal DESC, ms.id_mutasi DESC
    LIMIT ? OFFSET ?
  ";

  $params[] = $limit;
  $params[] = $offset;
  $types .= 'ii';

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();

  $res = $stmt->get_result();
  $data = [];

  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();
  return [$data, $total];
}

function tambahMutasiStok($data)
{
  $conn = db();

  $sql = "
    INSERT INTO mutasi_stok 
    (kode_produk, type, jumlah, keterangan, harga_pokok, sisa_stok)
    VALUES (?, ?, ?, ?, ?, ?)
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "ssissi",
    $data['kode_produk'],
    $data['type'],
    $data['jumlah'],
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
  $conn = db();
  $stmt = $conn->prepare("DELETE FROM mutasi_stok WHERE id_mutasi = ?");
  $stmt->bind_param("i", $id);
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}

function getMutasiByProduk($kode)
{
  $conn = db();
  $stmt = $conn->prepare("
    SELECT * FROM mutasi_stok 
    WHERE kode_produk=? AND sisa_stok > 0 AND type='masuk'
    ORDER BY tanggal ASC
  ");
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

function getMutasiByProdukForUpdate($kode)
{
  $conn = db();
  $stmt = $conn->prepare("
    SELECT * FROM mutasi_stok
    WHERE kode_produk=? AND sisa_stok > 0 AND type='masuk'
    ORDER BY tanggal ASC, id_mutasi ASC
    FOR UPDATE
  ");
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

function findMutasiStokForUpdate($id)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT * FROM mutasi_stok WHERE id_mutasi = ? FOR UPDATE");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $res;
}

function ubahSisaStokMutasi($id, $stok_baru)
{
  $conn = db();
  $stmt = $conn->prepare("UPDATE mutasi_stok SET sisa_stok=? WHERE id_mutasi=?");
  $stmt->bind_param("ii", $stok_baru, $id);
  return $stmt->execute();
}


function getMutasiByProdukForReturn($kode_produk)
{
  $conn = db();

  $stmt = $conn->prepare("
        SELECT *
        FROM mutasi_stok
        WHERE kode_produk = ?
          AND type = 'masuk'
        ORDER BY tanggal DESC, id_mutasi DESC
    ");

  $stmt->bind_param("s", $kode_produk);
  $stmt->execute();

  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) {
    // stok tersisa = stok_masuk - stok_keluar
    $row['stok_masuk'] = intval($row['jumlah']);
    $data[] = $row;
  }

  $stmt->close();
  return $data;
}
