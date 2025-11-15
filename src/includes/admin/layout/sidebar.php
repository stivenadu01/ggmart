<div x-show="!fullscreen" x-transition.opacity>
  <aside
    x-cloak
    :class="sidebarCollapse ? 'w-16' : 'w-64'"
    class="w-16 hidden lg:flex flex-col bg-white border-r shadow-md transition-all duration-200 fixed inset-y-0 left-0 z-10">

    <!-- Header Sidebar -->
    <div class="flex items-center justify-between px-4 py-2 border-b">
      <div class="flex items-center">
        <a :href="baseUrl" x-show="!sidebarCollapse" class="h-10 w-10 p-1">
          <img :src="assetsUrl + 'logo.png'" alt="GG MART" class="h-full w-full object-contain">
        </a>
        <span x-show="!sidebarCollapse" class="font-bold text-xl text-gg-primary">
          Menu
        </span>
      </div>

      <!-- Tombol Tutup Sidebar -->
      <button x-show="!sidebarCollapse" @click="toggleSidebar" class="p-1 rounded hover:bg-gray-100 cursor-w-resize" title="Tutup Sidebar">
        <div class="h-6 w-6" x-html="icon('arrowkiri2')"></div>
      </button>
      <!-- Tombol Buka Sidebar -->
      <button
        @click="toggleSidebar"
        x-show="sidebarCollapse"
        class="relative flex justify-center items-center rounded hover:bg-gray-100 transition-all duration-100 group cursor-e-resize"
        title="Buka Sidebar">

        <!-- Logo -->
        <div class="h-10 w-10 p-1 transition-opacity duration-200 group-hover:opacity-0">
          <img :src="assetsUrl + 'logo.png'" alt="GG MART" class="h-full w-full object-contain">
        </div>

        <!-- aroow kanan muncul saat hover -->
        <div class="absolute inset-0 flex justify-center items-center opacity-0 group-hover:opacity-100 transition-opacity duration-100">
          <div class="h-6 w-6" x-html="icon('arrowkanan2')"></div>
        </div>
      </button>

    </div>

    <!-- Menu -->
    <?php include INCLUDES_PATH . "/admin/layout/list_menu.php" ?>
    </nav>
  </aside>


  <!-- MOBILE SIDEBAR OVERLAY -->
  <div
    x-cloak
    x-show="sidebarOpen"
    class="fixed inset-0 flex z-30 lg:hidden"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-x-full"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 -translate-x-full">

    <div @click="sidebarOpen = false" class="fixed inset-0 bg-black bg-opacity-50"></div>

    <aside class="relative w-64 bg-white shadow-md flex flex-col z-50">
      <a :href="baseUrl" class="flex items-center justify-between p-2 border-b">
        <span><img :src="assetsUrl  + '/logo.png'" alt="" class="h-10 inline-block"></span>
        <span class="text-gg-primary font-bold">Menu</span>
        <button @click="sidebarOpen = false" class="p-2 rounded hover:bg-gray-100">
          <span class="h-5 w-5" x-html="icon('arrowkiri2')"></span>
        </button>
      </a>

      <!-- MENU -->
      <?php include INCLUDES_PATH . "/admin/layout/list_menu.php" ?>
    </aside>
  </div>
</div>