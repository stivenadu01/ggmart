<?php

class ApiProdukController
{
  public function __construct()
  {
    model('Produk');
    expireOrders();
  }

  public function list()
  {
    try {
      $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
      $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
      $search = isset($_GET['search']) ? trim($_GET['search']) : '';
      $order_by = isset($_GET['order_by']) ? trim($_GET['order_by']) : 'tanggal_dibuat';
      $order_dir = isset($_GET['order_dir']) ? trim($_GET['order_dir']) : 'DESC';

      [$data, $total] = getProdukList($page, $limit, $search, $order_by, $order_dir);
      return response([
        'success' => true,
        'data' => $data,
        'pagination' => [
          'page' => $page,
          'limit' => $limit,
          'total' => $total
        ]
      ]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], (int)$e->getCode() ?: 500);
    }
  }

  public function trx()
  {
    try {
      $search = isset($_GET['search']) ? trim($_GET['search']) : '';
      $data = getProdukTrx($search);
      return response(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function dropdown()
  {
    try {
      $search = query('search');
      $data = getDropdownProduk($search);
      return response(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function public()
  {
    try {
      $limit    = max(1, intval($_GET['limit'] ?? 12));
      $offset   = max(0, intval($_GET['offset'] ?? 0));
      $search   = trim($_GET['search'] ?? '');
      $kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : null;
      $lokal    = isset($_GET['lokal']) ? (int)$_GET['lokal'] : null;
      $sort     = trim($_GET['sort'] ?? 'rekomendasi');

      // ambil +1 untuk cek has_more
      $data = getProdukPublic(
        $limit + 1,
        $offset,
        $search,
        $kategori,
        $lokal,
        $sort
      );

      $hasMore = count($data) > $limit;

      if ($hasMore) {
        array_pop($data); // buang data ekstra
      }

      $res = [
        'success' => true,
        'data' => $data,
        'has_more' => $hasMore,
        'next_offset' => $offset + $limit
      ];
      return response(['success' => true, 'data' => $res]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function landing()
  {
    try {
      $data = getProdukLanding();
      return response(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function terkait()
  {
    try {
      $kode_produk = isset($_GET['k']) ? trim($_GET['k']) : '';
      if ($kode_produk === '') {
        throw new Exception("Kode produk tidak valid", 400);
      }
      $produk = findProduk($kode_produk);
      if (!$produk) {
        throw new Exception("Produk tidak ditemukan", 404);
      }

      $data = getProdukTerkait($kode_produk, $produk['id_kategori']);
      return response(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function detail()
  {
    try {
      $kode_produk = isset($_GET['k']) ? trim($_GET['k']) : '';
      if ($kode_produk === '') {
        throw new Exception("Kode produk tidak valid", 400);
      }
      $data = findProduk($kode_produk);
      if (!$data) {
        throw new Exception("Produk tidak ditemukan", 404);
      }
      return response(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function tambah()
  {
    $conn = db();
    $newImage = null;
    try {
      require_once ROOT_PATH . "/app/helpers/upload.php";

      $input = input();

      if (empty($input['nama_produk']) || empty($input['harga_jual']) || empty($input['id_kategori'])) {
        throw new Exception('Nama, Harga dan kategori wajib diisi.', 422);
      }

      $timePart = substr(str_replace('.', '', microtime(true)), -8);
      $randomPart = random_int(100, 999);
      $new_kode_produk = 'PRD_' . $timePart . $randomPart;
      $input['kode_produk'] = $new_kode_produk;
      $input['stok'] = 0;
      $input['terjual'] = 0;
      $input['asal_produk'] = isset($input['asal_produk']) ? trim($input['asal_produk']) : null;
      $input['tagline'] = isset($input['tagline']) ? trim($input['tagline']) : null;

      $conn->begin_transaction();
      if (!empty($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $newImage = uploadImageGeneral($_FILES['gambar'], 'produk', $new_kode_produk, 5);
        $input['gambar'] = $newImage;
      } else {
        $input['gambar'] = null;
      }

      if (!tambahProduk($input)) throw new Exception('Gagal menambahkan produk ke database.', 500);
      $conn->commit();
      return response(['success' => true, 'message' => 'Produk berhasil ditambahkan', 'data' => $new_kode_produk], 201);
    } catch (Throwable $e) {
      try { $conn->rollback(); } catch (Throwable $ignored) {}
      if (!empty($newImage)) deleteUploadedFile($newImage);
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function ubah()
  {
    $conn = db();
    $newImage = null;
    $oldImage = null;
    try {
      require_once ROOT_PATH . "/app/helpers/upload.php";
      $input = input();
      if (empty($input['kode_produk'])) throw new Exception('Kode produk wajib diisi.', 422);

      $kode_produk = trim($input['kode_produk']);
      $produk = findProduk($kode_produk);
      if (!$produk) throw new Exception('Produk tidak ditemukan', 404);
      $oldImage = $produk['gambar'] ?? null;

      if (!empty($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $newImage = uploadImageGeneral($_FILES['gambar'], 'produk', $kode_produk, 5);
        $input['gambar'] = $newImage;
      }

      $conn->begin_transaction();
      if (!editProduk($kode_produk, $input)) throw new Exception('Gagal memperbarui produk.', 500);
      $conn->commit();

      if ($newImage && $oldImage && $oldImage !== $newImage) deleteUploadedFile($oldImage);
      return response(['success' => true, 'message' => 'Produk berhasil diperbarui']);
    } catch (Throwable $e) {
      try { $conn->rollback(); } catch (Throwable $ignored) {}
      if ($newImage) deleteUploadedFile($newImage);
      return response(['success' => false, 'message' => $e->getMessage()], (int)$e->getCode() ?: 500);
    }
  }

  public function hapus()
  {
    $conn = db();
    try {
      require_once ROOT_PATH . "/app/helpers/upload.php";
      $kode_produk = request('kode_produk');
      if (empty($kode_produk)) throw new Exception('Kode produk wajib diisi.', 422);

      $produk = findProduk($kode_produk);
      if (!$produk) throw new Exception('Produk tidak ditemukan', 404);

      $conn->begin_transaction();
      if (!hapusProduk($kode_produk)) throw new Exception('Gagal menghapus produk.', 500);
      $conn->commit();

      deleteUploadedFile($produk['gambar'] ?? null);
      return response(['success' => true, 'message' => 'Produk berhasil dihapus']);
    } catch (Throwable $e) {
      try { $conn->rollback(); } catch (Throwable $ignored) {}
      return response(['success' => false, 'message' => $e->getMessage()], (int)$e->getCode() ?: 500);
    }
  }

}
