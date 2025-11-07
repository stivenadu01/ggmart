<?php
models('User');
require_once ROOT_PATH . '/config/api_init.php';

$res = [];
$status = 200;

// deteksi method dan parameter
$mode = isset($_GET['mode']);

switch ($method) {
  // === LOGIN ===
  case 'POST':
    try {
      if ($mode == 'login') {
        // ambil input
        $email = trim($input_data['email'] ?? '');
        $password = $input_data['password'] ?? '';
        $remember = $input_data['remember-me'] ?? false;

        // validasi
        if (!$email || !$password) {
          throw new Exception('Email dan password wajib diisi.', 422);
        }

        // cari user berdasarkan email
        $user = findUserByEmail($email);
        if (!$user) {
          throw new Exception('Email tidak terdaftar.', 404);
        }

        // verifikasi password
        if (!password_verify($password, $user['password'])) {
          throw new Exception('Password salah.', 401);
        }

        // simpan session login
        unset($user['password']);
        $_SESSION['user'] = $user;
        // simpan cookie jika remember me
        if ($remember) {
          setcookie('remember_token', md5($user['email'] . time()), time() + (7 * 24 * 60 * 60), "/");
        }

        $res = [
          'success' => true,
          'message' => 'Login berhasil! ',
          'user' => $user
        ];
        break;
      }
      throw new Exception('Aksi tidak dikenal.', 400);
    } catch (Exception $e) {
      $status = $e->getCode() ?: 500;
      $res = ['success' => false, 'message' => $e->getMessage()];
    }
    break;

  // === LOGOUT ===
  case 'DELETE':
    try {
      session_destroy();
      setcookie('remember_token', '', time() - 3600, '/');
      $res = ['success' => true, 'message' => 'Logout berhasil.'];
    } catch (Exception $e) {
      $status = 500;
      $res = ['success' => false, 'message' => 'Gagal logout.'];
    }
    break;

  default:
    $status = 405;
    $res = ['success' => false, 'message' => 'Metode tidak didukung.'];
    break;
}

respond_json($res, $status);
