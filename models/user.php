<?php

function findUser($id)
{
  global $conn;
  $stmt = $conn->prepare("SELECT id_user, nama, email, role, tanggal_dibuat FROM user WHERE id_user = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();
  return $user;
}

function getAllUser()
{
  global $conn;
  $sql = "SELECT id_user, nama FROM user ORDER BY nama ASC";
  $res = $conn->query($sql);

  $users = [];
  while ($row = $res->fetch_assoc()) {
    $users[] = $row;
  }
  return $users;
}

function getUserList($page = 1, $limit = 10, $search = '', $role = '')
{
  global $conn;
  $offset = ($page - 1) * $limit;

  $where = [];
  if ($search !== '') {
    $safe = "%" . $conn->real_escape_string($search) . "%";
    $where[] = "(nama LIKE '$safe' OR email LIKE '$safe')";
  }
  if ($role !== '') {
    $safeRole = $conn->real_escape_string($role);
    $where[] = "role = '$safeRole'";
  }

  $whereSql = $where ? "WHERE " . implode(' AND ', $where) : '';

  // Hitung total
  $resCount = $conn->query("SELECT COUNT(*) AS total FROM user $whereSql");
  $total = $resCount->fetch_assoc()['total'];

  // Ambil data
  $res = $conn->query("
    SELECT id_user, nama, email, role, tanggal_dibuat
    FROM user
    $whereSql
    ORDER BY id_user DESC
    LIMIT $limit OFFSET $offset
  ");

  $users = [];
  while ($row = $res->fetch_assoc()) {
    $users[] = $row;
  }

  return [$users, $total];
}

function tambahUser($data)
{
  global $conn;
  $sql = "INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);

  $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
  $stmt->bind_param('ssss', $data['nama'], $data['email'], $hashed, $data['role']);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function editUser($id, $data)
{
  global $conn;

  // Jika password kosong, jangan update password
  if (empty($data['password'])) {
    $sql = "UPDATE user SET nama = ?, email = ?, role = ? WHERE id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $data['nama'], $data['email'], $data['role'], $id);
  } else {
    $sql = "UPDATE user SET nama = ?, email = ?, password = ?, role = ? WHERE id_user = ?";
    $stmt = $conn->prepare($sql);
    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt->bind_param('ssssi', $data['nama'], $data['email'], $hashed, $data['role'], $id);
  }

  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function hapusUser($id)
{
  global $conn;
  $stmt = $conn->prepare("DELETE FROM user WHERE id_user = ?");
  $stmt->bind_param('i', $id);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}

function findUserByEmail($email)
{
  global $conn;

  $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();

  $result = $stmt->get_result();
  $user = $result->fetch_assoc();

  $stmt->close();
  return $user ?: null;
}
