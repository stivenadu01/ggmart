<?php
models('Gallery');
require_once ROOT_PATH . '/config/api_init.php';

$id = $_GET['id'] ?? null;
$img_id = $_GET['img'] ?? null;

$res = [];
$status = 200;

// Folder Upload
$uploadDir = ROOT_PATH . "/public/uploads/gallery/";
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);


// =======================
// Helper upload multiple
// =======================
function uploadMultipleImages($files, $uploadDir)
{
  $uploaded = [];

  foreach ($files['name'] as $i => $val) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) continue;

    $newName = "GAL_" . time() . "_" . rand(1000, 9999) . "." . $ext;
    $dest = $uploadDir . $newName;

    if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
      $uploaded[] = $newName;
    }
  }

  return $uploaded;
}


// ==========================
//        API ROUTES
// ==========================

switch ($method) {


  // ========================
  //        GET
  // ========================
  case 'GET':
    try {
      if ($id) {
        $data = getGallery($id);
        if (!$data) throw new Exception("Gallery tidak ditemukan", 404);

        $res = ['success' => true, 'data' => $data];
        break;
      }

      $data = getAllGallery();
      $res = ['success' => true, 'data' => $data];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;


  // ========================
  //        CREATE
  // ========================
  case 'POST':
    api_require(['admin']);
    try {
      if (empty($input_data['title'])) {
        throw new Exception("Title wajib diisi", 422);
      }

      // 1. Tambah gallery
      $newID = tambahGallery($input_data);
      if (!$newID) throw new Exception("Gagal menambahkan gallery", 500);

      // 2. Upload multiple images
      if (!empty($_FILES['images'])) {
        $uploaded = uploadMultipleImages($_FILES['images'], $uploadDir);

        foreach ($uploaded as $img) {
          tambahGalleryImage([
            'id_gallery' => $newID,
            'kode_produk' => $input_data['kode_produk'] ?? null,
            'image_path' => $img
          ]);
        }
      }

      $res = [
        'success' => true,
        'message' => "Gallery berhasil ditambahkan",
        'id' => $newID
      ];
      $status = 201;
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;


  // ========================
  //        UPDATE
  // ========================
  case 'PUT':
    api_require(['admin']);
    try {
      if (!$id) throw new Exception("ID diperlukan", 400);

      if (!editGallery($id, $input_data)) {
        throw new Exception("Gagal update gallery", 500);
      }

      $res = ['success' => true, 'message' => "Gallery berhasil diupdate"];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;


  // ========================
  //      DELETE GALLERY
  // ========================
  case 'DELETE':
    api_require(['admin']);
    try {
      if (!$id) throw new Exception("ID diperlukan", 400);

      $data = getGallery($id);

      // Hapus file fisik
      foreach ($data['images'] as $img) {
        $file = $uploadDir . $img['image_path'];
        if (file_exists($file)) unlink($file);
      }

      if (!hapusGallery($id)) {
        throw new Exception("Gagal menghapus gallery", 500);
      }

      $res = ['success' => true, 'message' => "Gallery berhasil dihapus"];
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
