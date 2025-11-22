<?php
models('LandingHero');
require_once ROOT_PATH . '/config/api_init.php';
require_once ROOT_PATH . "/helpers/upload.php";

$id = $_GET['id'] ?? null;

$res = [];
$status = 200;

switch ($method) {

  // GET
  case 'GET':
    try {
      if ($id) {
        $hero = getHero($id);
        if (!$hero) throw new Exception("Data tidak ditemukan", 404);

        $res = ['success' => true, 'data' => $hero];
        break;
      }

      $data = getAllHero();
      $res = ['success' => true, 'data' => $data];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;


  // POST: CREATE (dengan upload)
  case 'POST':
    api_require(['admin']);
    try {
      if (empty($input_data['title']) && empty($input_data['subtitle'])) {
        throw new Exception("Title Atau Subtitle wajib", 422);
      }

      // Upload gambar 
      $input_data['image_path'] = uploadImageGeneral($_FILES['image'], "hero");
      if (!$input_data['image_path']) throw new Exception("Gambar Rusak Atau Tidak Lengkap", 400);
      if (!tambahHero($input_data)) {
        throw new Exception("Gagal menyimpan data", 500);
      }

      $res = ['success' => true, 'message' => "Hero berhasil ditambahkan"];
      $status = 201;
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;


  // PUT: UPDATE
  case 'PUT':
    api_require(['admin']);
    try {
      if (!$id) throw new Exception("ID diperlukan", 400);

      if (empty($input_data['title']) && empty($input_data['subtitle'])) {
        throw new Exception("Title Atau Subtitle wajib diisi", 422);
      }

      $hero = getHero($id);
      if (!$hero) throw new Exception("Data tidak ditemukan", 404);

      // Upload jika ada file baru
      if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $input_data['image_path'] = uploadImageGeneral($_FILES['image'], "hero");
        // Hapus gambar lama
        if (!empty($hero['image_path'])) {
          $old = ROOT_PATH . "/public/uploads/" . ltrim($hero['image_path'], '/');
          if (file_exists($old)) unlink($old);
        }
      } else {
        $input_data['image_path'] = $hero['image_path'];
      }

      if (!editHero($id, $input_data)) {
        throw new Exception("Gagal mengupdate data", 500);
      }

      $res = ['success' => true, 'message' => "Hero berhasil diupdate"];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;
  // DELETE
  case 'DELETE':
    api_require(['admin']);

    try {
      if (!$id) throw new Exception("ID diperlukan", 400);

      // hapus gambar
      $hero = getHero($id);
      if ($hero && $hero['image_path']) {
        $file = ROOT_PATH . "/public/uploads/" . ltrim($hero['image_path'], '/');
        if (file_exists($file)) unlink($file);
      }

      if (!hapusHero($id)) {
        throw new Exception("Gagal menghapus data", 500);
      }

      $res = ['success' => true, 'message' => "Hero berhasil dihapus"];
    } catch (Exception $e) {
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
