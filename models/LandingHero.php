<?php

function getLandingHero()
{
  global $conn;
  $sql = "SELECT * FROM landing_hero ORDER BY urutan ASC, id ASC";
  $result = $conn->query($sql);

  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  return $data;
}

function insertLandingHero($data)
{
  global $conn;

  $stmt = $conn->prepare("
    INSERT INTO landing_hero
      (title, subtitle, text, cta_primary_text, cta_primary_url, cta_secondary_text, cta_secondary_url, image_path, urutan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");

  $stmt->bind_param(
    "ssssssssi",
    $data['title'],
    $data['subtitle'],
    $data['text'],
    $data['cta_primary_text'],
    $data['cta_primary_url'],
    $data['cta_secondary_text'],
    $data['cta_secondary_url'],
    $data['image_path'],
    $data['urutan']
  );

  return $stmt->execute();
}

function updateLandingHero($id, $data)
{
  global $conn;

  $stmt = $conn->prepare("
    UPDATE landing_hero SET
      title=?, subtitle=?, text=?, cta_primary_text=?, cta_primary_url=?,
      cta_secondary_text=?, cta_secondary_url=?, image_path=?, urutan=?
    WHERE id=?
  ");

  $stmt->bind_param(
    "ssssssssii",
    $data['title'],
    $data['subtitle'],
    $data['text'],
    $data['cta_primary_text'],
    $data['cta_primary_url'],
    $data['cta_secondary_text'],
    $data['cta_secondary_url'],
    $data['image_path'],
    $data['urutan'],
    $id
  );

  return $stmt->execute();
}

function deleteLandingHero($id)
{
  global $conn;

  $stmt = $conn->prepare("DELETE FROM landing_hero WHERE id=?");
  $stmt->bind_param("i", $id);
  return $stmt->execute();
}
