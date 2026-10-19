<div class="auth-page">
  <div class="auth-simple-shell" x-data="resetForm()">
    <div class="auth-heading text-center">
      <img :src="BASE_URL + '/assets/logo.png'" alt="Logo GG-Mart" class="auth-logo auth-logo-small">
      <p class="auth-eyebrow">KEAMANAN AKUN</p>
      <h1 class="auth-title">Buat kata sandi baru</h1>
      <p class="auth-description">Gunakan kata sandi baru untuk masuk kembali ke akun Anda.</p>
    </div>

    <template x-if="success">
      <div class="auth-success-state mt-6">
        <div class="auth-state-icon auth-state-icon-success"><?= masterIconSvg('check-circle', 'h-6 w-6') ?></div>
        <h2 class="auth-state-title">Kata sandi berhasil diubah</h2>
        <p class="auth-state-text">Anda dapat masuk menggunakan kata sandi baru.</p>
        <a :href="BASE_URL + '/login'" class="btn-primary mt-5">Masuk Sekarang</a>
      </div>
    </template>

    <form x-show="!success" @submit.prevent="submit" class="auth-form mt-8">
      <div class="form-group">
        <label for="reset-password" class="label">Kata Sandi Baru *</label>
        <div class="auth-password-wrap">
          <input id="reset-password" :type="showPassword ? 'text' : 'password'" x-model="password"
            autocomplete="new-password" placeholder="Buat kata sandi baru" class="input pr-12" required>
          <button type="button" class="auth-password-toggle" @click="showPassword = !showPassword">
            <span x-text="showPassword ? 'Sembunyikan' : 'Lihat'"></span>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label for="reset-confirm" class="label">Ulangi Kata Sandi *</label>
        <input id="reset-confirm" :type="showPassword ? 'text' : 'password'" x-model="confirmPassword"
          autocomplete="new-password" placeholder="Masukkan kembali kata sandi" class="input" required>
      </div>

      <div x-show="error" x-cloak class="auth-alert auth-alert-error" role="alert" x-text="error"></div>

      <button type="submit" class="btn-primary auth-submit" :disabled="loading || !password || !confirmPassword">
        <span x-text="loading ? 'Menyimpan...' : 'Simpan Kata Sandi'"></span>
      </button>

      <a :href="BASE_URL + '/login'" class="btn-secondary">Kembali ke Login</a>
    </form>
  </div>
</div>

<script>
  function resetForm() {
    return {
      password: '', confirmPassword: '', showPassword: false, loading: false, error: '', success: false,
      async submit() {
        if (!this.password || !this.confirmPassword || this.loading) return;
        if (this.password !== this.confirmPassword) {
          this.error = 'Konfirmasi kata sandi tidak cocok.';
          return;
        }
        const token = new URLSearchParams(window.location.search).get('token');
        if (!token) {
          this.error = 'Tautan reset tidak memiliki token yang valid.';
          return;
        }
        this.loading = true;
        this.error = '';
        try {
          const res = await API.post('/auth/reset-password', { token, password: this.password });
          if (res.success) {
            this.success = true;
            Alpine.store('ui').toast('Kata sandi berhasil diubah.');
          } else {
            this.error = res.message || 'Kata sandi gagal diubah.';
          }
        } catch (error) {
          this.error = error.message || 'Tidak dapat menghubungi server.';
        } finally {
          this.loading = false;
        }
      }
    }
  }
</script>
