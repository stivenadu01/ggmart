<header x-data="" class="fixed top-0 z-20 w-full text-slate-900 font-sans bg-white shadow-sm">
  <div x-cloak class="md:px-6 lg:px-12 xl:px-16 py-3">
    <!-- 🔸 MAIN HEADER -->
    <div class="flex flex-wrap justify-between items-center px-2 md:px-8 md:py-3">
      <div class="flex gap-x-4 items-center">
        <!-- LOGO -->
        <a :href="baseUrl" class="hidden md:flex items-center space-x-1">
          <img :src="assetsUrl + '/logo.png'" alt="GG MART" class="h-9 filter logo">
          <span x-cloak class="text-2xl font-bold tracking-tight text-gg-primary drop-shadow hidden md:block">GG MART</span>
        </a>
        <button onclick="window.history.back()" class="md:hidden flex w-7 stroke-none items-center" x-html="icon('arrowkiri1')">
        </button>
        <h1 class="md:text-2xl text-xl"><?= $pageTitle ?></h1>
      </div>

      <button class="md:hidden w-7" @click="navOpen = !navOpen" x-html="icon('menu')"></button>
    </div>
  </div>
</header>

<main class="mt-[3.27rem] md:mt-[5.2rem] flex min-h-full min-w-full">