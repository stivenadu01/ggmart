<?php
models('MutasiStok');
models('Produk');
require_once ROOT_PATH . '/config/api_init.php';

$id_mutasi = $_GET['id'] ?? null;

$res = [];
$status = 200;

switch ($method) {
  case 'GET':
    api_require(['admin', 'manager']);
    try {
      // Mode: dropdown
      if (isset($_GET['mode']) && $_GET['mode'] == 'dropdown' && isset($_GET['kode'])) {
        $kode = $_GET['kode'];
        $mutasi = getMutasiByProduk($conn->real_escape_string($kode));
        if (!$mutasi) throw new Exception('Mutasi List stok tidak ditemukan', 404);
        $res = ['success' => true, 'data' => $mutasi];
        break;
      }
      // Mode: detail mutasi
      if ($id_mutasi) {
        $mutasi = findMutasiStok($id_mutasi);
        if (!$mutasi) throw new Exception('Mutasi stok tidak ditemukan', 404);
        $res = ['success' => true, 'data' => $mutasi];
        break;
      }

      // Mode: list + pagination + search
      $page   = intval($_GET['halaman'] ?? 1);
      $limit  = intval($_GET['limit'] ?? 10);
      $search = trim($_GET['search'] ?? '');
      $type = trim($_GET['type'] ?? '');

      [$data, $total] = getMutasiStokList($page, $limit, $type, $search);

      $res = [
        'success' => true,
        'data' => $data,
        'pagination' => [
          'page' => $page,
          'limit' => $limit,
          'total' => intval($total),
          'total_pages' => ($limit > 0) ? ceil($total / $limit) : 1
        ]
      ];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  // POST /api/mutasiStok
  case 'POST':
    api_require(['admin']);
    global $conn;
    $conn->begin_transaction();
    try {
      $kode_produk = $input_data['kode_produk'] ?? null;
      $jumlah = $input_data['jumlah'] ?? null;
      $type = $input_data['type'] ?? null;
      $id_mutasi = $input_data['id_mutasi'] ?? null;


      if (!$kode_produk || !$type || !$jumlah) {
        throw new Exception('Produk dan Type wajib di isi.', 422);
      }

      if ($type == 'masuk') {
        if (!$input_data['harga_pokok'] || !$input_data['total_pokok']) {
          throw new Exception('Harga pokok dan Total pokok wajib di isi.', 422);
        }
        if (!tambahMutasiStok($input_data)) throw new Exception("Gagal Tambah", 500);
        if (!updateStokProduk($kode_produk)) throw new Exception("Gagal Update", 500);
      } elseif ($type == 'keluar') {
        $mutasi = findMutasiStok($id_mutasi);
        if (!$id_mutasi || !$mutasi) {
          throw new Exception('Mutasi/Batch Stok Tidak Ditemukan!', 422);
        }

        if (!ubahSisaStokMutasi($id_mutasi, $mutasi['sisa_stok'] - $jumlah)) throw new Exception("Gagal Ubah Stok", 500);


        $input_data['total_pokok'] = $input_data['jumlah'] * $input_data['harga_pokok'];
        $input_data['sisa_stok'] =  null;
        if (!tambahMutasiStok($input_data)) throw new Exception("Gagal Tambah", 500);
        if (!updateStokProduk($kode_produk)) throw new Exception("Gagal Update", 500);
      }

      $conn->commit();
      $res = ['success' => true, 'message' => 'Perubahan Stok berhasil', 'data' => $input_data];
      $status = 201;
    } catch (Exception $e) {
      $conn->rollback();
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  // DELETE /api/mutasi?k=1
  case 'DELETE':
    api_require(['admin']);
    global $conn;
    $conn->begin_transaction();
    try {
      if (!$id_mutasi) throw new Exception('ID Mutasi wajib diisi untuk delete.', 400);
      $mutasi = findMutasiStok($id_mutasi);
      if (!$mutasi) throw new Exception('Mutasi tidak ditemukan.', 404);

      $tanggal_mutasi = new DateTime($mutasi['tanggal']);
      $sekarang = new DateTime();
      // hitung selisih
      $interval = $sekarang->getTimestamp() - $tanggal_mutasi->getTimestamp();
      // lebih 1 hari maka tidak bisa hapus
      if ($interval >= 60 * 60 * 24) { // 86400 detik = 1 hari
        throw new Exception('Mutasi stok yang sudah lebih dari 1 hari tidak bisa dihapus', 400);
      }
      if ($mutasi['type'] == 'keluar') throw new Exception("Stok Keluar Tidak Bisa Dihapus", 400);

      if ($mutasi['type'] == 'masuk' && $mutasi['jumlah'] != $mutasi['sisa_stok']) {
        throw new Exception('Batch Stok ini sudah digunakan, silahkan kurangi.', 500);
      }
      if (!hapusMutasiStok($id_mutasi)) {
        throw new Exception('Mutasi Stok gagal dihapus.', 500);
      }
      if (!updateStokProduk($mutasi['kode_produk'])) throw new Exception("Gagal update stok produk", 1);

      $conn->commit();
      $res = ['success' => true, 'message' => 'Mutasi Stok berhasil dihapus'];
    } catch (Exception $e) {
      $conn->rollback();
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  default:
    $status = 405;
    $res = ['success' => false, 'message' => 'Metode tidak didukung'];
    break;
}

respond_json($res, $status);
