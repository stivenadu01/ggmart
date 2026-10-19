<?php

function findUser($id)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $user;
}

function getUserList($page = 1, $limit = 10, $search = '', $role = '')
{
  $conn = db();
  $offset = ($page - 1) * $limit;

  $where = [];
  $params = [];
  $types = '';

  if ($search !== '') {
    $where[] = "(nama LIKE ? OR email LIKE ?)";
    $safe = "%$search%";
    $params[] = $safe;
    $params[] = $safe;
    $types .= 'ss';
  }

  if ($role !== '') {
    $where[] = "role = ?";
    $params[] = $role;
    $types .= 's';
  }

  $whereSql = $where ? "WHERE " . implode(' AND ', $where) : '';

  // COUNT
  $sqlCount = "SELECT COUNT(*) AS total FROM pengguna $whereSql";
  $stmt = $conn->prepare($sqlCount);
  if ($params) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $total = $stmt->get_result()->fetch_assoc()['total'];
  $stmt->close();

  // DATA
  $sql = "
    SELECT id_pengguna, nama, email, no_hp, alamat, role, is_verified ,tanggal_dibuat
    FROM pengguna
    $whereSql
    ORDER BY id_pengguna DESC
    LIMIT ? OFFSET ?
  ";

  $params[] = $limit;
  $params[] = $offset;
  $types .= 'ii';

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();

  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();
  return [$data, $total];
}

function tambahUser($data, $isRegister = false)
{
  $conn = db();

  $nama = $data['nama'];
  $email = $data['email'];
  $no_hp = $data['no_hp'] ?? null;
  $password = password_hash($data['password'], PASSWORD_DEFAULT);

  $role = $data['role'] ?? 'pelanggan';

  // REGISTER MODE → pakai token
  if ($isRegister) {
    $token = bin2hex(random_bytes(32));
    $expired = date('Y-m-d H:i:s', strtotime('+1 day'));

    $stmt = $conn->prepare("
      INSERT INTO pengguna (nama, email, no_hp, password, role, verify_token, token_expired)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssssss", $nama, $email, $no_hp, $password, $role, $token, $expired);
    $stmt->execute();

    return [
      'email' => $email,
      'token' => $token
    ];
  }

  // ADMIN MODE
  $stmt = $conn->prepare("
    INSERT INTO pengguna (nama, email, no_hp, password, role, is_verified)
    VALUES (?, ?, ?, ?, ?, ?)
  ");

  $is_verified = 1; // langsung verified jika dibuat admin
  $stmt->bind_param("sssssi", $nama, $email, $no_hp, $password, $role, $is_verified);
  $res = $stmt->execute();
  $stmt->close();

  return $res;
}

function editUser($id, $data)
{
  $conn = db();

  if (empty($data['password'])) {
    $stmt = $conn->prepare("UPDATE pengguna SET nama=?, email=?, no_hp=?, alamat=?, role=? WHERE id_pengguna=?");
    $stmt->bind_param("sssssi", $data['nama'], $data['email'], $data['no_hp'], $data['alamat'], $data['role'], $id);
  } else {
    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE pengguna SET nama=?, email=?, no_hp=?, alamat=?, password=?, role=? WHERE id_pengguna=?");
    $stmt->bind_param("ssssssi", $data['nama'], $data['email'], $data['no_hp'], $data['alamat'], $hashed, $data['role'], $id);
  }

  $res = $stmt->execute();
  $stmt->close();
  return $res;
}

function hapusUser($id)
{
  $conn = db();
  $stmt = $conn->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
  $stmt->bind_param('i', $id);
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}

function findUserByEmail($email)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT * FROM pengguna WHERE email = ? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $user;
}

function findUserByToken($token)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT * FROM pengguna WHERE verify_token = ? LIMIT 1");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $user;
}

function verifyUser($id_pengguna)
{
  $conn = db();
  $stmt = $conn->prepare("UPDATE pengguna SET is_verified = 1, verify_token = NULL WHERE id_pengguna = ?");
  $stmt->bind_param("i", $id_pengguna);
  return $stmt->execute();
}

function savePasswordResetToken($email, $token, $expired)
{
  $conn = db();
  $stmt = $conn->prepare("UPDATE pengguna SET reset_token = ?, reset_expired = ? WHERE email = ?");
  $stmt->bind_param("sss", $token, $expired, $email);
  return $stmt->execute();
}

function findPasswordResetByToken($token)
{
  $conn = db();
  $stmt = $conn->prepare("SELECT * FROM pengguna WHERE reset_token = ? LIMIT 1");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $res;
}

function updateUserPassword($id_pengguna, $newPassword)
{
  $conn = db();
  $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("UPDATE pengguna SET password = ?, reset_token = NULL, reset_expired = NULL WHERE id_pengguna = ?");
  $stmt->bind_param("si", $hashed, $id_pengguna);
  return $stmt->execute();
}


// DASHBOARD
function getTopUserBelanja($limit = 5)
{
  $limit=max(1,min(100,(int)$limit));
  $stmt=db()->prepare("SELECT u.nama, COUNT(t.kode_transaksi) AS total_transaksi, SUM(t.total_harga) AS total_belanja FROM transaksi t JOIN pengguna u ON u.id_pengguna=t.id_pengguna WHERE u.role='pelanggan' AND t.status='selesai' GROUP BY t.id_pengguna,u.nama ORDER BY total_belanja DESC LIMIT ?");
  $stmt->bind_param('i',$limit); $stmt->execute(); $data=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close(); return $data;
}
