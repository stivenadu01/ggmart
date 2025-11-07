<?php

function has_role($roles = [])
{
  if (!isset($_SESSION['user']['role'])) {
    return false;
  }
  $userRole = $_SESSION['user']['role'];
  return in_array($userRole, $roles);
}

function api_require($roles = [])
{
  if (!has_role($roles)) {
    respond_json(['success' => false, 'message' => 'Akses ditolak'], 403);
    exit;
  }
}

function page_require($roles = [])
{
  if (!isset($_SESSION['user'])) {
    redirect('/auth/login');
    exit;
  }

  if (!has_role($roles)) {
    redirect_back('/auth/login'); // atau bisa ke halaman "403 Forbidden"
    exit;
  }
}
