<?php
models('User');
require_once ROOT_PATH . '/config/api_init.php';

$id_user = $_GET['id'] ?? null;
$res = [];
$status = 200;

switch ($method) {
  // === GET /api/user[?id=] ===
  case 'GET':
    try {
      // Ambil semua user (dropdown)
      if (isset($_GET['mode']) && $_GET['mode'] === 'all') {
        $data = getAllUser();
        $res = ['success' => true, 'data' => $data];
        break;
      }

      // Ambil 1 user detail
      if ($id_user) {
        $user = findUser($id_user);
        if (!$user) throw new Exception('User tidak ditemukan', 404);
        $res = ['success' => true, 'data' => $user];
        break;
      }

      // Ambil list user (dengan pagination & filter)
      $page   = max(1, intval($_GET['halaman'] ?? 1));
      $limit  = max(1, intval($_GET['limit'] ?? 10));
      $search = trim($_GET['search'] ?? '');
      $role   = trim($_GET['role'] ?? '');

      [$data, $total] = getUserList($page, $limit, $search, $role);

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

  // === POST /api/user ===
  case 'POST':
    api_require(['admin']);
    try {
      // Validasi input
      if (empty($input_data['nama']) || empty($input_data['email']) || empty($input_data['password']) || empty($input_data['rePassword'])) {
        throw new Exception('Nama, email, dan password wajib diisi.', 422);
      }

      if ($input_data['password'] !== $input_data['rePassword']) {
        throw new Exception('Konfirmasi password tidak cocok.', 422);
      }

      if (!filter_var($input_data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid.', 422);
      }

      // Cek email sudah digunakan?
      if (!findUserByEmail($input_data['email'])) {
        throw new Exception('Email sudah digunakan oleh user lain.', 409);
      }

      // Default role jika kosong
      if (empty($input_data['role'])) $input_data['role'] = 'user';

      if (!tambahUser($input_data)) {
        throw new Exception('Gagal menambahkan user ke database.', 500);
      }

      $res = ['success' => true, 'message' => 'User berhasil ditambahkan'];
      $status = 201;
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  // === PUT /api/user?id=1 ===
  case 'PUT':
    api_require(['admin']);
    try {
      if (!$id_user) throw new Exception('ID user wajib diisi untuk update.', 400);

      if (empty($input_data['nama']) || empty($input_data['email']) || empty($input_data['role'])) {
        throw new Exception('Nama, email, dan role wajib diisi.', 422);
      }

      if (!filter_var($input_data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid.', 422);
      }

      // Cek duplikat email selain dirinya sendiri
      $userEmail = findUserByEmail($input_data['email'])['email'];
      if ($userEmail && $input_data['email'] !== $userEmail) {
        throw new Exception('Email sudah digunakan oleh user lain.', 409);
      }

      // Cek konfirmasi password hanya jika diisi
      if (!empty($input_data['password']) && isset($input_data['rePassword']) && $input_data['password'] !== $input_data['rePassword']) {
        throw new Exception('Konfirmasi password tidak cocok.', 422);
      }

      if (!editUser($id_user, $input_data)) {
        throw new Exception('User gagal diupdate atau tidak ditemukan.', 404);
      }

      $res = ['success' => true, 'message' => 'User berhasil diupdate'];
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  // === DELETE /api/user?id=1 ===
  case 'DELETE':
    api_require(['admin']);
    try {
      if (!$id_user) throw new Exception('ID user wajib diisi untuk delete.', 400);

      $user = findUser($id_user);
      if (!$user) throw new Exception('User tidak ditemukan.', 404);

      if (!hapusUser($id_user)) {
        throw new Exception('User gagal dihapus.', 500);
      }

      $res = ['success' => true, 'message' => 'User berhasil dihapus'];
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
