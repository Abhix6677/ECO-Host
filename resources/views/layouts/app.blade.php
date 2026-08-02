<!DOCTYPE html>
<html lang="en">
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
                            50: '#f0f3ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#1e1b4b',
                        },
                        hostinger: {
                            dark: '#F8FAFC',
                            card: '#ffffff',
                            cardHover: '#f1f5f9',
                            border: '#e2e8f0',
                            accent: '#4f46e5',
                            glow: 'rgba(79, 70, 229, 0.15)'
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
            background-color: #F8FAFC;
            color: #1e293b;
        }
        .glow-effect {
            box-shadow: 0 10px 30px -5px rgba(79, 70, 229, 0.15);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        /* --- High-Impression Interactive Button Animations --- */
        button, a.btn, input[type="submit"] {
            position: relative;
            overflow: hidden;
            user-select: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        button:active, a.btn:active, input[type="submit"]:active {
            transform: scale(0.96) translateY(1px);
        }
        .btn-pulse-glow {
            animation: pulseGlowRing 2s infinite;
        }
        @keyframes pulseGlowRing {
            0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
            70% { box-shadow: 0 0 0 12px rgba(79, 70, 229, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
        }
        .shimmer-btn {
            background-size: 200% 100%;
            animation: shimmerMove 3s infinite linear;
        }
        @keyframes shimmerMove {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Attach ripple click animation to all buttons globally
            document.body.addEventListener('click', (e) => {
                const btn = e.target.closest('button, a.btn, .animated-btn');
                if (!btn) return;
                
                const circle = document.createElement('span');
                const diameter = Math.max(btn.clientWidth, btn.clientHeight);
                const radius = diameter / 2;
                const rect = btn.getBoundingClientRect();
                
                circle.style.width = circle.style.height = `${diameter}px`;
                circle.style.left = `${e.clientX - rect.left - radius}px`;
                circle.style.top = `${e.clientY - rect.top - radius}px`;
                circle.classList.add('ripple-wave');
                
                const existingRipple = btn.getElementsByClassName('ripple-wave')[0];
                if (existingRipple) {
                    existingRipple.remove();
                }
                
                btn.appendChild(circle);
                setTimeout(() => circle.remove(), 600);
            });
        });
    </script>
    <style>
        .ripple-wave {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: rippleAnim 0.6s linear;
            background-color: rgba(255, 255, 255, 0.35);
            pointer-events: none;
        }
        @keyframes rippleAnim {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="antialiased bg-hostinger-dark text-slate-800 flex min-h-screen">

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
