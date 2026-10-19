<?php

function run_middleware($middlewares = [])
{
  foreach ($middlewares as $mw) {
    // AUTH MIDDLEWARE
    if ($mw === 'auth') {
      if (!isset($_SESSION['user'])) {
        response([
          'success' => false,
          'message' => 'Akses ditolak, silakan login terlebih dahulu'
        ], 401);
        exit;
      }
    }

    // ROLE MIDDLEWARE
    if (str_starts_with($mw, 'role:')) {
      $roles = substr($mw, 5);
      $allowedRoles = array_values(array_filter(array_map('trim', explode(',', $roles))));
      $user = $_SESSION['user'] ?? null;


      if (!$user || !in_array($user['role'], $allowedRoles)) {
        response([
          'success' => false,
          'message' => 'Akses ditolak, Anda tidak memiliki izin untuk mengakses sumber daya ini'
        ], 403);
        exit;
      }
    }
  }
}
