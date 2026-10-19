<div class="sf-page sf-profile user-container" x-data="profilPage()">
  <div class="sf-profile-layout">
    <header class="sf-page-heading">
      <h1>Profil Saya</h1>
      <p>Informasi Anda membantu kami menyiapkan pesanan dan menghubungi Anda.</p>
    </header>

    <section class="card sf-profile-card overflow-hidden">
      <div class="sf-card-heading"><?= masterIconSvg('users', 'h-5 w-5') ?><h2>Informasi akun</h2></div>
      <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-[180px_1fr] md:items-start">
        <div class="flex flex-col items-center text-center">
          <div class="flex h-24 w-24 items-center justify-center rounded-full bg-emerald-50 text-2xl font-bold text-emerald-700 ring-8 ring-slate-50" aria-hidden="true">
            <span x-text="initials"></span>
          </div>
          <span class="mt-4 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-600" x-text="roleLabel"></span>
          <p class="mt-2 text-xs text-slate-400">Akun GG-Mart</p>
        </div>

        <div class="min-w-0">
          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <p class="label">Nama</p>
              <p class="mt-1 break-words text-sm font-semibold text-slate-800" x-text="user.nama || '-' "></p>
            </div>
            <div>
              <p class="label">Email</p>
              <p class="mt-1 break-all text-sm text-slate-700" x-text="user.email || '-' "></p>
            </div>
            <div>
              <p class="label">Nomor HP</p>
              <p class="mt-1 text-sm text-slate-700" x-text="user.no_hp || 'Belum diisi'"></p>
            </div>
            <div>
              <p class="label">Alamat</p>
              <p class="mt-1 whitespace-pre-line text-sm text-slate-700" x-text="user.alamat || 'Belum diisi'"></p>
            </div>
          </div>

          <div class="mt-6 flex flex-col gap-2 border-t border-slate-100 pt-5 sm:flex-row">
            <button type="button" @click="startEdit()" class="user-action-primary w-full sm:w-auto">
              Edit Profil
            </button>
            <button type="button" @click="logout()" class="user-action-danger w-full sm:w-auto">
              Keluar Akun
            </button>
          </div>
        </div>
      </div>
    </section>

    <section x-show="editing" x-cloak class="card sf-profile-edit p-5 sm:p-6">
      <div class="mb-5">
        <h2 class="text-lg font-bold text-slate-900">Edit Informasi Profil</h2>
        <p class="mt-1 text-sm text-slate-500">Perbarui data yang ingin digunakan pada akun Anda.</p>
      </div>

      <form @submit.prevent="save" class="space-y-5" novalidate>
        <div class="grid gap-5 sm:grid-cols-2">
          <div>
            <label for="profil-nama" class="label">Nama *</label>
            <input id="profil-nama" type="text" x-model.trim="form.nama" class="input mt-1" placeholder="Contoh: Stiven Adu" autocomplete="name" required>
            <p class="form-help">Gunakan nama yang mudah dikenali untuk pesanan dan komunikasi.</p>
          </div>

          <div>
            <label for="profil-email" class="label">Email *</label>
            <input id="profil-email" type="email" x-model.trim="form.email" class="input mt-1" placeholder="Contoh: stiven@email.com" autocomplete="email" required>
            <p class="form-help">Gunakan email aktif yang dapat Anda akses.</p>
          </div>
        </div>

        <div>
          <label for="profil-nohp" class="label">Nomor HP</label>
          <input id="profil-nohp" type="tel" x-model.trim="form.no_hp" class="input mt-1" placeholder="Contoh: 081234567890" autocomplete="tel">
          <p class="form-help">Opsional. Masukkan nomor yang dapat dihubungi.</p>
        </div>

        <div>
          <label for="profil-alamat" class="label">Alamat</label>
          <textarea id="profil-alamat" x-model.trim="form.alamat" class="input mt-1" rows="4" placeholder="Contoh: Jl. Timor Raya No. 10, Kupang" autocomplete="street-address"></textarea>
          <p class="form-help">Opsional. Alamat dapat membantu saat komunikasi terkait pesanan.</p>
        </div>

        <div x-show="error" x-cloak class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert" x-text="error"></div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
          <button type="button" @click="cancel()" class="user-action-secondary w-full sm:w-auto" :disabled="saving">Batal</button>
          <button type="submit" class="user-action-primary w-full sm:w-auto" :disabled="saving || !canSave">
            <span x-show="!saving">Simpan Perubahan</span>
            <span x-show="saving" x-cloak>Menyimpan...</span>
          </button>
        </div>
      </form>
    </section>

    <section class="card sf-profile-security p-5 sm:p-6">
      <div class="sf-feature-icon mb-4"><?= masterIconSvg('shield-check', 'h-6 w-6') ?></div>
      <h2 class="text-base font-bold text-slate-900">Keamanan akun</h2>
      <p class="mt-2 text-sm leading-6 text-slate-500">Untuk mengganti kata sandi, gunakan fitur lupa kata sandi pada halaman masuk.</p>
      <a href="<?= BASE_URL ?>/login" class="sf-text-link mt-4">Buka halaman masuk <?= masterIconSvg('arrow-right', 'ml-1 h-4 w-4') ?></a>
      <div class="mt-6 border-t border-slate-100 pt-5"><h3 class="text-sm font-semibold">Lengkapi data Anda</h3><p class="mt-2 text-sm leading-6 text-slate-500">Nama, nomor WhatsApp, dan alamat yang tepat memudahkan komunikasi mengenai pesanan.</p></div>
    </section>
  </div>
