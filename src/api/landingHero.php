<?php
models('LandingHero');
require_once ROOT_PATH . '/config/api_init.php';

$res = [];
$status = 200;
$slide = $_GET['slide'] ?? null;

switch ($method) {

  // GET: Ambil
  case 'GET':
    try {
      $data = getLandingHero($slide);

      $res = [
        "success" => true,
        "data" => $data
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
