<?php

function getKeranjangByUser($id_pengguna)
{
  $conn = db();
  $stmt = $conn->prepare("
    SELECT k.*, p.nama_produk,p.gambar, p.harga_jual, p.stok
    FROM keranjang k
    LEFT JOIN produk p ON k.kode_produk = p.kode_produk
    WHERE k.id_pengguna = ?
  ");
  $stmt->bind_param("i", $id_pengguna);
  $stmt->execute();

  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) $data[] = $row;

  return $data;
}

function tambahKeranjang($id_pengguna, $kode_produk, $jumlah)
{
  $conn = db();
  $stmt = $conn->prepare("
    INSERT INTO keranjang (id_pengguna, kode_produk, jumlah)
    VALUES (?, ?, ?)
  ");
  $stmt->bind_param("isi", $id_pengguna, $kode_produk, $jumlah);
  return $stmt->execute();
}

function updateKeranjang($id_keranjang, $jumlah)
{
  $conn = db();

  if ($jumlah <= 0) {
    return hapusItemKeranjang($id_keranjang);
  }

  $stmt = $conn->prepare("
    UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?
  ");
  $stmt->bind_param("ii", $jumlah, $id_keranjang);
  return $stmt->execute();
}

function hapusItemKeranjang($id_keranjang)
{
  $conn = db();
  $stmt = $conn->prepare("DELETE FROM keranjang WHERE id_keranjang = ?");
  $stmt->bind_param("i", $id_keranjang);
  return $stmt->execute();
}

function clearKeranjang($id_pengguna)
{
  $conn = db();
  $stmt = $conn->prepare("DELETE FROM keranjang WHERE id_pengguna = ?");
  $stmt->bind_param("i", $id_pengguna);
  return $stmt->execute();
}

function cekKeranjang($id_pengguna, $kode_produk)
{
  $conn = db();
  $stmt = $conn->prepare('SELECT jumlah , id_keranjang FROM keranjang WHERE id_pengguna=? AND kode_produk=?');
  $stmt->bind_param('is', $id_pengguna, $kode_produk);
  $stmt->execute();
  return $stmt->get_result()->fetch_assoc();
}
