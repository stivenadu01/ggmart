<script>
  const baseUrl = <?= json_encode(BASE_URL) ?>;
  const assetsUrl = <?= json_encode(ASSETS_URL) ?>;
  const uploadsUrl = <?= json_encode(UPLOADS_URL) ?>;

  const currentUser = <?= json_encode($_SESSION['user'] ?? null) ?>;

  function hasRole(roles) {
    if (!currentUser || !currentUser.role) return false;
    if (!roles) return true;

    // Normalisasi biar tidak masalah huruf besar/kecil atau spasi
    const userRole = currentUser.role.toLowerCase().trim();
    const roleList = (Array.isArray(roles) ? roles : [roles])
      .map(r => r.toLowerCase().trim());
    return roleList.includes(userRole);
  }



  function formatRupiah(angka, prefix = true) {
    const formatted = new Intl.NumberFormat('id-ID', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(angka);

    return prefix ? 'Rp' + formatted : formatted;
  }

  function formatDate(dateStr) {
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    };
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', options);
  }

  function formatDateTime(dateStr) {
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    };
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', options);
  }

  function storageHelper(key, action, value = null) {
    const data = JSON.parse(localStorage.getItem(key) || '[]')

    switch (action) {
      case 'load':
        return data

      case 'save':
        if (!value) return
        // tambahkan item baru ke depan, hapus duplikat, batasi 100 item
        const updated = [value, ...data.filter(i => i !== value)].slice(0, 100)
        localStorage.setItem(key, JSON.stringify(updated))
        return updated

      case 'remove':
        if (typeof value !== 'number') return
        data.splice(value, 1)
        localStorage.setItem(key, JSON.stringify(data))
        return data

      case 'clear':
        localStorage.removeItem(key)
        return []

      default:
        console.warn('storageHelper: action tidak dikenal ->', action)
        return data
    }
  }
</script>