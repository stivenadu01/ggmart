<?php

class ApiKategoriController
{
  public function __construct()
  {
    model('kategori');
  }

  public function all()
  {
    try {
      $data = getAllKategori();
      return response(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function list()
  {
    try {
      $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
      $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
      $search = isset($_GET['search']) ? trim($_GET['search']) : '';

      list($data, $total) = getKategoriList($page, $limit, $search);
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
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function detail()
  {
    try {
      $id_kategori = isset($_GET['id']) ? intval($_GET['id']) : null;
      if (!$id_kategori) throw new Exception('ID kategori wajib diisi.', 400);

      $kategori = findKategori($id_kategori);
      if (!$kategori) throw new Exception('Kategori tidak ditemukan.', 404);

      return response(['success' => true, 'data' => $kategori]);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function tambah()
  {
    try {
      $input = input();

      if (empty($input['nama_kategori'])) {
        throw new Exception('Nama kategori wajib diisi.', 422);
      }
      if (!isset($input['deskripsi'])) {
        $input['deskripsi'] = '';
      }

      if (!tambahKategori($input)) {
        throw new Exception('Gagal menambahkan kategori ke database.', 500);
      }
      return response(['success' => true, 'message' => 'Kategori berhasil ditambahkan'], 201);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function ubah()
  {
    try {
      $id_kategori = isset($_GET['id']) ? intval($_GET['id']) : null;
      $input = input();

      if (!$id_kategori) throw new Exception('ID kategori wajib diisi untuk update.', 400);
      if (empty($input['nama_kategori'])) {
        throw new Exception('Nama kategori wajib diisi.', 422);
      }
      if (!isset($input['deskripsi'])) {
        $input['deskripsi'] = '';
      }

      if (!editKategori($id_kategori, $input)) {
        throw new Exception('Kategori gagal diupdate atau tidak ditemukan.', 404);
      }

      return response(['success' => true, 'message' => 'Kategori berhasil diupdate']);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function hapus()
  {
    try {
      $id_kategori = isset($_GET['id']) ? intval($_GET['id']) : null;
      if (!$id_kategori) throw new Exception('ID kategori wajib diisi untuk delete.', 400);

      $kategori = findKategori($id_kategori);
      if (!$kategori) throw new Exception('Kategori tidak ditemukan.', 404);

      if (!hapusKategori($id_kategori)) {
        throw new Exception('Kategori gagal dihapus.', 500);
      }
      return response(['success' => true, 'message' => 'Kategori berhasil dihapus']);
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }
}
