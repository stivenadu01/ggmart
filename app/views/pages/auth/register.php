<div class="auth-page">
  <div class="auth-shell">
    <?php include ROOT_PATH . '/app/views/partials/auth-story.php'; ?>

    <section class="auth-form-panel">
      <div class="auth-form-wrap" x-data="registerForm()">
        <div class="auth-heading">
          <h1 class="auth-title">Daftar Akun</h1>
          <p class="auth-description">Lengkapi data berikut untuk mulai berbelanja produk lokal.</p>
        </div>

        <template x-if="success">
          <div class="auth-success-state mb-6">
            <div class="auth-state-icon auth-state-icon-success"><?= masterIconSvg('check-circle', 'h-6 w-6') ?></div>
            <h2 class="auth-state-title">Registrasi berhasil</h2>
            <p class="auth-state-text">Kami sudah mengirim email verifikasi. Verifikasi akun Anda sebelum masuk.</p>
            <a :href="BASE_URL + '/login'" class="btn-primary mt-5">Ke Halaman Login</a>
          </div>
        </template>

        <form x-show="!success" @submit.prevent="submit" class="auth-form">
          <div class="form-group">
            <label for="register-nama" class="label">Nama Lengkap</label>
            <input id="register-nama" type="text" x-model.trim="nama" autocomplete="name"
              placeholder="Contoh: Jhon Doe" class="input" required>
            <p class="form-help">Gunakan nama yang mudah dikenali untuk pesanan Anda.</p>
          </div>

          <div class="form-group">
            <label for="register-email" class="label">Email</label>
            <input id="register-email" type="email" x-model.trim="email" autocomplete="email"
              placeholder="Contoh: jhon@email.com" class="input" required>
          </div>

          <div class="form-group">
            <label for="register-phone" class="label">Nomor HP</label>
            <input id="register-phone" type="tel" x-model.trim="no_hp" autocomplete="tel"
              placeholder="Contoh: 081234567890" class="input" required>
          </div>

          <div class="form-group">
            <label for="register-password" class="label">Kata Sandi</label>
            <div class="auth-password-wrap">
              <input id="register-password" :type="showPassword ? 'text' : 'password'" x-model="password"
                autocomplete="new-password" placeholder="Buat kata sandi Anda" class="input pr-12" required>
              <button type="button" class="auth-password-toggle" @click="showPassword = !showPassword">
                <span x-text="showPassword ? 'Sembunyikan' : 'Lihat'"></span>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="register-confirm" class="label">Konfirmasi Kata Sandi</label>
            <input id="register-confirm" :type="showPassword ? 'text' : 'password'" x-model="password_confirm"
              autocomplete="new-password" placeholder="Masukkan kembali kata sandi" class="input" required>
          </div>

          <div x-show="error" x-cloak class="auth-alert auth-alert-error" role="alert" x-text="error"></div>

          <button type="submit" class="btn-primary auth-submit" :disabled="loading || !isValid">
            <span x-text="loading ? 'Membuat akun...' : 'Daftar' "></span>
          </button>
        </form>

        <div x-show="!success" class="auth-footer-text">
          Sudah punya akun?
          <a :href="BASE_URL + '/login'" class="auth-link">Masuk di sini</a>
        </div>
      </div>
    </section>
  </div>
</div>

<script>
  function registerForm() {
    return {
      nama: '',
      email: '',
      password: '',
      password_confirm: '',
      no_hp: '',
      showPassword: false,
      loading: false,
      error: '',
      success: false,
      get isValid() {
        return this.nama && this.email && this.password && this.password_confirm && this.no_hp && this.password === this.password_confirm;
      },
      async submit() {
        if (!this.isValid || this.loading) {
          if (this.password && this.password_confirm && this.password !== this.password_confirm) {
            this.error = 'Konfirmasi kata sandi tidak cocok.';
          }
          return;
        }
        this.loading = true;
        this.error = '';
        try {
          const res = await API.post('/auth/register', {
            nama: this.nama,
            email: this.email,
            password: this.password,
            no_hp: this.no_hp,
          });
          if (res.success) {
            this.success = true;
            Alpine.store('ui').toast('Registrasi berhasil. Silakan verifikasi email Anda.');
          } else {
            this.error = res.message || 'Registrasi gagal diproses.';
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
