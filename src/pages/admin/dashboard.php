<?php
if (!has_role(['admin', 'kasir', 'manager'])) {
  redirect('/auth/login');
}
if (has_role(['kasir'])) redirect('/admin/transaksi/input');

$pageTitle = "Dashboard Admin";
include INCLUDES_PATH . "/admin/layout/header.php";
?>

<div x-data="dashboardAdminPage()" class="bg-gray-50 p-2 lg:p-4 space-y-4">
  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-neutral-800 tracking-tight">Dashboard Admin</h1>
      <p class="text-sm text-gray-500">Pantau statistik dan aktivitas terbaru di GGMART.</p>
    </div>
    <div class="text-sm text-gray-500">
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= ASSETS_URL ?>/js/admin/dashboardAdminPage.js"></script>
<?php include INCLUDES_PATH . "/admin/layout/footer.php"; ?>