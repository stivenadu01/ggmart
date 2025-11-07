<?php
models('Kategori');
require_once ROOT_PATH . '/config/api_init.php';

$id_kategori = $_GET['id'] ?? null;
$res = [];
$status = 200;

switch ($method) {
  // GET /api/kategori[?id=]
  case 'GET':
    try {
      // Mode: semua kategori (tanpa pagination, untuk dropdown)
      if (isset($_GET['mode']) && $_GET['mode'] === 'all') {
        $data = getAllKategori();
        $res = ['success' => true, 'data' => $data];
        break;
      }

      // Mode: detail kategori
      if ($id_kategori) {
        $kategori = findKategori($id_kategori);
        if (!$kategori) throw new Exception('Kategori tidak ditemukan', 404);
        $res = ['success' => true, 'data' => $kategori];
        break;
      }

      // Mode: list + pagination + search
      $page   = max(1, intval($_GET['halaman'] ?? 1));
      $limit  = max(1, intval($_GET['limit'] ?? 10));
      $search = trim($_GET['search'] ?? '');

      [$data, $total] = getKategoriList($page, $limit, $search);

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

  // POST /api/kategori
  case 'POST':
    api_require(['admin']);
    try {
      if (empty($input_data['nama_kategori'])) {
        throw new Exception('Nama kategori wajib diisi.', 422);
      }
      if (!isset($input_data['deskripsi'])) {
        $input_data['deskripsi'] = '';
      }

      if (!tambahKategori($input_data)) {
        throw new Exception('Gagal menambahkan kategori ke database.', 500);
      }

      $res = ['success' => true, 'message' => 'Kategori berhasil ditambahkan'];
      $status = 201;
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  // PUT /api/kategori?k=1
  case 'PUT':
    api_require(['admin']);
    try {
      if (!$id_kategori) throw new Exception('ID kategori wajib diisi untuk update.', 400);
      if (empty($input_data['nama_kategori'])) {
        throw new Exception('Nama kategori wajib diisi.', 422);
      }
      if (!isset($input_data['deskripsi'])) {
        $input_data['deskripsi'] = '';
      }

      if (!editKategori($id_kategori, $input_data)) {
        throw new Exception('Kategori gagal diupdate atau tidak ditemukan.', 404);
      }

      $res = ['success' => true, 'message' => 'Kategori berhasil diupdate'];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  // DELETE /api/kategori?k=1
  case 'DELETE':
    api_require(['admin']);
    try {
      if (!$id_kategori) throw new Exception('ID kategori wajib diisi untuk delete.', 400);

      $kategori = findKategori($id_kategori);
      if (!$kategori) throw new Exception('Kategori tidak ditemukan.', 404);

      if (!hapusKategori($id_kategori)) {
        throw new Exception('Kategori gagal dihapus.', 500);
      }

      $res = ['success' => true, 'message' => 'Kategori berhasil dihapus'];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => "Coba pastikan tidak ada produk dari kategori ini"];
    }
    break;

  default:
    $status = 405;
    $res = ['success' => false, 'message' => 'Metode tidak didukung'];
    break;
}

respond_json($res, $status);
