<?php

function saveSetting($name, $value)
{
  global $conn;
  $stmt = $conn->prepare("
    INSERT INTO setting (name, value)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE value = VALUES(value)
  ");
  $stmt->bind_param("ss", $name, $value);
  return $stmt->execute();
}

function getSetting($name, $default = null)
{
  global $conn;
  $stmt = $conn->prepare("SELECT value FROM setting WHERE name = ?");
  $stmt->bind_param("s", $name);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $result ? $result['value'] : $default;
}

function getAllSetting()
{
  global $conn;
  $data = [];
  $sql = "SELECT name, value FROM setting";
  $result = $conn->query($sql);
  while ($row = $result->fetch_assoc()) {
    $data[$row['name']] = $row['value'];
  }
  return $data;
}
