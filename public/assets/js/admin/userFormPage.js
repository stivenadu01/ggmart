
const userFormPage = (id) => ({
  mode: id ? "edit" : "tambah",
  submitting: false,
  form: {
    nama: "",
    email: "",
    password: "",
    rePassword: "",
    role: "",
  },

  async initForm() {
    if (this.mode === "edit") {
      this.submitting = true;
      try {
        const res = await fetch(`${baseUrl}/api/user?id=${id}`);
        const data = await res.json();
        if (data.success) {
          this.form.nama = data.data.nama;
          this.form.email = data.data.email;
          this.form.role = data.data.role;
        } else {
          showFlash(data.message, "error");
        }
      } catch (err) {
        console.error("Load user error:", err);
        showFlash("Gagal memuat data user", "error");
      } finally {
        this.submitting = false;
      }
    }
  },

  isValidEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email);
  },

  async submitForm() {
    if (!this.form.nama || !this.form.email || !this.form.role) {
      showFlash("Lengkapi semua field wajib!", "warning");
      return;
    }

    if (!this.isValidEmail(this.form.email)) {
      showFlash("Format email tidak valid!", "warning");
      return;
    }

    if (this.mode === "tambah") {
      if (!this.form.password || !this.form.rePassword) {
        showFlash("Password dan konfirmasi wajib diisi!", "warning");
        return;
      }
      if (this.form.password !== this.form.rePassword) {
        showFlash("Konfirmasi password tidak cocok!", "warning");
        return;
      }
    }

    this.submitting = true;
    try {
      const body = new FormData();
      Object.entries(this.form).forEach(([key, value]) => {
        if (value !== "") body.append(key, value);
      });
      this.mode === "edit" ? body.append('_method', 'PUT') : '';

      let url = `${baseUrl}/api/user`;
      this.mode == 'edit' ? url += `?id=${id}` : '';
      const res = await fetch(url, {
        method: 'POST',
        body
      });
      const data = await res.json();

      if (data.success) {
        showFlash(data.message);
        setTimeout(() => (window.location.href = `${baseUrl}/admin/user`), 800);
      } else {
        showFlash(data.message, "error");
      }
    } catch (err) {
      console.error("Submit user error:", err);
      showFlash("Terjadi kesalahan saat menyimpan data", "error");
    } finally {
      this.submitting = false;
    }
  },
});