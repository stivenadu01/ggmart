<?php
function loadIcons()
{
  $iconDir = ROOT_PATH . '/public/assets/icons/';
  $icons = [];

  foreach (glob($iconDir . '*.svg') as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);
    $icons[$name] = trim(file_get_contents($file));
  }

  return $icons;
}
$ICONS = loadIcons();
?>
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.store('icons', <?= json_encode($ICONS) ?>);
  });

  // Fungsi global agar bisa dipanggil x-html="icon('home')"
  function icon(name) {
    return Alpine.store('icons')[name] || '';
  }
</script>