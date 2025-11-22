<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
models('Produk');
require_once ROOT_PATH . '/config/api_init.php';
require_once ROOT_PATH . "/helpers/upload.php";

$kode_produk = $_GET['k'] ?? null;
$res = [];
$status = 200;

switch ($method) {
  case 'GET':
    try {
      if (isset($_GET['mode'])) {
        $search = trim($_GET['search'] ?? '');
        // Mode: trx (transaksi)
        if ($_GET['mode'] == 'trx') {
          $data = getProdukTrx($search);
          $res = ['success' => true, 'data' => $data];
          break;
        }

        // Mode: dropdown (tanpa pagination)
        if ($_GET['mode'] === 'dropdown') {
          $data = getDropdownProduk($search);
          $res = ['success' => true, 'data' => $data];
          break;
        }
      }

      // Mode: detail produk
      if ($kode_produk) {
        $produk = findProduk($kode_produk);
        if (!$produk) throw new Exception('Produk tidak ditemukan', 404);
        $res = ['success' => true, 'data' => $produk];
        break;
      }

      // Mode: list + pagination + search + sort
      $page   = max(1, intval($_GET['halaman'] ?? 1));
      $limit  = max(1, intval($_GET['limit'] ?? 10));
      $search = trim($_GET['search'] ?? '');
      $sort   = trim($_GET['sort'] ?? 'tanggal_dibuat');
      $dir    = strtoupper(trim($_GET['dir'] ?? 'DESC'));

      [$produk, $total] = getProdukList($page, $limit, $search, $sort, $dir);
      $res = [
        'success' => true,
        'data' => $produk,
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

  case 'POST':
    api_require(['admin']);
    try {
      if (empty($input_data['nama_produk']) || empty($input_data['harga_jual']) || empty($input_data['id_kategori'])) {
        throw new Exception('Nama, Harga dan kategori wajib diisi.', 422);
      }

      $timePart = substr(str_replace('.', '', microtime(true)), -8);
      $randomPart = random_int(100, 999);
      $new_kode_produk = 'PRD_' . $timePart . $randomPart;
      $input_data['kode_produk'] = $new_kode_produk;
      $input_data['stok'] = 0;
      $input_data['terjual'] = 0;
      $input_data['is_lokal'] = isset($input_data['is_lokal']) ? (int)$input_data['is_lokal'] : 0;

      // Upload dan kompres gambar
      if (!empty($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $input_data['gambar'] = uploadImageGeneral(
          $_FILES['gambar'],
          "produk",
          $new_kode_produk,   // nama file
          5,                  // max size 5MB
          true                // convert ke WEBP
        );
      }

      if (!tambahProduk($input_data)) {
        throw new Exception('Gagal menambahkan produk ke database.', 500);
      }

      $res = ['success' => true, 'message' => 'Produk berhasil ditambahkan', 'data' => $new_kode_produk];
      $status = 201;
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  case 'PUT':
    api_require(['admin']);
    try {
      if (!$kode_produk) throw new Exception('Kode produk wajib diisi untuk update.', 400);
      if (empty($input_data['nama_produk']) || empty($input_data['harga_jual'])) {
        throw new Exception('Nama dan Harga wajib diisi.', 422);
      }

      // === Upload & kompres gambar baru jika ada ===
      if (!empty($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        // Hapus gambar lama jika ada
        $produk_lama = findProduk($kode_produk);
        if (!empty($produk_lama['gambar'])) {
          $oldPath = ROOT_PATH . '/public/uploads/' . ltrim($produk_lama['gambar'], '/');
          if (file_exists($oldPath)) unlink($oldPath);
        }

        $input_data['gambar'] = uploadImageGeneral(
          $_FILES['gambar'],
          "produk",
          $kode_produk,
          5,
          true
        );
      }

      // Jalankan update produk
      if (!editProduk($kode_produk, $input_data)) {
        throw new Exception('Produk gagal diupdate atau tidak ditemukan.', 404);
      }

      $res = ['success' => true, 'message' => 'Produk berhasil diupdate'];
      $status = 200;
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  case 'DELETE':
    api_require(['admin']);
    try {
      if (!$kode_produk) throw new Exception('Kode produk wajib diisi untuk delete.', 400);

      $produk_lama = findProduk($kode_produk);
      if ($produk_lama['stok'] != 0) throw new Exception("Stok produk masih tersedia, Silahkan kurangi dulu", 400);
      if ($produk_lama && !empty($produk_lama['gambar'])) {
        $path = ROOT_PATH . '/public/uploads/' . ltrim($produk_lama['gambar'], '/');
        if (file_exists($path)) unlink($path);
      }
      if (!hapusProduk($kode_produk)) {
        throw new Exception('Produk gagal dihapus atau tidak ditemukan.', 404);
      }

      $res = ['success' => true, 'message' => 'Produk berhasil dihapus'];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  default:
    $status = 405;
    $res = ['success' => false, 'message' => 'Metode tidak didukung'];
}

respond_json($res, $status);
