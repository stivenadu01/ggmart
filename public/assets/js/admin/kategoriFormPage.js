const kategoriFormPage = (act, id) => ({
  form: {
    nama_kategori: '',
    deskripsi: ''
  },
  formTitle: act === 'edit' ? 'Edit Kategori' : 'Tambah Kategori',
  isEdit: act === 'edit',

  async initPage() {
    if (this.isEdit && id) await this.fetchData(id);
  },

  async fetchData(id) {
    const res = await fetch(`${baseUrl}/api/kategori?id=${id}`);
    const data = await res.json();
    data.success ? this.form = data.data : showFlash(data.message, 'warning');
  },

  async submitForm() {
    const formData = new FormData();
    formData.append("nama_kategori", this.form.nama_kategori);
    formData.append("deskripsi", this.form.deskripsi);
    if (this.isEdit) formData.append("_method", "PUT");

    const url = this.isEdit ? `${baseUrl}/api/kategori?id=${id}` : `${baseUrl}/api/kategori`;
    const res = await fetch(url, {
      method: 'POST',
      body: formData
    });
    const data = await res.json();

    if (data.success) {
      showFlash(`kategori berhasil ${this.isEdit ? 'di edit' : 'di tambahkan'}`);
      setInterval(() => {
        window.location.href = `${baseUrl}/admin/kategori/`
      }, 1000);
    } else {
      showFlash(data.message, 'error');
    }
  }
})