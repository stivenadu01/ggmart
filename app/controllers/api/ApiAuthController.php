<?php

class ApiAuthController
{
  public function __construct()
  {
    model('User');
    require_once ROOT_PATH . '/app/helpers/rate_limit_helper.php';
  }
  public function login()
  {
    try {
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      rateLimit('login_' . $ip, 5, 60);

      $input = input();

      $email = trim($input['email'] ?? '');
      $password = $input['password'] ?? '';

      if (!$email || !$password) {
        throw new Exception("Email dan password wajib diisi", 422);
      }

      $user = findUserByEmail($email);

      if (!$user) {
        throw new Exception("Email atau password tidak valid", 400);
      }

      if (!password_verify($password, $user['password'])) {
        throw new Exception("Email atau password tidak valid", 400);
      }

      if ($user['is_verified'] == 0) {
        throw new Exception("Akun belum diverifikasi", 403);
      }

      // Regenerasi session setelah autentikasi untuk mencegah session fixation.
      session_regenerate_id(true);

      // hapus token dan verified dari data session
      unset($user['verify_token'], $user['token_expired'], $user['is_verified'], $user['password'], $user['reset_token'], $user['reset_expired']);
      // simpan session
      $_SESSION['user'] = $user;
      unset($_SESSION['notifikasi_sejak'],$_SESSION['notifikasi_sesi']);

      return response([
        'success' => true,
        'message' => 'Login berhasil',
        'data' => $_SESSION['user']
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }

  // REGISTER
  public function register()
  {
    $conn = db();
    $conn->begin_transaction();
    try {
      require_once ROOT_PATH . '/app/helpers/email_helper.php';
      $input = input();
      $nama     = trim($input['nama'] ?? '');
      $email    = trim($input['email'] ?? '');
      $password = $input['password'] ?? '';

      if (!$nama || !$email || !$password) {
        throw new Exception("Semua field wajib diisi", 422);
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email tidak valid", 422);
      }

      if (findUserByEmail($email)) {
        throw new Exception("Email sudah digunakan", 409);
      }

      $input['role'] = 'pelanggan';

      $result = tambahUser($input, true);

      // true = mode register (buat token verifikasi)
      if (!sendVerificationEmail($result['email'], $result['token'])) {
        throw new Exception("Gagal Mengirim Email Verifikasi");
      };
      $conn->commit();
      return response([
        'success' => true,
        'message' => 'Registrasi berhasil, cek email untuk verifikasi'
      ], 201);
    } catch (Exception $e) {
      $conn->rollback();
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }

  // REQUEST PASSWORD RESET
  public function requestReset()
  {
    try {
      require_once ROOT_PATH . '/app/helpers/email_helper.php';
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      rateLimit('reset_' . $ip, 3, 300); // 3x per 5 menit

      $input = input();
      $email = trim($input['email'] ?? '');

      if (!$email) {
        throw new Exception("Email wajib diisi", 422);
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email tidak valid", 422);
      }

      $user = findUserByEmail($email);

      if (!$user) {
        throw new Exception("Email tidak ditemukan", 404);
      }

      // buat token reset
      $token = bin2hex(random_bytes(16));
      savePasswordResetToken($email, $token, date('Y-m-d H:i:s', strtotime('+1 hour')));

      sendResetToken($email, $token);

      return response([
        'success' => true,
        'message' => 'Instruksi reset telah dikirim ke email Anda.'
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }

  // RESET PASSWORD
  public function resetPassword()
  {
    try {
      $input = input();

      $token = trim($input['token'] ?? '');
      $newPassword = $input['password'] ?? '';

      if (!$token || !$newPassword) {
        throw new Exception("Token dan password baru wajib diisi", 422);
      }

      $resetRequest = findPasswordResetByToken($token);

      if (!$resetRequest) {
        throw new Exception("Token tidak valid", 400);
      }

      if (strtotime($resetRequest['reset_expired']) < time()) {
        throw new Exception("Token telah expired", 400);
      }

      // update password
      updateUserPassword($resetRequest['id_pengguna'], $newPassword);

      return response([
        'success' => true,
        'message' => 'Password berhasil direset, Silahkan Login dengan password baru Anda'
      ]);
    } catch (Exception $e) {
      return response([
        'success' => false,
        'message' => $e->getMessage()
      ], $e->getCode() ?: 500);
    }
  }

  // LOGOUT
  public function logout()
  {
    unset($_SESSION['user']);
    session_destroy();

    return response([
      'success' => true,
      'message' => 'Logout berhasil'
    ]);
  }

  // ME
  public function me()
  {
    $user_id = $_SESSION['user']['id_pengguna'] ?? null;
    if (!$user_id) {
      return response(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    try {
      $user = findUser($user_id);
      if (!$user) {
        throw new Exception('User tidak ditemukan', 404);
      }
    } catch (Exception $e) {
      return response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }

    // hapus token dan verified dari data session
    unset($user['verify_token'], $user['token_expired'], $user['is_verified'], $user['password'], $user['reset_token'], $user['reset_expired']);
    // update session
    $_SESSION['user'] = $user;

    return response([
      'success' => true,
      'data' => $_SESSION['user']
    ]);
  }
}
