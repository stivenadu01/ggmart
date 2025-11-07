function loginPage() {
  return {
    email: '',
    password: '',
    rememberMe: false,
    submitting: false,

    async fetchLogin() {
      try {
        this.submitting = true;
        if (!this.email || !this.password) {
          showFlash("Email & Kata sandi wajib di isi!", 'warning');
          return;
        }
        console.log(currentUser)
        const url = `${baseUrl}/api/auth?mode=login`;
        const res = await fetch(url, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            email: this.email,
            password: this.password,
            "remember-me": this.rememberMe
          })
        });

        const data = await res.json();
        if (data.success) {
          if (data.user.role == 'admin' || data.user.role == 'kasir' || data.user.role == 'manager') {
            showFlash(data.message + 'Mengarahkan ke dashboard...');
            setTimeout(() => {
              window.location.href = baseUrl + '/admin/dashboard';
            }, 1000)
          }
        } else {
          showFlash(data.message, 'error');
        }

      } catch (error) {
        console.error(error);
        showFlash('Terjadi kesalahan koneksi.', 'error');
      } finally {
        this.submitting = false;
      }
    }
  }
}
