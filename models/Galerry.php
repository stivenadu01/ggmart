<?php

// =========================
//   GALLERY (EVENT)
// =========================

function getAllGallery()
{
  global $conn;
  $sql = "SELECT * FROM gallery ORDER BY created_at DESC";
  $res = $conn->query($sql);

  $data = [];
  while ($row = $res->fetch_assoc()) {
    $row['images'] = getGalleryImages($row['id_galery']);
    $data[] = $row;
  }

  return $data;
}

function getGallery($id_galery)
{
  global $conn;
  $stmt = $conn->prepare("SELECT * FROM gallery WHERE id_galery = ? LIMIT 1");
  $stmt->bind_param("i", $id_galery);
  $stmt->execute();
  $res = $stmt->get_result();
  $gallery = $res->fetch_assoc();
  $stmt->close();

  if ($gallery) {
    // Ambil semua image
    $gallery['images'] = getGalleryImages($id_galery);
  }

  return $gallery;
}

function tambahGallery($data)
{
  global $conn;

  $sql = "INSERT INTO gallery (title, description) VALUES (?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $data['title'], $data['description']);

  if (!$stmt->execute()) return false;
  $id = $stmt->insert_id;
  $stmt->close();

  return $id; // return ID untuk upload multiple images
}

function editGallery($id, $data)
{
  global $conn;

  $sql = "UPDATE gallery SET title = ?, description = ? WHERE id_galery = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssi", $data['title'], $data['description'], $id);

  $status = $stmt->execute();
  $stmt->close();
  return $status;
}

function hapusGallery($id)
{
  global $conn;

  // gambar otomatis kehapus karena ON DELETE CASCADE
  $stmt = $conn->prepare("DELETE FROM gallery WHERE id_galery = ?");
  $stmt->bind_param("i", $id);
  $res = $stmt->execute();
  $stmt->close();

  return $res;
}

// =========================
//     GALLERY IMAGES
// =========================

function getGalleryImages($id_gallery)
{
  global $conn;
  $stmt = $conn->prepare("SELECT * FROM gallery_images WHERE id_gallery = ? ORDER BY uploaded_at ASC");
  $stmt->bind_param("i", $id_gallery);
  $stmt->execute();
  $res = $stmt->get_result();

  $images = [];
  while ($row = $res->fetch_assoc()) {
    $images[] = $row;
  }

  $stmt->close();
  return $images;
}

function tambahGalleryImage($data)
{
  global $conn;

  $sql = "INSERT INTO gallery_images (id_gallery, kode_produk, image_path)
          VALUES (?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iss", $data['id_gallery'], $data['kode_produk'], $data['image_path']);

  $exec = $stmt->execute();
  $stmt->close();
  return $exec;
}

function hapusGalleryImage($id)
{
  global $conn;
  $stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
  $stmt->bind_param("i", $id);
  $res = $stmt->execute();
  $stmt->close();
  return $res;
}
