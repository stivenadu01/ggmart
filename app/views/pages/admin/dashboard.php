<div class="admin-page space-y-6 p-4 sm:p-6 lg:p-8">

  <!-- PAGE HEADER -->
  <div class="admin-page-header">
    <div class="min-w-0">
      <div class="admin-eyebrow">Overview</div>
      <div class="flex flex-wrap items-center gap-3">
        <h1 class="admin-page-title">Dashboard Admin</h1>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
          Sistem aktif
        </span>
      </div>
      <p class="admin-page-subtitle">
        Pantau penjualan, laba, transaksi, dan kondisi stok GG-Mart dalam satu tampilan.
      </p>
    </div>

    <div class="flex shrink-0 items-center gap-2">
      <div class="hidden rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-sm sm:block">
        <span class="font-medium text-slate-700"><?= date('d M Y') ?></span>
      </div>
      <button
        type="button"
        class="admin-action-icon"
        @click="$dispatch('dashboard-refresh')"
        title="Muat ulang dashboard"
        aria-label="Muat ulang dashboard">
        <?= masterIconSvg('refresh', 'h-4.5 w-4.5') ?>
      </button>
    </div>
  </div>

  <!-- SUMMARY -->
  <section x-data="dashboardPage()" @dashboard-refresh.window="refresh()">
    <div class="mb-3 flex items-center justify-between gap-3">
      <div>
        <h2 class="text-base font-bold text-slate-900 sm:text-lg">Ringkasan Hari Ini</h2>
        <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Perbandingan otomatis terhadap hari sebelumnya.</p>
      </div>
      <div x-show="loading" x-cloak class="flex items-center gap-2 text-xs font-medium text-slate-400">
        <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-200 border-t-emerald-500"></span>
        Memuat...
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

      <!-- PRODUCT -->
      <div class="admin-card p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">Total Produk</p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
              x-text="summary.total_produk"></p>
            <p class="mt-1 text-xs text-slate-400">Produk terdaftar</p>
          </div>
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
            <?= masterIconSvg('product', 'h-5 w-5') ?>
          </span>
        </div>
      </div>

      <!-- TRANSACTIONS -->
      <div class="admin-card p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">Transaksi Hari Ini</p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
              x-text="summary.transaksi_hari_ini"></p>
            <div class="mt-1 flex items-center gap-1.5 text-xs font-semibold"
              :class="growthClass(summary.growth.transaksi)">
              <span x-text="growthIcon(summary.growth.transaksi)"></span>
              <span x-text="formatGrowth(summary.growth.transaksi)"></span>
              <span class="font-normal text-slate-400">vs kemarin</span>
            </div>
          </div>
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
            <?= masterIconSvg('report', 'h-5 w-5') ?>
          </span>
        </div>
      </div>

    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <!-- SALES -->
      <div class="admin-card p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">Penjualan Hari Ini</p>
            <p class="mt-2 truncate text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
              x-text="utils.formatRupiah(summary.penjualan_hari_ini)"></p>
            <div class="mt-1 flex items-center gap-1.5 text-xs font-semibold"
              :class="growthClass(summary.growth.penjualan)">
              <span x-text="growthIcon(summary.growth.penjualan)"></span>
              <span x-text="formatGrowth(summary.growth.penjualan)"></span>
              <span class="font-normal text-slate-400">vs kemarin</span>
            </div>
          </div>
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
            <?= masterIconSvg('warehouse', 'h-5 w-5') ?>
          </span>
        </div>
      </div>

      <!-- PROFIT -->
      <div class="admin-card p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">Laba Hari Ini</p>
            <p class="mt-2 truncate text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
              x-text="utils.formatRupiah(summary.laba_hari_ini)"></p>
            <div class="mt-1 flex items-center gap-1.5 text-xs font-semibold"
              :class="growthClass(summary.growth.laba)">
              <span x-text="growthIcon(summary.growth.laba)"></span>
              <span x-text="formatGrowth(summary.growth.laba)"></span>
              <span class="font-normal text-slate-400">vs kemarin</span>
            </div>
          </div>
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
            <?= masterIconSvg('profit', 'h-5 w-5') ?>
          </span>
        </div>
      </div>
    </div>
  </section>

  <!-- ANALYTICS -->
  <section x-data="analyticsPage()" @dashboard-refresh.window="refresh()" class="space-y-4">

    <!-- MONTHLY -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div class="admin-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="text-sm font-medium text-slate-500">Omzet Bulan Ini</p>
            <p class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl"
              x-text="utils.formatRupiah(data.monthly.omzet_bulan_ini)"></p>
          </div>
          <div class="text-right">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Growth</p>
            <p class="mt-1 text-sm font-bold" :class="growthClass(data.monthly.omzet_growth)"
              x-text="formatGrowth(data.monthly.omzet_growth)"></p>
          </div>
        </div>
      </div>

      <div class="admin-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="text-sm font-medium text-slate-500">Laba Bulan Ini</p>
            <p class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl"
              x-text="utils.formatRupiah(data.monthly.laba_bulan_ini)"></p>
          </div>
          <div class="text-right">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Growth</p>
            <p class="mt-1 text-sm font-bold" :class="growthClass(data.monthly.laba_growth)"
              x-text="formatGrowth(data.monthly.laba_growth)"></p>
          </div>
        </div>
      </div>
    </div>

    <!-- TREND CHART -->
    <div class="admin-card overflow-hidden">
      <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <div>
          <h2 class="text-base font-bold text-slate-900 sm:text-lg">Trend 7 Hari</h2>
          <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Pergerakan penjualan dan laba harian.</p>
        </div>
        <div class="flex items-center gap-4 text-xs font-medium text-slate-500">
          <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-sky-500"></i>Penjualan</span>
          <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-emerald-500"></i>Laba</span>
        </div>
      </div>
      <div class="p-4 sm:p-5">
        <div class="relative h-[260px] sm:h-[320px]">
          <canvas id="trendChartCanvas"></canvas>
          <div x-show="!data.trend.length" x-cloak class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
              <p class="text-sm font-semibold text-slate-500">Belum ada data trend</p>
              <p class="mt-1 text-xs text-slate-400">Data akan muncul setelah transaksi tersedia.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PRODUCTS -->
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

      <!-- TOP SELLING -->
      <div class="admin-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
          <div>
            <h2 class="text-base font-bold text-slate-900">Produk Terlaris</h2>
            <p class="mt-0.5 text-xs text-slate-500">5 produk dengan penjualan tertinggi.</p>
          </div>
          <span class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">Top 5</span>
        </div>
        <div class="divide-y divide-slate-100">
          <template x-for="(p, index) in data.top_produk" :key="p.kode_produk || index">
            <div class="flex items-center gap-3 px-4 py-3.5 sm:px-5">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500" x-text="index + 1"></span>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-800" x-text="p.nama_produk"></p>
                <p class="mt-0.5 text-xs text-slate-400">Unit terjual</p>
              </div>
              <span class="shrink-0 text-sm font-bold text-slate-700" x-text="p.total_terjual"></span>
            </div>
          </template>
          <div x-show="!data.top_produk.length" x-cloak class="px-5 py-10 text-center text-sm text-slate-400">
            Belum ada data produk terlaris.
          </div>
        </div>
      </div>

      <!-- PROFIT -->
      <div class="admin-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
          <div>
            <h2 class="text-base font-bold text-slate-900">Produk Paling Menguntungkan</h2>
            <p class="mt-0.5 text-xs text-slate-500">Produk dengan kontribusi laba terbesar.</p>
          </div>
          <span class="rounded-lg bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700">Top 5</span>
        </div>
        <div class="divide-y divide-slate-100">
          <template x-for="(p, index) in data.profit_produk" :key="p.kode_produk || index">
            <div class="flex items-center gap-3 px-4 py-3.5 sm:px-5">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-xs font-bold text-amber-700" x-text="index + 1"></span>
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-800" x-text="p.nama_produk"></p>
                <p class="mt-0.5 text-xs text-slate-400">Profit</p>
              </div>
              <span class="shrink-0 text-sm font-bold text-emerald-600" x-text="utils.formatRupiah(p.profit)"></span>
            </div>
          </template>
          <div x-show="!data.profit_produk.length" x-cloak class="px-5 py-10 text-center text-sm text-slate-400">
            Belum ada data profit produk.
          </div>
        </div>
      </div>

    </div>

    <!-- LOW STOCK -->
    <div class="admin-card overflow-hidden">
      <div class="flex flex-col gap-2 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <div>
          <h2 class="text-base font-bold text-slate-900">Stok Perlu Perhatian</h2>
          <p class="mt-0.5 text-xs text-slate-500">Produk yang mendekati batas stok minimum.</p>
        </div>
        <span class="w-fit rounded-lg bg-red-50 px-2 py-1 text-xs font-bold text-red-600">Low Stock</span>
      </div>
      <div class="divide-y divide-slate-100 sm:grid sm:grid-cols-2 sm:divide-y-0 lg:grid-cols-3">
        <template x-for="(p, index) in data.low_stock" :key="p.kode_produk || index">
          <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:border-b sm:border-slate-100 sm:px-5">
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-800" x-text="p.nama_produk"></p>
              <p class="mt-0.5 text-xs text-slate-400">Stok tersisa</p>
            </div>
            <span class="shrink-0 rounded-lg bg-red-50 px-2.5 py-1 text-sm font-bold text-red-600" x-text="p.stok"></span>
          </div>
        </template>
        <div x-show="!data.low_stock.length" x-cloak class="px-5 py-10 text-center text-sm text-emerald-600 sm:col-span-2 lg:col-span-3">
          Semua stok produk masih dalam kondisi aman.
        </div>
      </div>
    </div>

    <!-- TRAFFIC + CUSTOMER -->
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

      <div class="admin-card overflow-hidden">
        <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
          <h2 class="text-base font-bold text-slate-900">Jam Ramai</h2>
          <p class="mt-0.5 text-xs text-slate-500">Waktu dengan aktivitas transaksi tertinggi.</p>
        </div>
        <div class="divide-y divide-slate-100">
          <template x-for="(j, index) in data.jam_ramai" :key="index">
            <div class="flex items-center justify-between px-4 py-3.5 sm:px-5">
              <span class="text-sm font-semibold text-slate-700" x-text="j.jam + ':00'"></span>
              <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600" x-text="j.total + ' transaksi'"></span>
            </div>
          </template>
          <div x-show="!data.jam_ramai.length" x-cloak class="px-5 py-10 text-center text-sm text-slate-400">
            Belum ada data jam ramai.
          </div>
        </div>
      </div>

      <div class="admin-card overflow-hidden">
        <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
          <h2 class="text-base font-bold text-slate-900">Top Customer</h2>
          <p class="mt-0.5 text-xs text-slate-500">Pelanggan dengan total belanja tertinggi.</p>
        </div>
        <div class="divide-y divide-slate-100">
          <template x-for="(u, index) in data.top_user" :key="u.id_pengguna || index">
            <div class="flex items-center gap-3 px-4 py-3.5 sm:px-5">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-50 text-xs font-bold text-violet-700" x-text="index + 1"></span>
              <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-700" x-text="u.nama"></span>
              <span class="shrink-0 text-sm font-bold text-slate-800" x-text="utils.formatRupiah(u.total_belanja)"></span>
            </div>
          </template>
          <div x-show="!data.top_user.length" x-cloak class="px-5 py-10 text-center text-sm text-slate-400">
            Belum ada data customer.
          </div>
        </div>
      </div>

    </div>

  </section>
