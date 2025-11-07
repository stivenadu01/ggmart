<?php
models('User');
require_once ROOT_PATH . '/config/api_init.php';
models('Setting');

$res = [];
$status = 200;
$name = $_GET['name'] ?? null;

switch ($method) {

  // === GET SETTINGS ===
  case 'GET':
    try {
      if ($name) {
        $data = getSetting($name);
        if (!$data) throw new Exception("Gagal mengambil data", 400);
        $res = [
          'success' => true,
          'data' => $data
        ];
        break;
      }
      $data = getAllSetting();
      if (!$data) throw new Exception("Gagal mengambil data", 1);
      $res = [
        'success' => true,
        'data' => $data
      ];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  // === UPDATE / INSERT SETTINGS ===
  case 'POST':
    api_require(['admin']);
    try {
      if (empty($input_data) || !is_array($input_data)) {
        throw new Exception('Tidak ada data yang dikirim.', 422);
      }

      foreach ($input_data as $key => $value) {
        if (!saveSetting($key, $value)) {
          throw new Exception("Gagal menyimpan pengaturan: {$key}", 500);
        }
      }

      $res = [
        'success' => true,
        'message' => 'Pengaturan berhasil disimpan.'
      ];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  default:
    $status = 405;
    $res = ['success' => false, 'message' => 'Metode tidak didukung.'];
    break;
}

respond_json($res, $status);
