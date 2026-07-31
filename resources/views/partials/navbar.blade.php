<!-- Navbar Partial -->
<header class="h-20 bg-hostinger-card/60 backdrop-blur-md border-b border-hostinger-border px-6 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center space-x-4">
        <!-- Mobile Menu Toggle Button -->
        <button class="md:hidden p-2 text-gray-400 hover:text-white rounded-lg border border-hostinger-border">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <h1 class="text-lg font-bold text-white tracking-tight">
            @yield('title', 'Dashboard')
        </h1>
    </div>

    <!-- Status Indicator & User Quick Menu -->
    <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Cloud Engine Active</span>
        </div>

        <div class="h-6 w-px bg-hostinger-border"></div>

        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center font-bold text-white text-sm shadow-md shadow-indigo-500/20">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
            <span class="text-sm font-semibold text-gray-200 hidden sm:inline-block">
                {{ Auth::user()->name }}
            </span>
        </div>
    </div>
</header>