</div>

<script>
  function profilPage() {
    return {
      user: Alpine.store('auth').user || {},
      editing: false,
      saving: false,
      error: '',
      form: {},

      init() {
        this.resetForm()
      },

      get initials() {
        const value = (this.user.nama || this.user.email || 'U').trim()
        return value.split(/\s+/).slice(0, 2).map(part => part.charAt(0)).join('').toUpperCase()
      },

      get roleLabel() {
        const roles = {
          pelanggan: 'Pelanggan',
          admin: 'Admin',
          kasir: 'Kasir',
          pimpinan: 'Pimpinan'
        }
        return roles[this.user.role] || this.user.role || 'Pelanggan'
      },

      get canSave() {
        return Boolean(this.form.nama?.trim() && this.form.email?.trim())
      },

      resetForm() {
        this.form = {
          nama: this.user.nama || '',
          email: this.user.email || '',
          no_hp: this.user.no_hp || '',
          alamat: this.user.alamat || ''
        }
        this.error = ''
      },

      startEdit() {
        this.resetForm()
        this.editing = true
        this.$nextTick(() => document.getElementById('profil-nama')?.focus())
      },

      cancel() {
        if (this.saving) return
        this.editing = false
        this.resetForm()
      },

      async save() {
        if (!this.canSave || this.saving) return
        this.error = ''
        this.saving = true

        try {
          const res = await API.put('/user', {
            nama: this.form.nama.trim(),
            email: this.form.email.trim(),
            no_hp: this.form.no_hp?.trim() || '',
            alamat: this.form.alamat?.trim() || ''
          })

          if (!res?.success) throw new Error(res?.message || 'Profil gagal diperbarui.')

          await Alpine.store('auth').refresh()
          this.user = {
            ...Alpine.store('auth').user
          }
          this.editing = false
          this.resetForm()
          Alpine.store('ui').toast(res.message || 'Profil berhasil diperbarui.')
        } catch (err) {
          this.error = err?.message || 'Profil gagal diperbarui. Silakan coba lagi.'
        } finally {
          this.saving = false
        }
      },

      async logout() {
        await Alpine.store('auth').logout()
      }
    }
  }
</script>
