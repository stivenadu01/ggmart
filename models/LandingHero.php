<?php

function getLandingHero($slide)
{
  global $conn;
  $sql = "SELECT * FROM landing_hero ORDER BY urutan ASC, id ASC ";
  isset($slide) ? $sql .= "LIMIT 1 OFFSET $slide" : '';
  $result = $conn->query($sql);

  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  return $data;
}
