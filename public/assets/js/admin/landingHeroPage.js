function landingHeroPage() {
  return {
    slides: [],
    index: 0,
    preview: null,
    submitting: false,
    dragOver: false,
    get current() {
      return this.slides[this.index] || {};
    },

    async initPage() {
      await this.fetchSlides();
      this.applyPreview();
    },

    async fetchSlides() {
      const res = await fetch(`${baseUrl}/api/landingHero`);
      const data = await res.json();

      if (data.success) {
        this.slides = data.data;
      } else {
        showFlash("Gagal memuat data hero", "warning");
      }
    },

    nextSlide() {
      if (this.index < this.slides.length - 1) {
        this.index++;
        this.applyPreview();
      }
    },

    prevSlide() {
      if (this.index > 0) {
        this.index--;
        this.applyPreview();
      }
    },

    applyPreview() {
      this.preview = this.current?.image_path
        ? `${uploadsUrl}/${this.current.image_path}`
        : null;
    },

    onFileChange(e) {
      const file = e.target.files[0];
      if (file) this.setFile(file);
    },

    setFile(file) {
      if (!file.type.startsWith("image/")) {
        showFlash("File harus berupa gambar!", "warning");
        return;
      }

      this.current._file = file;
      this.preview = URL.createObjectURL(file);
    },

    handleDrop(e) {
      this.dragOver = false;
      const file = e.dataTransfer.files[0];
      if (file) this.setFile(file);
    },

    async saveSlide() {
      if (this.submitting) return;
      try {
        this.submitting = true;
        const formData = new FormData();
        for (const key in this.current) {
          if (key !== "_file" && this.current[key] !== null) {
            formData.append(key, this.current[key]);
          }
        }
        if (this.current._file) {
          formData.append("image", this.current._file);
        }

        let url = `${baseUrl}/api/landingHero`;

        // MODE UPDATE
        if (this.current.id) {
          formData.append("_method", "PUT");
          url += `?id=${this.current.id}`;
        }

        const res = await fetch(url, {
          method: "POST",
          body: formData
        });

        const data = await res.json();
        this.submitting = false;

        if (data.success) {
          showFlash("Slide berhasil disimpan!");
          await this.fetchSlides();
          this.applyPreview();
        } else {
          showFlash(data.message, "error");
        }
      } catch (error) {
        showFlash("Terjadi kesalahan saat menyimpan slide", "error");
      } finally {
        this.submitting = false;
      }
    },

    async deleteSlide() {
      if (!confirm("Yakin hapus slide ini?")) return;
      try {
        const res = await fetch(`${baseUrl}/api/landingHero?id=${this.current.id}`, {
          method: "DELETE"
        });

        const data = await res.json();

        if (data.success) {
          showFlash(data.message);
          await this.fetchSlides();
          this.index = 0;
          this.applyPreview();
        } else {
          showFlash(data.message, "error");
        }
      } catch (error) {
        showFlash("Terjadi kesalahan saat menghapus slide", "error");
      } finally {
        this.submitting = false;
      }
    },

    addSlide() {
      this.slides.push({
        id: null,
        title: "",
        subtitle: "",
        text: "",
        cta_primary_text: "",
        cta_primary_url: "",
        cta_secondary_text: "",
        cta_secondary_url: "",
        image_path: "",
        _file: null,
      });

      this.index = this.slides.length - 1;
      this.preview = null;
    },
  };
}
