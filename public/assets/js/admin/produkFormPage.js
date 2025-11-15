function produkFormPage(act, id) {
  return {
    page: 1,
    kategori: [],
    preview: null,
    submitting: false,
    isEdit: act === 'edit',
    formTitle: act === 'edit' ? 'Edit Produk' : 'Tambah Produk',
    fileName: '',
    dragOver: false,
    form: {
      id_kategori: '',
      nama_produk: '',
      harga_jual: '',
      satuan_dasar: '',
      deskripsi: '',
      is_lokal: 0,
      gambar: ''
    },

    async initPage() {
      await this.fetchKategori();
      if (this.isEdit && id) await this.fetchProduk(id);
    },

    async fetchKategori() {
      const res = await fetch(`${baseUrl}/api/kategori?mode=all`);
      const data = await res.json();
      data.success ? this.kategori = data.data : showFlash(data.message, 'warning');
    },

    async fetchProduk(kode) {
      const res = await fetch(`${baseUrl}/api/produk?k=${kode}`);
      const data = await res.json();
      if (data.success) {
        this.form = { ...data.data }
        if (data.data.gambar) this.preview = `${uploadsUrl}/${data.data.gambar}`;
      } else showFlash(data.message, 'warning');
    },

    // ---- handler input file manual ----
    onFileChange(e) {
      const file = e.target.files[0];
      if (file) this.setFile(file);
    },

    // ---- handler drag-drop ----
    handleDrop(e) {
      this.dragOver = false;
      const file = e.dataTransfer.files[0];
      if (file) this.setFile(file);
    },

    // ---- reusable untuk preview ----
    setFile(file) {
      if (!file.type.startsWith("image/")) {
        showFlash("File harus berupa gambar!", "warning");
        return;
      }
      this.form.gambar = file;
      this.fileName = file.name;
      this.preview = URL.createObjectURL(file);
    },

    async submitForm() {
      try {
        if (this.submitting) return;
        this.submitting = true;
        const formData = new FormData();
        for (const key in this.form) formData.append(key, this.form[key]);
        if (this.isEdit) formData.append("_method", "PUT");

        const url = this.isEdit
          ? `${baseUrl}/api/produk?k=${id}`
          : `${baseUrl}/api/produk`;

        const res = await fetch(url, { method: "POST", body: formData });
        const data = await res.json();

        if (data.success) {
          showFlash(this.isEdit ? "Produk berhasil diperbarui!" : "Produk berhasil ditambahkan!");
          setTimeout(() => window.location.href = `${baseUrl}/admin/produk`, 1000);
        } else {
          showFlash("Gagal menyimpan produk: " + data.message, 'error');
        }
      } catch (error) {
        console.error(error);
      } finally {
        this.submitting = false;
      }
    }
  };
}
