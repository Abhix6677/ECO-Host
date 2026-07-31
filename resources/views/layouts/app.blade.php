<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EcoHost') }} - Hostinger Inspired Web Hosting</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        hostinger: {
                            dark: '#0b0f19',
                            card: '#131b2e',
                            cardHover: '#1a243d',
                            border: '#232f48',
                            accent: '#6366f1',
                            glow: 'rgba(99, 102, 241, 0.15)'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f19;
            color: #f3f4f6;
        }
        .glow-effect {
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.12);
        }
        .glass-card {
            background: rgba(19, 27, 46, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid #232f48;
        }
    </style>
</head>
<body class="antialiased bg-hostinger-dark text-gray-100 flex min-h-screen">

    @auth
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Navbar -->
            @include('partials.navbar')

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <!-- Flash Notification Messages -->
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
                            <span class="font-medium text-sm">{!! session('success') !!}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-400 hover:text-emerald-200">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
                            <span class="font-medium text-sm">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-400 hover:text-rose-200">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    @else
        <!-- Guest Content (Auth Layout) -->
        <div class="flex-1 flex items-center justify-center min-h-screen p-4">
            @yield('content')
        </div>
    @endauth

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
