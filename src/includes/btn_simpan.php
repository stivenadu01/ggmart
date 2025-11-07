<button type="submit"
  class="btn btn-primary flex items-center gap-2"
  :disabled="submitting">
  <template x-if="!submitting">
    <div class="flex gap-2 items-center ">
      <span class="h-5 w-5" x-html="icon('save')"></span>Simpan
    </div>
  </template>
  <template x-if="submitting">
    <div class="flex items-center gap-2"><span class="h-5 w-5" x-html="icon('spin')"></span>
      Memproses...
  </template>
</button>