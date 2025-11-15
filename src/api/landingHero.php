<?php
models('LandingHero');
require_once ROOT_PATH . '/config/api_init.php';

$res = [];
$status = 200;

switch ($method) {

  // GET: Ambil semua Slide
  case 'GET':
    try {
      $data = getLandingHero();

      $res = [
        "success" => true,
        "data" => $data
      ];
    } catch (Exception $e) {
      $status = 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;


  // POST: Insert / Update Slide
  case 'POST':
    api_require(['admin']);

    try {
      $uploaded = [];
      // HANDLE FILE-GAMBAR
      foreach ($_FILES as $key => $file) {
        if ($file['error'] === 0) {
          $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
          $newName = "hero_" . time() . "_" . rand(1000, 9999) . "." . $ext;

          $uploadPath = ROOT_PATH . '/public/assets/img/' . $newName;
          move_uploaded_file($file["tmp_name"], $uploadPath);

          $uploaded[$key] = $newName;
        }
      }

      // SIMPAN DATA SLIDE
      foreach ($input_data['data'] as $slide) {
        if (isset($uploaded['img_' . $slide['id']])) {
          $slide['image_path'] = $uploaded['img_' . $slide['id']];
        }

        if ($slide['id'] === 0) {
          insertLandingHero($slide);
        } else {
          updateLandingHero($slide['id'], $slide);
        }
      }

      $res = [
        'success' => true,
        'message' => 'Carousel berhasil disimpan.'
      ];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;


  // DELETE slide
  case 'DELETE':
    api_require(['admin']);

    try {
      $id = $_GET['id'] ?? 0;
      deleteLandingHero($id);

      $res = [
        "success" => true,
        "message" => "Slide berhasil dihapus"
      ];
    } catch (Exception $e) {
      $status = 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;


  default:
    $status = 405;
    $res = ['success' => false, 'message' => 'Metode tidak didukung.'];
}

respond_json($res, $status);
