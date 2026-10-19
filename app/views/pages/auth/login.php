<div class="auth-page">
  <div class="auth-shell">
    <?php include ROOT_PATH . '/app/views/partials/auth-story.php'; ?>

    <section class="auth-form-panel">
      <div class="auth-form-wrap" x-data="loginForm()">
        <div class="auth-heading">
          <h1 class="auth-title">Masuk ke akun Anda</h1>
          <p class="auth-description">Gunakan email dan kata sandi yang terdaftar untuk melanjutkan.</p>
        </div>

        <form @submit.prevent="submit" class="auth-form">
          <div class="form-group">
            <label for="login-email" class="label">Email</label>
            <input id="login-email" type="email" x-model.trim="email" autocomplete="email" class="input" required>
            <p class="form-help">Gunakan email yang Anda daftarkan di GG-Mart.</p>
          </div>

          <div class="form-group">
            <label for="login-password" class="label">Kata Sandi</label>
            <div class="auth-password-wrap">
              <input id="login-password" :type="showPassword ? 'text' : 'password'" x-model="password"
                autocomplete="current-password" class="input pr-12" required>
              <button type="button" class="auth-password-toggle" @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                <span x-text="showPassword ? 'Sembunyikan' : 'Lihat'"></span>
              </button>
            </div>
          </div>

          <div class="flex justify-end">
            <button type="button" class="auth-link" @click="forgotModal = true; forgotEmail = email; forgotError = ''; forgotSuccess = false">
              Lupa kata sandi?
            </button>
          </div>

          <div x-show="error" x-cloak class="auth-alert auth-alert-error" role="alert" x-text="error"></div>

          <button type="submit" class="btn-primary auth-submit" :disabled="loading || !email || !password">
            <span x-text="loading ? 'Memeriksa akun...' : 'Masuk'"></span>
          </button>
        </form>

        <div class="auth-footer-text">
          Belum punya akun?
          <a :href="BASE_URL + '/register'" class="auth-link">Daftar sekarang</a>
        </div>

        <div x-show="forgotModal" x-cloak class="modal-backdrop" @keydown.escape.window="forgotModal = false">
          <div class="modal-box" @click.outside="forgotModal = false">
            <template x-if="!forgotSuccess">
              <div>
                <h2 class="auth-modal-title">Lupa kata sandi?</h2>
                <p class="auth-modal-text">Masukkan email akun Anda. Jika email ditemukan, instruksi untuk membuat kata sandi baru akan dikirim.</p>

                <div class="form-group mt-5">
                  <label for="forgot-email" class="label">Email</label>
                  <input id="forgot-email" type="email" x-model.trim="forgotEmail" autocomplete="email"
                    class="input" required>
                </div>

                <div x-show="forgotError" x-cloak class="auth-alert auth-alert-error mt-4" role="alert" x-text="forgotError"></div>

                <div class="auth-modal-actions">
                  <button type="button" class="btn-secondary" @click="forgotModal = false" :disabled="forgotLoading">Batal</button>
                  <button type="button" class="btn-primary" @click="submitForgot" :disabled="forgotLoading || !forgotEmail">
                    <span x-text="forgotLoading ? 'Mengirim...' : 'Kirim Instruksi'"></span>
                  </button>
                </div>
              </div>
            </template>

            <template x-if="forgotSuccess">
              <div class="auth-success-state">
                <div class="auth-state-icon auth-state-icon-success"><?= masterIconSvg('check-circle', 'h-6 w-6') ?></div>
                <h2 class="auth-modal-title">Instruksi terkirim</h2>
                <p class="auth-modal-text">Periksa email Anda untuk melanjutkan proses reset kata sandi.</p>
                <button type="button" class="btn-primary mt-5" @click="forgotModal = false">Tutup</button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<script>
  function loginForm() {
    return {
      email: '',
      password: '',
      showPassword: false,
      loading: false,
      error: '',
      forgotModal: false,
      forgotEmail: '',
      forgotLoading: false,
      forgotError: '',
      forgotSuccess: false,

      async submitForgot() {
        if (!this.forgotEmail || this.forgotLoading) return;
        this.forgotLoading = true;
        this.forgotError = '';
        try {
          const res = await API.post('/auth/request-reset', {
            email: this.forgotEmail
          });
          if (res.success) {
            this.forgotSuccess = true;
          } else {
            this.forgotError = res.message || 'Permintaan reset gagal diproses.';
          }
        } catch (error) {
          this.forgotError = error.message || 'Tidak dapat menghubungi server.';
        } finally {
          this.forgotLoading = false;
        }
      },

      async submit() {
        if (!this.email || !this.password || this.loading) return;
        this.loading = true;
        this.error = '';
        try {
          const res = await API.post('/auth/login', {
            email: this.email,
            password: this.password,
          });

          if (!res.success) {
            this.error = res.message || 'Email atau kata sandi tidak valid.';
            return;
          }

          Alpine.store('ui').toast('Login berhasil.');
          const role = res.data?.role || 'pelanggan';
          const destination = role === 'kasir' ? '/admin/kasir' : ['admin', 'pimpinan'].includes(role) ? '/admin' : '/';
          setTimeout(() => {
            window.location.href = BASE_URL + destination;
          }, 500);
        } catch (error) {
          this.error = error.message || 'Login gagal. Silakan coba lagi.';
        } finally {
          this.loading = false;
        }
      }
    }
  }
</script>
