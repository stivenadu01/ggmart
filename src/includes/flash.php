<!-- Global Flash Message -->
<div
  x-data="globalFlash()"
  x-show="visible"
  x-transition:enter="transition-opacity duration-500"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition-opacity duration-300"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed inset-0 top-16 max-h-fit flex justify-center z-[9999]">
  <div
    class="flex justify-between items-center gap-3 p-4 rounded-lg text-white shadow-xl max-w-md w-full mx-4"
    :class="{
      'bg-green-600/80' : type === 'success',
      'bg-yellow-600/80' :type === 'warning',
      'bg-blue-600/80' : type === 'info',
      'bg-red-600/80' : type === 'error'
    }">
    <div class="flex justify-center items-center gap-2">
      <span class="w-5 h-5" x-html="icon(type)"></span>
      <span x-text="message"></span>
    </div>
    <button @click="visible = false">
      <span class="h-5 w-5" x-html="icon('x')"></span>
    </button>
  </div>
</div>


<script>
  function globalFlash() {
    return {
      visible: false,
      message: '',
      type: 'success',

      show(message, type = 'success') {
        this.message = message;
        this.type = type;
        this.visible = true;
        setTimeout(() => (this.visible = false), 2500);
      },

      init() {
        // Dengarkan event flash global
        document.addEventListener('show-flash', e => {
          const {
            message,
            type
          } = e.detail;
          this.show(message, type);
        });
      }
    }
  }

  // Helper global (supaya bisa dipanggil dari mana saja)
  window.showFlash = (message, type = 'success') => {
    document.dispatchEvent(
      new CustomEvent('show-flash', {
        detail: {
          message,
          type
        }
      })
    );
  };
</script>