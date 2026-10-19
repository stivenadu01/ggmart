<div class="auth-page">
  <div class="auth-simple-shell text-center">
    <img :src="BASE_URL + '/assets/logo.png'" alt="Logo GG-Mart" class="auth-logo auth-logo-small mx-auto">

    <?php if ($status === 'success'): ?>
      <div class="auth-state-icon auth-state-icon-success mx-auto mt-6"><?= masterIconSvg('check-circle', 'h-6 w-6') ?></div>
      <p class="auth-eyebrow mt-5">VERIFIKASI BERHASIL</p>
      <h1 class="auth-title">Akun Anda sudah aktif</h1>
      <p class="auth-description">Email berhasil diverifikasi. Silakan masuk untuk mulai menggunakan GG-Mart.</p>
      <a :href="BASE_URL + '/login'" class="btn-primary mt-6">Masuk Sekarang</a>
    <?php else: ?>
      <div class="auth-state-icon auth-state-icon-error mx-auto mt-6"><?= masterIconSvg('warning', 'h-6 w-6') ?></div>
      <p class="auth-eyebrow mt-5">VERIFIKASI GAGAL</p>
      <h1 class="auth-title">Tautan tidak dapat digunakan</h1>
      <p class="auth-description"><?= htmlspecialchars($message) ?></p>
      <a :href="BASE_URL + '/login'" class="btn-secondary mt-6">Kembali ke Login</a>
    <?php endif; ?>
  </div>
</div>
