<?php $user=$_SESSION['user'] ?? []; ?>
<div class="admin-page space-y-5 p-4 sm:p-5 lg:p-7" x-data="storeSettingsPage()">
  <header class="admin-page-header"><div><p class="admin-eyebrow">Toko</p><h2 class="admin-page-title">Pengaturan</h2><p class="admin-page-subtitle">Kelola akun, pembayaran, dan wilayah pengiriman.</p></div></header>
  <div class="flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2" role="tablist" aria-label="Bagian pengaturan">
    <?php foreach (['akun'=>'Akun','pembayaran'=>'Pembayaran','pengiriman'=>'Pengiriman'] as $key=>$label): ?>
      <button id="tab-<?= $key ?>" type="button" role="tab" aria-controls="panel-<?= $key ?>" :aria-selected="tab === '<?= $key ?>'" @click="tab = '<?= $key ?>'; error = ''" :class="tab === '<?= $key ?>' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100'" class="rounded-xl px-5 py-3 text-sm font-semibold"><?= $label ?></button>
    <?php endforeach; ?>
  </div>
  <section id="panel-akun" role="tabpanel" aria-labelledby="tab-akun" x-show="tab === 'akun'" class="admin-card p-5 sm:p-6">
    <h3 class="text-lg font-semibold text-slate-900">Informasi akun</h3>
    <dl class="mt-5 grid gap-5 sm:grid-cols-2">
      <?php foreach (['nama'=>'Nama','email'=>'Email','no_hp'=>'Nomor WhatsApp','role'=>'Peran'] as $field=>$label): ?>
        <div><dt class="text-sm text-slate-500"><?= $label ?></dt><dd class="mt-1 break-all font-semibold text-slate-800"><?= htmlspecialchars((string)($user[$field] ?? '-')) ?></dd></div>
      <?php endforeach; ?>
    </dl>
    <p class="form-help mt-5">Ubah informasi pribadi dan kata sandi melalui halaman profil.</p>
    <a href="<?= BASE_URL ?>/profil" class="admin-action-secondary mt-3">Kelola profil</a>
  </section>
  <?php include ROOT_PATH . '/app/views/partials/store-settings.php'; ?>
</div>
