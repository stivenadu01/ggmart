<?php

function getAllHero()
{
  global $conn;
  $sql = "SELECT * FROM landing_hero ORDER BY urutan ASC, id DESC";
  $res = $conn->query($sql);

  $heroes = [];
  while ($row = $res->fetch_assoc()) {
    $heroes[] = $row;
  }
  return $heroes;
}

function getHero($id)
{
  global $conn;
  $stmt = $conn->prepare("SELECT * FROM landing_hero WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $hero = $res->fetch_assoc();
  $stmt->close();
  return $hero;
}

function tambahHero($data)
{
  global $conn;

  $sql = "INSERT INTO landing_hero 
          (title, subtitle, cta_primary_text, cta_primary_url,
           cta_secondary_text, cta_secondary_url, text, urutan, image_path)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "sssssssis",
    $data['title'],
    $data['subtitle'],
    $data['cta_primary_text'],
    $data['cta_primary_url'],
    $data['cta_secondary_text'],
    $data['cta_secondary_url'],
    $data['text'],
    $data['urutan'],
    $data['image_path']
  );

  return $stmt->execute();
}

function editHero($id, $data)
{
  global $conn;

  $sql = "UPDATE landing_hero SET 
          title = ?, subtitle = ?, cta_primary_text = ?, cta_primary_url = ?,
          cta_secondary_text = ?, cta_secondary_url = ?, text = ?, urutan = ?, image_path = ?
          WHERE id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "sssssssisi",
    $data['title'],
    $data['subtitle'],
    $data['cta_primary_text'],
    $data['cta_primary_url'],
    $data['cta_secondary_text'],
    $data['cta_secondary_url'],
    $data['text'],
    $data['urutan'],
    $data['image_path'],
    $id
  );

  return $stmt->execute();
}


function hapusHero($id)
{
  global $conn;
  $stmt = $conn->prepare("DELETE FROM landing_hero WHERE id = ?");
  $stmt->bind_param("i", $id);
  $result = $stmt->execute();
  $stmt->close();
  return $result;
}
