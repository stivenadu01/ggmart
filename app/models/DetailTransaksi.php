<?php

function getDetailTransaksi($kode_transaksi)
{
  $conn = db();
  $stmt = $conn->prepare("
    SELECT d.*, p.nama_produk, p.gambar 
    FROM detail_transaksi d
    LEFT JOIN produk p ON d.kode_produk = p.kode_produk
    WHERE d.kode_transaksi = ?
  ");
  $stmt->bind_param("s", $kode_transaksi);
  $stmt->execute();
  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }
  $stmt->close();
  return $data;
}

function tambahDetailTransaksi($data)
{
  $conn = db();

  $sql = "INSERT INTO detail_transaksi 
    (kode_transaksi, kode_produk, jumlah, harga_satuan, harga_pokok)
    VALUES (?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);

  $stmt->bind_param(
    "ssidd",
    $data['kode_transaksi'],
    $data['kode_produk'],
    $data['jumlah'],
    $data['harga_satuan'],
    $data['harga_pokok']
  );

  $res = $stmt->execute();
  $stmt->close();
  return $res;
}

function hapusDetailTransaksi($kode_transaksi)
{
  $conn = db();
  $stmt = $conn->prepare("DELETE FROM detail_transaksi WHERE kode_transaksi = ?");
  $stmt->bind_param("s", $kode_transaksi);
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}


function updateHargaPokokDetail($kode_transaksi, $kode_produk, $harga_pokok)
{
  $conn = db();
  $stmt = $conn->prepare("UPDATE detail_transaksi SET harga_pokok = ? WHERE kode_transaksi = ? AND kode_produk = ?");
  $stmt->bind_param("dss", $harga_pokok, $kode_transaksi, $kode_produk);
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}
