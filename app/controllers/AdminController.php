<?php

class AdminController
{
  public function dashboard()
  {
    view('admin/dashboard', ['title' => 'Dashboard Admin'], 'admin');
  }

  public function kasir()
  {
    view('admin/kasir', ['title' => 'Kasir'], 'kasir');
  }

  public function pesanan()
  {
    view('admin/pesanan', ['title' => 'Pesanan'], 'admin');
  }

  public function kategori()
  {
    view('admin/kategori', ['title' => 'Kategori Produk'], 'admin');
  }

  public function produk()
  {
    view('admin/produk', ['title' => 'Produk'], 'admin');
  }

  public function produkForm()
  {
    $title = 'Tambah Produk';
    $k = query('k');
    if ($k) {
      $title = 'Ubah Produk';
    }
    view('admin/produk-form', ['title' => $title, 'kodeProduk' => $k], 'admin');
  }

  public function stok()
  {
    view('admin/stok', ['title' => 'Mutasi Stok'], 'admin');
  }

  public function stokForm()
  {
    view('admin/stok-form', ['title' => 'Tambah Perubahan Stok'], 'admin');
  }

  public function user()
  {
    view('admin/user', ['title' => 'Manajemen User'], 'admin');
  }

  public function transaksi()
  {
    view('admin/transaksi', ['title' => 'Transaksi'], ($_SESSION['user']['role'] ?? '') === 'kasir' ? 'kasir' : 'admin');
  }

  public function transaksiDetail()
  {
    view('admin/transaksi-detail', ['title' => 'Detail transaksi'], ($_SESSION['user']['role'] ?? '') === 'kasir' ? 'kasir' : 'admin');
  }

  public function laporan()
  {
    view('admin/laporan', ['title' => 'Laporan'], 'admin');
  }

  public function pengaturan()
  {
    view('admin/pengaturan', ['title' => 'Pengaturan'], 'admin');
  }
}
