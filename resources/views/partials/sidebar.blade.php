<!-- Sidebar Partial -->
<aside class="w-64 bg-hostinger-card border-r border-hostinger-border flex flex-col justify-between hidden md:flex min-h-screen">
    <div>
        <!-- Logo Header -->
        <div class="h-20 flex items-center px-6 border-b border-hostinger-border">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i data-lucide="cloud-lightning" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-white via-gray-200 to-indigo-300 bg-clip-text text-transparent">
                        EcoHost
                    </span>
                    <span class="block text-[10px] font-semibold text-indigo-400 tracking-wider uppercase">STATIC CLOUD</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1.5">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-gray-400 hover:text-white hover:bg-hostinger-cardHover' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ Route::has('websites.index') ? route('websites.index') : '#' }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('websites.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-gray-400 hover:text-white hover:bg-hostinger-cardHover' }}">
                <i data-lucide="globe" class="w-5 h-5"></i>
                <span>My Websites</span>
            </a>

            <a href="{{ Route::has('websites.create') ? route('websites.create') : '#' }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 text-gray-400 hover:text-white hover:bg-hostinger-cardHover">
                <i data-lucide="upload-cloud" class="w-5 h-5 text-indigo-400"></i>
                <span>Upload Website</span>
            </a>

            <a href="{{ Route::has('deployments.index') ? route('deployments.index') : '#' }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('deployments.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-gray-400 hover:text-white hover:bg-hostinger-cardHover' }}">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                <span>Deployments</span>
            </a>

            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-semibold text-gray-400 hover:text-white hover:bg-hostinger-cardHover transition-all duration-200">
                <i data-lucide="settings" class="w-5 h-5"></i>
                <span>Settings</span>
            </a>
        </nav>
    </div>

    <!-- Bottom User Section -->
    <div class="p-4 border-t border-hostinger-border">
        <div class="glass-card p-3 rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div class="w-9 h-9 rounded-lg bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 font-bold">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="truncate">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="p-2 text-gray-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
