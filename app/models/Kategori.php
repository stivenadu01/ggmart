<?php

function findKategori($id)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $result;
}

function getAllKategori()
{
  $conn = db();
  $stmt = $conn->prepare("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
  $stmt->execute();
  $res = $stmt->get_result();
  $kategori = [];
  while ($row = $res->fetch_assoc()) $kategori[] = $row;
  $stmt->close();
  return $kategori;
}

function getKategoriList($page = 1, $limit = 10, $search = '')
{
  $conn = db();
  $page = max(1, (int)$page);
  $limit = max(1, min(100, (int)$limit));
  $offset = ($page - 1) * $limit;
  $params = [];
  $types = '';
  $where = '';

  if ($search !== '') {
    $where = "WHERE nama_kategori LIKE ? OR deskripsi LIKE ?";
    $safe = "%{$search}%";
    $params = [$safe, $safe];
    $types = 'ss';
  }

  $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM kategori $where");
  if ($params) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
  $stmt->close();

  $sql = "SELECT id_kategori, nama_kategori, deskripsi FROM kategori $where ORDER BY id_kategori DESC LIMIT ? OFFSET ?";
  $dataParams = [...$params, $limit, $offset];
  $dataTypes = $types . 'ii';
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($dataTypes, ...$dataParams);
  $stmt->execute();
  $res = $stmt->get_result();
  $kategori = [];
  while ($row = $res->fetch_assoc()) $kategori[] = $row;
  $stmt->close();
  return [$kategori, $total];
}

function tambahKategori($data)
{
  $conn = db();
  $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
  $stmt->bind_param("ss", $data['nama_kategori'], $data['deskripsi']);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function editKategori($id, $data)
{
  $conn = db();
  $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?");
  $stmt->bind_param("ssi", $data['nama_kategori'], $data['deskripsi'], $id);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function hapusKategori($id)
{
  $conn = db();
  $stmt = $conn->prepare("DELETE FROM kategori WHERE id_kategori = ?");
  $stmt->bind_param('i', $id);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function getLandingKategori()
{
  $conn = db();
  $sql = "
    SELECT k.id_kategori, k.nama_kategori,
      (SELECT p.gambar FROM produk p WHERE p.id_kategori = k.id_kategori ORDER BY p.terjual DESC LIMIT 1) AS image,
      (SELECT COUNT(*) FROM produk p2 WHERE p2.id_kategori = k.id_kategori) AS total_produk,
      COALESCE((SELECT SUM(p3.terjual) FROM produk p3 WHERE p3.id_kategori = k.id_kategori), 0) AS total_penjualan
    FROM kategori k
    ORDER BY total_penjualan DESC
    LIMIT 8";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) $data[] = $row;
  $stmt->close();
  return $data;
}
