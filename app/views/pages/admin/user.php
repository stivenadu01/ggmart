<div x-data="userPage()" x-init="init()" class="space-y-5 p-4 sm:p-5 lg:p-6">
  <!-- HEADER -->
  <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
      <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Kelola User</h1>
      <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">Kelola akun, role, informasi kontak, dan status verifikasi pengguna GG-Mart.</p>
    </div>
    <button type="button" @click="openCreate()" class="admin-action-primary w-full sm:w-auto">
      <?= masterIconSvg('plus', 'h-4 w-4') ?>
      Tambah User
    </button>
  </section>

  <!-- FILTER -->
  <section class="card">
    <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto] md:items-end">
      <div>
        <label class="label">Cari User</label>
        <input type="search" x-model="search" @input.debounce.500="pagination.page = 1; load()" class="input w-full" placeholder="Contoh: Budi atau budi@gmail.com" autocomplete="off">
        <p class="form-help">Cari berdasarkan nama atau email pengguna.</p>
      </div>
      <div>
        <label class="label">Role</label>
        <select x-gg-select x-model="role" @change="pagination.page = 1; load()" class="input w-full">
          <option value="">Semua role</option>
          <option value="pelanggan">Pelanggan</option>
          <option value="admin">Admin</option>
          <option value="kasir">Kasir</option>
          <option value="pimpinan">Pimpinan</option>
        </select>
      </div>
      <button type="button" @click="resetFilter()" class="admin-action-secondary w-full md:w-auto">Reset Filter</button>
    </div>
  </section>

  <!-- ERROR -->
  <div x-show="error" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <span x-text="error"></span>
      <button type="button" @click="load()" class="admin-action-danger admin-action-sm">Coba Lagi</button>
    </div>
  </div>

  <!-- TABLE -->
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
      <div>
        <h2 class="text-sm font-bold text-slate-900 sm:text-base">Daftar Pengguna</h2>
        <p class="mt-0.5 text-xs text-slate-500"><span x-text="pagination.total"></span> pengguna ditemukan</p>
      </div>
      <span x-show="loading" class="status-neutral">Memuat...</span>
    </div>

    <!-- Desktop/tablet -->
    <div class="hidden overflow-x-auto md:block">
      <table class="table min-w-[900px]">
        <thead>
          <tr>
            <th class="w-12">No</th>
            <th>Pengguna</th>
            <th>Kontak</th>
            <th>Alamat</th>
            <th>Role</th>
            <th>Verifikasi</th>
            <th class="w-40 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <template x-if="loading">
            <tr>
              <td colspan="7" class="py-12 text-center text-sm text-slate-500">Memuat data pengguna...</td>
            </tr>
          </template>
          <template x-if="!loading && !error && user.length === 0">
            <tr>
              <td colspan="7" class="py-12 text-center">
                <div class="mx-auto max-w-sm">
                  <p class="font-semibold text-slate-700">Belum ada pengguna yang sesuai</p>
                  <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter, atau tambahkan pengguna baru.</p>
                </div>
              </td>
            </tr>
          </template>
          <template x-for="(item, idx) in user" :key="item.id_pengguna">
            <tr class="hover:bg-slate-50/80">
              <td x-text="(pagination.page - 1) * pagination.limit + idx + 1"></td>
              <td>
                <div class="font-semibold text-slate-800" x-text="item.nama"></div>
                <div class="mt-0.5 text-xs text-slate-500" x-text="item.email"></div>
              </td>
              <td>
                <div class="text-sm text-slate-600" x-text="item.no_hp || '-' "></div>
              </td>
              <td>
                <div class="max-w-56 truncate text-sm text-slate-600" :title="item.alamat || '-'" x-text="item.alamat || '-' "></div>
              </td>
              <td><span class="status-badge" :class="roleClass(item.role)" x-text="roleLabel(item.role)"></span></td>
              <td>
                <span class="status-success" x-show="Number(item.is_verified) === 1">Terverifikasi</span>
                <span class="status-warning" x-show="Number(item.is_verified) !== 1">Belum verifikasi</span>
              </td>
              <td>
                <div class="flex justify-end gap-2">
                  <button type="button" @click="edit(item)" class="admin-action-secondary admin-action-sm">Edit</button>
                  <button type="button" @click="hapus(item.id_pengguna)" class="admin-action-danger admin-action-sm">Hapus</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Mobile -->
    <div class="divide-y divide-slate-100 md:hidden">
      <template x-if="loading">
        <div class="px-4 py-12 text-center text-sm text-slate-500">Memuat data pengguna...</div>
      </template>
      <template x-if="!loading && !error && user.length === 0">
        <div class="px-4 py-12 text-center">
          <p class="font-semibold text-slate-700">Belum ada pengguna yang sesuai</p>
          <p class="mt-1 text-sm text-slate-500">Ubah filter atau tambahkan pengguna baru.</p>
        </div>
      </template>
      <template x-for="item in user" :key="item.id_pengguna">
        <article class="space-y-4 p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h3 class="truncate font-semibold text-slate-900" x-text="item.nama"></h3>
              <p class="mt-0.5 truncate text-xs text-slate-500" x-text="item.email"></p>
            </div>
            <span class="status-badge shrink-0" :class="roleClass(item.role)" x-text="roleLabel(item.role)"></span>
          </div>
          <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div>
              <dt class="text-xs font-medium text-slate-400">Nomor HP</dt>
              <dd class="mt-0.5 text-slate-700" x-text="item.no_hp || '-' "></dd>
            </div>
            <div>
              <dt class="text-xs font-medium text-slate-400">Verifikasi</dt>
              <dd class="mt-1"><span class="status-success" x-show="Number(item.is_verified) === 1">Terverifikasi</span><span class="status-warning" x-show="Number(item.is_verified) !== 1">Belum verifikasi</span></dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-xs font-medium text-slate-400">Alamat</dt>
              <dd class="mt-0.5 text-slate-700" x-text="item.alamat || '-' "></dd>
            </div>
          </dl>
          <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="edit(item)" class="admin-action-secondary w-full">Edit</button>
            <button type="button" @click="hapus(item.id_pengguna)" class="admin-action-danger w-full">Hapus</button>
          </div>
        </article>
      </template>
    </div>
  </section>

  <!-- PAGINATION -->
  <div class="flex flex-col gap-3 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
    <label class="flex items-center gap-2">Tampilkan
      <select x-gg-select x-model.number="pagination.limit" @change="pagination.page = 1; load()" class="input w-auto py-2">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
      </select>
      dari <span class="font-semibold text-slate-800" x-text="pagination.total"></span>
    </label>
    <div class="flex flex-wrap gap-2">
      <button type="button" @click="pagination.page--; load()" :disabled="pagination.page <= 1 || loading" class="admin-page-button disabled:cursor-not-allowed disabled:opacity-50"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Prev</button>
      <template x-for="page in pagination.total_pages" :key="page"><button type="button" @click="pagination.page = page; load()" :disabled="loading" :class="pagination.page == page ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' : ''" class="admin-page-button"><span x-text="page"></span></button></template>
      <button type="button" @click="pagination.page++; load()" :disabled="pagination.page >= pagination.total_pages || loading" class="admin-page-button disabled:cursor-not-allowed disabled:opacity-50">Next <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></button>
    </div>
  </div>

  <!-- USER MODAL -->
  <template x-if="showModal">
    <div class="modal-backdrop flex items-center justify-center p-3 sm:p-5" @click.self="closeModal()" @keydown.escape.window="closeModal()">
      <section class="modal-box max-h-[calc(100dvh-1.5rem)] max-w-2xl overflow-y-auto p-0 sm:max-h-[calc(100dvh-2.5rem)]" role="dialog" aria-modal="true" aria-labelledby="user-modal-title" @click.stop>
        <!-- Modal header -->
        <header class="sticky top-0 z-10 border-b border-slate-100 bg-white/95 px-4 py-4 backdrop-blur sm:px-6">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-600" x-text="editingId ? 'Edit akun' : 'Akun baru'"></p>
              <h2 id="user-modal-title" class="mt-1 text-lg font-bold text-slate-900 sm:text-xl" x-text="editingId ? 'Edit User' : 'Tambah User'"></h2>
              <p class="mt-1 text-sm leading-5 text-slate-500" x-text="editingId ? 'Perbarui data akun tanpa mengubah aturan akses yang berlaku.' : 'Isi data pengguna dengan lengkap. Akun yang dibuat admin langsung berstatus terverifikasi.'"></p>
            </div>
            <button type="button" @click="closeModal()" class="admin-action-icon" aria-label="Tutup dialog">✕</button>
          </div>
        </header>

        <form @submit.prevent="save" class="space-y-6 px-4 py-5 sm:px-6 sm:py-6">
          <!-- Verification: deliberately redesigned -->
          <section class="rounded-2xl border p-4 sm:p-5" :class="form.is_verified ? 'border-emerald-200 bg-emerald-50/70' : 'border-amber-200 bg-amber-50/70'">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div class="flex min-w-0 gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" :class="form.is_verified ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                  <?= masterIconSvg('shield-check', 'h-5 w-5') ?>
                </div>
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-bold text-slate-900">Status verifikasi akun</h3>
                    <span x-show="form.is_verified" class="status-success">Terverifikasi</span>
                    <span x-show="!form.is_verified" class="status-warning">Belum verifikasi</span>
                  </div>
                  <p class="mt-1 text-sm leading-5 text-slate-600" x-show="!editingId">Akun yang dibuat melalui menu admin otomatis ditandai terverifikasi. Tidak perlu melakukan verifikasi manual.</p>
                  <p class="mt-1 text-sm leading-5 text-slate-600" x-show="editingId && !form.is_verified">Akun ini belum terverifikasi. Verifikasi hanya dilakukan setelah Anda memastikan data akun benar.</p>
                  <p class="mt-1 text-sm leading-5 text-emerald-700" x-show="editingId && form.is_verified">Akun ini sudah terverifikasi dan tidak memerlukan tindakan tambahan.</p>
                </div>
              </div>
              <button type="button" x-show="editingId && !form.is_verified" @click="verifikasiAkun(editingId)" :disabled="verifying" class="admin-action-primary w-full shrink-0 sm:w-auto">
                <span x-text="verifying ? 'Memverifikasi...' : 'Verifikasi Akun'"></span>
              </button>
            </div>
          </section>

          <!-- Account information -->
          <div class="space-y-4">
            <div>
              <h3 class="text-sm font-bold text-slate-900">Informasi akun</h3>
              <p class="mt-1 text-xs text-slate-500">Data utama yang digunakan untuk mengenali dan mengakses akun.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="sm:col-span-2">
                <label class="label">Nama User *</label>
                <input type="text" x-model.trim="form.nama" class="input w-full" required placeholder="Contoh: Budi Santoso" autocomplete="name">
                <p class="form-help">Masukkan nama lengkap pengguna.</p>
              </div>
              <div>
                <label class="label">Email *</label>
                <input type="email" x-model.trim="form.email" class="input w-full" required placeholder="Contoh: budi@gmail.com" autocomplete="email">
                <p class="form-help">Gunakan email yang benar dan belum dipakai akun lain.</p>
              </div>
              <div>
                <label class="label">Nomor HP</label>
                <input type="tel" x-model.trim="form.no_hp" class="input w-full" placeholder="Contoh: 081234567890" autocomplete="tel">
                <p class="form-help">Gunakan nomor yang aktif jika perlu dihubungi.</p>
              </div>
              <div>
                <label class="label">Role *</label>
                <select x-gg-select x-model="form.role" class="input w-full" required>
                  <option value="pelanggan">Pelanggan</option>
                  <option value="admin">Admin</option>
                  <option value="kasir">Kasir</option>
                  <option value="pimpinan">Pimpinan</option>
                </select>
                <p class="form-help">Role menentukan hak akses pengguna di sistem.</p>
              </div>
              <div class="sm:col-span-2">
                <label class="label">Alamat</label>
                <textarea x-model.trim="form.alamat" class="input w-full" rows="3" placeholder="Contoh: Jl. El Tari No. 10, Kupang"></textarea>
                <p class="form-help">Masukkan alamat pengguna jika diperlukan untuk administrasi.</p>
              </div>
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-4 border-t border-slate-100 pt-5">
            <div>
              <h3 class="text-sm font-bold text-slate-900">Keamanan akun</h3>
              <p class="mt-1 text-xs text-slate-500" x-text="editingId ? 'Kosongkan password jika tidak ingin mengubahnya.' : 'Buat password yang akan digunakan pengguna untuk masuk ke sistem.'"></p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="label">Password <span x-show="!editingId">*</span></label>
                <input :type="showPassword ? 'text' : 'password'" x-model="form.password" class="input w-full" :required="!editingId" placeholder="Contoh: Ggmart@2026" autocomplete="new-password">
                <p class="form-help">Minimal gunakan kombinasi huruf, angka, dan simbol.</p>
              </div>
              <div>
                <label class="label">Konfirmasi Password <span x-show="!editingId">*</span></label>
                <input :type="showPassword ? 'text' : 'password'" x-model="form.rePassword" class="input w-full" :required="!editingId" placeholder="Ketik ulang password" autocomplete="new-password">
                <p class="form-help">Pastikan sama persis dengan password di sebelah kiri.</p>
              </div>
            </div>
            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-600"><input type="checkbox" x-model="showPassword" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"> Tampilkan password</label>
          </div>

          <div x-show="form.password && form.rePassword && form.password !== form.rePassword" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">Konfirmasi password belum sama.</div>
          <div x-show="formError" x-cloak class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="formError" role="alert"></div>

          <!-- Actions -->
          <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <button type="button" @click="closeModal()" class="admin-action-secondary w-full sm:w-auto">Batal</button>
            <button type="submit" :disabled="saving || (!editingId && form.password !== form.rePassword)" class="admin-action-primary w-full disabled:cursor-not-allowed disabled:opacity-60 sm:min-w-40">
              <span x-text="saving ? 'Menyimpan...' : (editingId ? 'Simpan Perubahan' : 'Tambah User')"></span>
            </button>
          </div>
        </form>
      </section>
    </div>
  </template>
