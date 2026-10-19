<?php

// kategori
get('/api/kategori', 'ApiKategoriController@all');
get('/api/kategori/list', 'ApiKategoriController@list');
get('/api/kategori/detail', 'ApiKategoriController@detail');

post('/api/kategori', 'ApiKategoriController@tambah', ["role:admin"]);
put('/api/kategori', 'ApiKategoriController@ubah', ["role:admin"]);
delete('/api/kategori', 'ApiKategoriController@hapus', ["role:admin"]);


// produk
get('/api/produk/list', 'ApiProdukController@list', ["role:admin,pimpinan"]);
get('/api/produk/terkait', 'ApiProdukController@terkait');
get('/api/produk/detail', 'ApiProdukController@detail');
get('/api/produk/trx', 'ApiProdukController@trx', ["role:admin,kasir"]);
get('/api/produk/dropdown', 'ApiProdukController@dropdown', ["role:admin,pimpinan"]);
get('/api/produk/public', 'ApiProdukController@public');
get('/api/produk/landing', 'ApiProdukController@landing');

post('/api/produk', 'ApiProdukController@tambah', ["role:admin"]);
put('/api/produk', 'ApiProdukController@ubah', ["role:admin"]);
delete('/api/produk', 'ApiProdukController@hapus', ["role:admin"]);


// user
get('/api/user/list', 'ApiUserController@list', ["role:admin"]);
get('/api/user/detail', 'ApiUserController@detail', ["auth"]);
post('/api/user', 'ApiUserController@tambah', ["role:admin"]);
put('/api/user', 'ApiUserController@ubah', ["auth"]);
put('/api/user/{id}/verify', 'ApiUserController@verify', ['role:admin']);
delete('/api/user', 'ApiUserController@hapus', ["role:admin"]);


// mutasi stok
get('/api/mutasi/list', 'ApiMutasiStokController@list', ["role:admin,pimpinan"]);
get('/api/mutasi/summary', 'ApiMutasiStokController@summary', ["role:admin,pimpinan"]);
get('/api/mutasi/detail', 'ApiMutasiStokController@detail', ["role:admin,pimpinan"]);
get('/api/mutasi/dropdown', 'ApiMutasiStokController@dropdown', ["role:admin,pimpinan"]);

post('/api/mutasi', 'ApiMutasiStokController@tambah', ["role:admin"]);
delete('/api/mutasi', 'ApiMutasiStokController@hapus', ["role:admin"]);


// Transaksi
get('/api/transaksi/list', 'ApiTransaksiController@list', ["auth"]);
get('/api/transaksi/detail', 'ApiTransaksiController@detail', ["auth"]);

post('/api/transaksi', 'ApiTransaksiController@tambah_transaksi', ["role:admin,kasir"]); // admin dan kasir
post('/api/transaksi/user', 'ApiTransaksiController@tambah_transaksi_user', ["role:pelanggan"]); // pesanan pelanggan

post('/api/transaksi/proses', 'ApiTransaksiController@proses_transaksi', ["role:admin,kasir"]); // proses
post('/api/transaksi/kirim', 'ApiTransaksiController@kirim_transaksi', ['role:admin']);
post('/api/transaksi/siap-ambil', 'ApiTransaksiController@siap_ambil', ['role:admin']);
post('/api/transaksi/konfirmasi', 'ApiTransaksiController@konfirmasi_transaksi', ["role:admin,kasir"]); // selesai

post('/api/transaksi/batal', 'ApiTransaksiController@batal_transaksi', ["role:admin,kasir"]); // admin & kasir
post('/api/transaksi/batal-pending', 'ApiTransaksiController@batal_transaksi_pending', ["role:pelanggan"]); // pending pelanggan


// Keranjang
get('/api/keranjang', 'ApiKeranjangController@list', ["role:pelanggan"]);

post('/api/keranjang', 'ApiKeranjangController@tambah', ["role:pelanggan"]);
put('/api/keranjang', 'ApiKeranjangController@update', ["role:pelanggan"]);

delete('/api/keranjang', 'ApiKeranjangController@hapus', ["role:pelanggan"]);
delete('/api/keranjang/clear', 'ApiKeranjangController@clear', ["role:pelanggan"]);


// Auth
get('/api/auth/me', 'ApiAuthController@me', ["auth"]);
post('/api/auth/login', 'ApiAuthController@login');
post('/api/auth/register', 'ApiAuthController@register');

post('/api/auth/logout', 'ApiAuthController@logout', ["auth"]);
post('/api/auth/request-reset', 'ApiAuthController@requestReset');
post('/api/auth/reset-password', 'ApiAuthController@resetPassword');


// laporan
get('/api/laporan/mutasi-stok', 'ApiLaporanController@mutasiStok', ['role:pimpinan']);
get('/api/laporan/transaksi', 'ApiLaporanController@transaksi', ['role:pimpinan']);

// dashboard
get('/api/dashboard/summary', 'ApiDashboardController@summary', ['role:admin,pimpinan']);
get('/api/dashboard/analytics', 'ApiDashboardController@analytics', ['role:admin,pimpinan']);

get('/api/transaksi/badge', 'ApiTransaksiController@badge', ['role:admin,pimpinan,kasir']);
get('/api/notifikasi', 'ApiNotifikasiController@list', ['role:admin,kasir,pelanggan']);
post('/api/notifikasi/baca', 'ApiNotifikasiController@read', ['role:admin,kasir,pelanggan']);
post('/api/notifikasi/klaim', 'ApiNotifikasiController@claim', ['role:admin,kasir,pelanggan']);
get('/api/kasir/pesanan', 'ApiTransaksiController@pesanan_kasir', ['role:admin,kasir']);

get('/api/checkout/config', 'ApiPengaturanController@checkout', ['auth']);
get('/api/pengaturan/toko', 'ApiPengaturanController@detail', ['role:admin']);
put('/api/pengaturan/toko', 'ApiPengaturanController@simpan', ['role:admin']);
put('/api/pengaturan/pembayaran', 'ApiPengaturanController@pembayaran', ['role:admin']);
put('/api/pengaturan/pengiriman', 'ApiPengaturanController@pengiriman', ['role:admin']);
post('/api/pembayaran/bukti', 'ApiPembayaranController@bukti', ['role:admin,kasir,pelanggan']);
get('/api/pembayaran/bukti/{kode}', 'ApiPembayaranController@gambar', ['role:admin,kasir,pelanggan']);
post('/api/pembayaran/tunai', 'ApiPembayaranController@tunai', ['role:admin,kasir']);
post('/api/pembayaran/verifikasi', 'ApiPembayaranController@verifikasi', ['role:admin']);
post('/api/pembayaran/refund', 'ApiPembayaranController@refund', ['role:admin']);