</div>

<script>
  function dashboardPage() {
    return {
      summary: {
        total_produk: 0,
        transaksi_hari_ini: 0,
        penjualan_hari_ini: 0,
        laba_hari_ini: 0,
        growth: {
          penjualan: 0,
          laba: 0,
          transaksi: 0
        },
      },
      loading: false,

      async init() {
        await this.loadSummary()
      },

      async refresh() {
        await this.loadSummary()
      },

      async loadSummary() {
        try {
          this.loading = true
          const res = await API.get('/dashboard/summary')
          this.summary = res.data
        } catch (e) {
          console.error('Summary error:', e)
        } finally {
          this.loading = false
        }
      },

      formatGrowth(val) {
        const n = Number(val ?? 0)
        if (n === 0) return '0%'
        return (n > 0 ? '+' : '') + n + '%'
      },

      growthClass(val) {
        const n = Number(val ?? 0)
        if (n > 0) return 'text-emerald-600'
        if (n < 0) return 'text-red-600'
        return 'text-slate-400'
      },

      growthIcon(val) {
        const n = Number(val ?? 0)
        if (n > 0) return '↑'
        if (n < 0) return '↓'
        return '→'
      }
    }
  }
</script>

<script>
  function analyticsPage() {
    return {
      data: {
        monthly: {
          laba_bulan_ini: 0,
          laba_growth: 0,
          omzet_bulan_ini: 0,
          omzet_growth: 0
        },
        trend: [],
        top_produk: [],
        slow_produk: [],
        low_stock: [],
        profit_produk: [],
        jam_ramai: [],
        top_user: []
      },

      chart: null,

      async init() {
        await this.loadAnalytics()
      },

      async refresh() {
        await this.loadAnalytics()
      },

      async loadAnalytics() {
        try {
          const res = await API.get('/dashboard/analytics')
          this.data = res.data
          this.$nextTick(() => this.renderChart())
        } catch (e) {
          console.error('Analytics error:', e)
        }
      },

      formatGrowth(val) {
        const n = Number(val ?? 0)
        if (n === 0) return '0%'
        return (n > 0 ? '+' : '') + n + '%'
      },

      growthClass(val) {
        const n = Number(val ?? 0)
        if (n > 0) return 'text-emerald-600'
        if (n < 0) return 'text-red-600'
        return 'text-slate-400'
      },

      renderChart() {
        const canvas = document.getElementById('trendChartCanvas')
        if (!canvas || typeof Chart === 'undefined') return

        const labels = this.data.trend.map(i => i.tanggal)
        const penjualan = this.data.trend.map(i => Number(i.penjualan || 0))
        const laba = this.data.trend.map(i => Number(i.laba || 0))

        if (this.chart) {
          this.chart.destroy()
        }

        this.chart = new Chart(canvas, {
          type: 'line',
          data: {
            labels,
            datasets: [{
                label: 'Penjualan',
                data: penjualan,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14,165,233,0.08)',
                fill: true,
                tension: 0.35,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5
              },
              {
                label: 'Laba',
                data: laba,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34,197,94,0.06)',
                fill: true,
                tension: 0.35,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              mode: 'index',
              intersect: false
            },
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                padding: 10,
                callbacks: {
                  label(context) {
                    return context.dataset.label + ': Rp ' +
                      new Intl.NumberFormat('id-ID').format(context.parsed.y || 0)
                  }
                }
              }
            },
            scales: {
              x: {
                grid: {
                  display: false
                },
                ticks: {
                  color: '#94a3b8',
                  font: {
                    size: 11
                  }
                }
              },
              y: {
                beginAtZero: true,
                border: {
                  display: false
                },
                grid: {
                  color: 'rgba(148,163,184,0.12)'
                },
                ticks: {
                  color: '#94a3b8',
                  font: {
                    size: 11
                  },
                  callback(value) {
                    const n = Number(value)
                    if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1) + ' jt'
                    if (n >= 1000) return 'Rp ' + (n / 1000).toFixed(0) + ' rb'
                    return 'Rp ' + n
                  }
                }
              }
            }
          }
        })
      }
    }
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>