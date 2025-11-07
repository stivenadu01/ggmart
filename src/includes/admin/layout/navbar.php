<!-- NAVBAR -->
<header x-data="headerAdmin()" class="flex items-center justify-between bg-white border-b px-4 py-2 relative z-10 h-14"
  :class="sidebarCollapse ? 'lg:ml-16' : 'lg:ml-64'" x-show="!fullscreen" x-transition.opacity>
  <div class="flex items-center space-x-2">
    <!-- Tombol menu (mobile) -->
    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded hover:bg-gray-100">
      <span class="h-5 w-5" x-html="icon('menu')"></span>
    </button>
  </div>
  <div>
    <button @click="logout" class="hover:text-red-500 gap-x-1 flex items-center">
      <span class="h-5 w-5" x-html="icon('logout')"></span>
      <span class="md:block hidden">Logout</span>
    </button>
  </div>
</header>

<script>
  const headerAdmin = () => ({
    async logout() {
      try {
        const res = await fetch(`${baseUrl}/api/auth`, {
          method: "DELETE"
        })
        const data = await res.json();
        if (data.success) {
          showFlash(data.message)
          setInterval(() => {
            window.location.href = baseUrl
          }, 1000);
        } else {
          showFlash(data.message, 'error')
        }
      } catch (error) {
        showFlash(error, 'error')
      }
    }
  })
</script>