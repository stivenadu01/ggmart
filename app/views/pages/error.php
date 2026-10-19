<div class="sf-page user-container flex min-h-[65vh] items-center justify-center">
  <div class="sf-error-card user-card w-full max-w-xl p-8 text-center sm:p-12">

    <!-- CODE -->
    <div class="mb-4 font-serif text-7xl text-primary">
      <?php echo $status; ?>
    </div>

    <!-- TITLE -->
    <h1 class="mb-3 font-serif text-3xl text-slate-800">
      <?php echo $title; ?>
    </h1>

    <!-- MESSAGE -->
    <p class="mb-8 text-sm leading-7 text-slate-500">
      <?php echo $message; ?>
    </p>

    <!-- ACTION -->
    <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
      <a :href="BASE_URL" class="btn-primary whitespace-nowrap">
        Kembali ke Beranda
      </a>

      <a href="<?= BASE_URL ?>/produk" class="btn-outline-primary whitespace-nowrap">Jelajahi Katalog</a>
    </div>

  </div>
</div>
