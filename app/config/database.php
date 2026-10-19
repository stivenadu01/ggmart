<?php

function db()
{
  $host = $_ENV['DB_HOST'];
  $user = $_ENV['DB_USER'];
  $pass = $_ENV['DB_PASS'];
  $name = $_ENV['DB_NAME'];

  static $conn;
  if ($conn === null) {
    $conn = new mysqli($host, $user, $pass, $name);

    if ($conn->connect_error) {
      die("Koneksi gagal: " . $conn->connect_error);
    }


    $stmt = $conn->prepare("SET time_zone = '+08:00'");
    $stmt->execute();
    $stmt->close();
    $conn->set_charset('utf8mb4');
  }

  return $conn;
}
