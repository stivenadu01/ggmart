<div class="<?= ($_SESSION['user']['role'] ?? '') === 'pelanggan' ? 'sf-page sf-payment' : 'space-y-5 py-8' ?> user-container">
  <div class="<?= ($_SESSION['user']['role'] ?? '') === 'pelanggan' ? 'sf-page-heading' : '' ?>">
    <h1 class="user-section-title"><?= ($_SESSION['user']['role'] ?? '') === 'pelanggan' ? 'Detail Pesanan' : 'Pembayaran' ?></h1><p class="user-section-subtitle"><?= ($_SESSION['user']['role'] ?? '') === 'pelanggan' ? 'Selesaikan pembayaran, lalu ikuti perkembangan pesanan Anda di sini.' : 'Selesaikan pembayaran. Setelah lunas, struk dicetak bila dipilih dan halaman kembali ke kasir.' ?></p></div>
  <?php include ROOT_PATH . '/app/views/partials/payment.php'; ?>
  <a href="<?= BASE_URL . (($_SESSION['user']['role'] ?? '') === 'pelanggan' ? '/transaksi' : '/admin/kasir') ?>" class="user-action-outline mt-5"><?= ($_SESSION['user']['role'] ?? '') === 'pelanggan' ? 'Kembali ke Pesanan Saya' : 'Layani pembeli berikutnya' ?></a>
</div>