</div>

<script>
  function userPage() {
    return {
      user: [],
      search: utils.getQuery('search') ?? '',
      role: utils.getQuery('role') ?? '',
      error: '',
      loading: false,
      pagination: {
        page: Number(utils.getQuery('page') || 1),
        total: 0,
        total_pages: 1,
        limit: Number(utils.getQuery('limit') || 10)
      },
      showModal: false,
      editingId: null,
      saving: false,
      verifying: false,
      formError: '',
      showPassword: false,
      form: {
        nama: '',
        email: '',
        no_hp: '',
        alamat: '',
        password: '',
        rePassword: '',
        role: 'pelanggan',
        is_verified: false
      },

      async init() {
        await this.load()
      },
      async load() {
        this.loading = true;
        this.error = ''
        try {
          const res = await API.get('/user/list?search=' + encodeURIComponent(this.search) + '&role=' + encodeURIComponent(this.role) + '&limit=' + this.pagination.limit + '&page=' + this.pagination.page)
          if (!res.success) throw new Error(res.message || 'Data user gagal dimuat.')
          this.user = res.data || []
          this.pagination.total = Number(res.pagination?.total || 0)
          this.pagination.total_pages = Math.max(1, Number(res.pagination?.total_pages || Math.ceil(this.pagination.total / this.pagination.limit)))
          utils.setQuery('search', this.search);
          utils.setQuery('role', this.role);
          utils.setQuery('page', this.pagination.page);
          utils.setQuery('limit', this.pagination.limit)
        } catch (err) {
          this.error = err.message || 'Terjadi kesalahan saat memuat data user.';
          this.user = []
        } finally {
          this.loading = false
        }
      },
      resetFilter() {
        this.search = '';
        this.role = '';
        this.pagination.page = 1;
        this.load()
      },
      roleLabel(role) {
        return ({
          pelanggan: 'Pelanggan',
          admin: 'Admin',
          kasir: 'Kasir',
          pimpinan: 'Pimpinan'
        })[role] || role
      },
      roleClass(role) {
        return role === 'admin' ? 'bg-red-50 text-red-700' : role === 'kasir' ? 'bg-amber-50 text-amber-700' : role === 'pimpinan' ? 'bg-violet-50 text-violet-700' : 'bg-blue-50 text-blue-700'
      },
      openCreate() {
        this.editingId = null;
        this.formError = '';
        this.showPassword = false
        this.form = {
          nama: '',
          email: '',
          no_hp: '',
          alamat: '',
          password: '',
          rePassword: '',
          role: 'pelanggan',
          is_verified: true
        }
        this.showModal = true
      },
      edit(item) {
        this.editingId = item.id_pengguna;
        this.formError = '';
        this.showPassword = false
        this.form = {
          nama: item.nama || '',
          email: item.email || '',
          no_hp: item.no_hp || '',
          alamat: item.alamat || '',
          password: '',
          rePassword: '',
          role: item.role || 'pelanggan',
          is_verified: Number(item.is_verified) === 1
        }
        this.showModal = true
      },
      closeModal() {
        this.showModal = false;
        this.editingId = null;
        this.saving = false;
        this.verifying = false;
        this.formError = '';
        this.showPassword = false
        this.form = {
          nama: '',
          email: '',
          no_hp: '',
          alamat: '',
          password: '',
          rePassword: '',
          role: 'pelanggan',
          is_verified: false
        }
      },
      async save() {
        this.saving = true;
        this.formError = ''
        try {
          if (!this.editingId && this.form.password !== this.form.rePassword) throw new Error('Konfirmasi password tidak cocok.')
          if (this.editingId) await API.put('/user', {
            ...this.form,
            id_pengguna: this.editingId
          })
          else await API.post('/user', this.form)
          Alpine.store('ui').toast(this.editingId ? 'User berhasil diperbarui.' : 'User berhasil ditambahkan dan terverifikasi.')
          this.closeModal();
          await this.load()
        } catch (err) {
          this.formError = err.message || 'User gagal disimpan.'
        } finally {
          this.saving = false
        }
      },
      async hapus(id_pengguna) {
        const ok = await Alpine.store('ui').confirm('Yakin ingin menghapus user ini? Data akun akan dihapus dan tindakan ini tidak dapat dibatalkan.')
        if (!ok) return
        try {
          await API.delete('/user', {
            id_pengguna
          });
          Alpine.store('ui').toast('User berhasil dihapus.');
          await this.load()
        } catch (err) {
          Alpine.store('ui').toast(err.message || 'User gagal dihapus.')
        }
      },
      async verifikasiAkun(id) {
        if (!id || this.verifying) return
        const ok = await Alpine.store('ui').confirm('Pastikan data akun sudah benar sebelum melanjutkan verifikasi. Lanjutkan?')
        if (!ok) return
        this.verifying = true;
        this.formError = ''
        try {
          await API.put(`/user/${id}/verify`);
          this.form.is_verified = true;
          Alpine.store('ui').toast('Akun berhasil diverifikasi.');
          await this.load()
        } catch (err) {
          this.formError = err.message || 'Akun gagal diverifikasi.'
        } finally {
          this.verifying = false
        }
      }
    }
  }
</script>