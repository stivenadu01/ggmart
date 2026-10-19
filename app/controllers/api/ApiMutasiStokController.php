<?php

class ApiMutasiStokController
{
  public function __construct()
  {
    model('MutasiStok');
    model('Produk');
  }

  // === LIST MUTASI ===
  public function list()
  {
    try {
      $page   = max(1, intval($_GET['page'] ?? 1));
      $limit  = max(1, intval($_GET['limit'] ?? 10));
      $search = trim($_GET['search'] ?? '');
      $type   = trim($_GET['type'] ?? '');

      [$data, $total] = getMutasiStokList($page, $limit, $type, $search);

      return response([
        'success' => true,
        'data' => $data,
        'pagination' => [
          'page' => $page,
          'limit' => $limit,
          'total' => intval($total),
          'total_pages' => ($limit > 0) ? ceil($total / $limit) : 1
        ]
      ]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  // === SUMMARY ===
  public function summary()
  {
    try {
      $conn = db();

      $stmt = $conn->prepare("SELECT COUNT(*) AS total_produk, SUM(CASE WHEN stok > 0 AND stok <= 5 THEN 1 ELSE 0 END) AS stok_menipis, SUM(CASE WHEN stok = 0 THEN 1 ELSE 0 END) AS stok_habis FROM produk");
      $stmt->execute();
      $row = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      $stmt = $conn->prepare("SELECT COUNT(*) AS batch_aktif FROM mutasi_stok WHERE type = 'masuk' AND sisa_stok > 0");
      $stmt->execute();
      $batch = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      return response([
        'success' => true,
        'data' => [
          'total_produk' => (int)($row['total_produk'] ?? 0),
          'stok_menipis' => (int)($row['stok_menipis'] ?? 0),
          'stok_habis' => (int)($row['stok_habis'] ?? 0),
          'batch_aktif' => (int)($batch['batch_aktif'] ?? 0)
        ]
      ]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  // === DETAIL ===
  public function detail()
  {
    try {
      $id = $_GET['id'] ?? null;
      if (!$id) throw new Exception('ID mutasi tidak valid', 400);

      $mutasi = findMutasiStok($id);
      if (!$mutasi) throw new Exception('Mutasi stok tidak ditemukan', 404);

      return response(['success' => true, 'data' => $mutasi]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  // === DROPDOWN (BATCH STOK) ===
  public function dropdown()
  {
    try {
      $kode = $_GET['kode'] ?? null;
      if (!$kode) throw new Exception('Kode produk tidak valid', 400);

      $data = getMutasiByProduk($kode);
      if (!$data) throw new Exception('Mutasi List stok tidak ditemukan', 404);

      return response(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  // === TAMBAH MUTASI ===
  public function tambah()
  {
    $conn = db();
    $conn->begin_transaction();

    try {
      $input = input();

      $kode_produk = $input['kode_produk'] ?? null;
      $jumlah      = $input['jumlah'] ?? null;
      $type        = $input['type'] ?? null;
      $id_mutasi   = $input['id_mutasi'] ?? null;

      if (!$kode_produk || !$type) {
        throw new Exception('Produk dan Type wajib di isi.', 422);
      }
      $jumlah = filter_var($jumlah, FILTER_VALIDATE_INT);
      if ($jumlah === false || $jumlah <= 0) {
        throw new Exception('Jumlah stok harus berupa bilangan bulat lebih dari 0.', 422);
      }
      if (!in_array($type, ['masuk', 'keluar'], true)) {
        throw new Exception('Type mutasi stok tidak valid.', 422);
      }
      $produk = findProduk($kode_produk);
      if (!$produk) throw new Exception('Produk tidak ditemukan.', 404);
      lockProducts([$kode_produk]);

      if ($type == 'masuk') {
        if (!isset($input['harga_pokok']) || !is_numeric($input['harga_pokok']) || (float)$input['harga_pokok'] < 0) {
          throw new Exception('Harga pokok wajib diisi dengan nilai yang valid.', 422);
        }
        $input['jumlah'] = $jumlah;
        $input['harga_pokok'] = round((float)$input['harga_pokok'], 2);
        $input['sisa_stok'] = $jumlah;

        if (!tambahMutasiStok($input)) throw new Exception("Gagal Tambah", 500);
        if (!updateStokProduk($kode_produk)) throw new Exception("Gagal Update", 500);
      } elseif ($type == 'keluar') {
        refreshAvailableStock($kode_produk);
        if (cekStokProduk($kode_produk) < $jumlah) throw new Exception('Stok tersedia tidak cukup; sebagian stok sedang dipesan pelanggan.', 422);
        if (!$id_mutasi) {
          throw new Exception('ID batch stok wajib diisi.', 422);
        }
        $mutasi = findMutasiStokForUpdate((int)$id_mutasi);

        if (!$mutasi || $mutasi['type'] !== 'masuk' || $mutasi['kode_produk'] !== $kode_produk) {
          throw new Exception('Mutasi/Batch Stok tidak valid.', 422);
        }
        if ((int)$mutasi['sisa_stok'] < $jumlah) {
          throw new Exception('Stok batch tidak mencukupi.', 422);
        }

        $input['jumlah'] = $jumlah;
        $input['sisa_stok'] = null;
        $input['harga_pokok'] = $mutasi['harga_pokok'];

        if (!ubahSisaStokMutasi($id_mutasi, (int)$mutasi['sisa_stok'] - $jumlah)) {
          throw new Exception("Gagal Ubah Stok", 500);
        }

        $input['sisa_stok'] = null;

        if (!tambahMutasiStok($input)) throw new Exception("Gagal Tambah", 500);
        if (!updateStokProduk($kode_produk)) throw new Exception("Gagal Update", 500);
      }

      $conn->commit();

      return response([
        'success' => true,
        'message' => 'Perubahan Stok berhasil',
        'data' => $input
      ], 201);
    } catch (Exception $e) {
      $conn->rollback();
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  // === DELETE ===
  public function hapus()
  {
    $conn = db();
    $conn->begin_transaction();

    try {
      $id = request('id');
      if (!$id) throw new Exception('ID Mutasi wajib diisi.', 400);

      $mutasi = findMutasiStok($id);
      if (!$mutasi) throw new Exception('Mutasi tidak ditemukan.', 404);
      lockProducts([$mutasi['kode_produk']]);
      $mutasi = findMutasiStokForUpdate($id);
      if (!empty($mutasi['kode_transaksi'])) throw new Exception('Mutasi transaksi tidak dapat dihapus.', 409);
      if (oq('SELECT 1 FROM reservasi_stok WHERE kode_produk=? LIMIT 1', [$mutasi['kode_produk']])->fetch_assoc()) throw new Exception('Produk sedang dipesan; batch tidak dapat dihapus.', 409);

      $tanggal_mutasi = new DateTime($mutasi['tanggal']);
      $sekarang = new DateTime();

      $interval = $sekarang->getTimestamp() - $tanggal_mutasi->getTimestamp();

      if ($interval >= 86400) {
        throw new Exception('Mutasi stok yang sudah lebih dari 1 hari tidak bisa dihapus', 400);
      }

      if ($mutasi['type'] == 'keluar') {
        throw new Exception("Stok Keluar Tidak Bisa Dihapus", 400);
      }

      if ($mutasi['type'] == 'masuk' && $mutasi['jumlah'] != $mutasi['sisa_stok']) {
        throw new Exception('Batch Stok ini sudah digunakan, silahkan kurangi.', 400);
      }

      if (!hapusMutasiStok($id)) {
        throw new Exception('Mutasi Stok gagal dihapus.', 500);
      }

      if (!updateStokProduk($mutasi['kode_produk'])) {
        throw new Exception("Gagal update stok produk", 500);
      }

      $conn->commit();

      return response(['success' => true, 'message' => 'Mutasi Stok berhasil dihapus']);
    } catch (Exception $e) {
      $conn->rollback();
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }
}
