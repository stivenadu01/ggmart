<?php

class AuthController
{
  public function login()
  {
    view('auth/login', ['title' => 'Masuk | GGmart']);
  }

  public function register()
  {
    view('auth/register', ['title' => 'Daftar | GGmart']);
  }

  public function verify()
  {
    try {
      model('User');
      $token = $_GET['token'] ?? '';

      if (!$token) {
        throw new Exception("Token tidak valid", 400);
      }

      $user = findUserByToken($token);

      if (!$user) {
        throw new Exception("Token tidak ditemukan", 404);
      }

      if (strtotime($user['token_expired']) < time()) {
        throw new Exception("Token sudah kadaluarsa", 410);
      }

      verifyUser($user['id_pengguna']);

      view('auth/verify', [
        'status' => 'success',
        'message' => 'Akun berhasil diverifikasi'
      ]);
    } catch (Exception $e) {
      view('auth/verify', [
        'status' => 'error',
        'message' => $e->getMessage()
      ]);
    }
  }

  public function resetPassword()
  {
    view('auth/reset-password', ['title' => 'Kata Sandi Baru | GGmart']);
  }
}
