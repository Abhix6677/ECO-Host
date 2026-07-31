<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EcoHost — Affordable &amp; Reliable Web Hosting Platform</title>
    <meta name="description" content="Deploy your static HTML/CSS/JS websites with 1-click ZIP upload, automated validation, instant Cloudflare Tunnels, and 99.9% uptime guaranteed.">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        heading: ['Outfit', 'sans-serif'],
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
                        hostdark: '#0f172a',
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-bg {
            background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.08) 0%, rgba(248, 250, 252, 0) 70%);
        }
        .card-shadow {
            box-shadow: 0 10px 30px -5px rgba(79, 70, 229, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased font-sans flex flex-col min-h-screen">

    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-blue-600 text-white text-xs font-semibold py-2.5 px-4 text-center">
        <div class="max-w-7xl mx-auto flex items-center justify-center space-x-2">
            <span class="bg-white/20 text-white px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wider font-bold">New Release</span>
            <span>🚀 1-Click ZIP Deployment Engine &amp; Instant Cloudflare Tunnel Integration is now live!</span>
            <a href="#pricing" class="underline hover:text-indigo-200 transition ml-2">View Hosting Plans &rarr;</a>
        </div>
    </div>

    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

            <!-- Logo -->
            <a href="/" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition">
                    <i data-lucide="zap" class="w-6 h-6 fill-current"></i>
                </div>
                <div>
                    <span class="font-heading font-extrabold text-2xl text-slate-900 tracking-tight">Eco<span class="text-indigo-600">Host</span></span>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-widest -mt-1">Web Hosting Platform</span>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold text-slate-600">
                <a href="#features" class="hover:text-indigo-600 transition">Features</a>
                <a href="#pricing" class="hover:text-indigo-600 transition">Hosting Plans</a>
                <a href="#services" class="hover:text-indigo-600 transition">Technology</a>
                <a href="#why-us" class="hover:text-indigo-600 transition">Why EcoHost</a>
                <a href="#support" class="hover:text-indigo-600 transition">Support</a>
            </nav>

            <!-- Actions -->
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center space-x-2">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Go to Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-slate-700 hover:text-indigo-600 px-3 py-2 transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/25 transition transform active:scale-95 flex items-center space-x-2">
                        <span>Get Started Free</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                @endauth
            </div>

        </div>
    </header>

    <main class="flex-grow">

        <!-- Hero Section -->
        <section class="relative pt-12 pb-20 md:pt-20 md:pb-28 hero-bg overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                    <!-- Left Text -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        <!-- Badge -->
                        <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>✅ 99.9% Uptime &nbsp;|&nbsp; 🔒 Free SSL &nbsp;|&nbsp; 🚀 NVMe SSD Speed</span>
                        </div>

                        <!-- Headline -->
                        <h1 class="font-heading text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                            Affordable &amp; Ultra-Fast <br class="hidden sm:inline">
                            <span class="gradient-text">Web Hosting in India</span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                            Deploy your static website with a single <code class="bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-mono text-sm font-semibold">website.zip</code> upload. Automated extraction, security scanning, and instant Cloudflare Tunnel hosting starting at ₹39/month.
                        </p>

                        <!-- Feature List -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm text-slate-700 font-semibold pt-2">
                            <div class="flex items-center justify-center lg:justify-start space-x-2">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                                </div>
                                <span>1-Click ZIP Deploy</span>
                            </div>
                            <div class="flex items-center justify-center lg:justify-start space-x-2">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                                </div>
                                <span>Zero Downtime Migration</span>
                            </div>
                            <div class="flex items-center justify-center lg:justify-start space-x-2">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                                </div>
                                <span>24/7 Live Support</span>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                            <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-base rounded-2xl shadow-xl shadow-indigo-600/30 transition transform hover:-translate-y-0.5 text-center">
                                Start Hosting for ₹39/mo
                            </a>
                            <a href="#pricing" class="w-full sm:w-auto px-7 py-4 bg-white border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-600 font-bold text-base rounded-2xl shadow-sm transition text-center">
                                Explore Plans &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Right Graphic -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-200/80 bg-white p-2">
                            <img src="{{ asset('images/hero.jpg') }}" alt="EcoHost Control Panel Preview" class="rounded-2xl w-full h-auto object-cover shadow-inner">
                            
                            <!-- Floating Stat Badge 1 -->
                            <div class="absolute top-6 left-6 bg-white/90 backdrop-blur-md border border-slate-200/80 p-3 rounded-2xl shadow-lg flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center font-bold">
                                    ⚡
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-semibold">Extraction Speed</p>
                                    <p class="text-sm font-bold text-slate-800">&lt; 250ms Instant</p>
                                </div>
                            </div>

                            <!-- Floating Stat Badge 2 -->
                            <div class="absolute bottom-6 right-6 bg-slate-900/90 text-white backdrop-blur-md border border-slate-700/50 p-3.5 rounded-2xl shadow-xl flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-500 flex items-center justify-center text-white font-bold">
                                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-400 font-semibold">Security Sandbox</p>
                                    <p class="text-xs font-bold text-indigo-300">Anti-Zip Bomb Active</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Stats Counter Strip -->
        <section class="bg-indigo-900 text-white py-10 border-y border-indigo-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <p class="font-heading text-3xl sm:text-4xl font-extrabold text-indigo-200">10,000+</p>
                        <p class="text-xs text-indigo-300 uppercase tracking-wider font-semibold mt-1">Websites Hosted</p>
                    </div>
                    <div>
                        <p class="font-heading text-3xl sm:text-4xl font-extrabold text-indigo-200">99.9%</p>
                        <p class="text-xs text-indigo-300 uppercase tracking-wider font-semibold mt-1">Uptime Guaranteed</p>
                    </div>
                    <div>
                        <p class="font-heading text-3xl sm:text-4xl font-extrabold text-indigo-200">&lt; 50ms</p>
                        <p class="text-xs text-indigo-300 uppercase tracking-wider font-semibold mt-1">Global Latency</p>
                    </div>
                    <div>
                        <p class="font-heading text-3xl sm:text-4xl font-extrabold text-indigo-200">24/7/365</p>
                        <p class="text-xs text-indigo-300 uppercase tracking-wider font-semibold mt-1">Technical Support</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Plans Section -->
        <section id="pricing" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">Simple &amp; Transparent Pricing</span>
                    <h2 class="font-heading text-3xl sm:text-4xl font-extrabold text-slate-900">Choose the Perfect Hosting Plan</h2>
                    <p class="text-slate-600 text-base">All plans include automated ZIP validation, instant extraction, free SSL certificates, and Cloudflare Tunnel deployment.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">

                    <!-- Plan 1: Bronze -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 card-shadow flex flex-col justify-between hover:border-indigo-300 transition">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-heading font-extrabold text-xl text-slate-900">Starter Bronze</h3>
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">Personal</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-6">Ideal for landing pages &amp; small personal portfolios.</p>
                            
                            <div class="mb-6">
                                <span class="font-heading text-4xl font-extrabold text-slate-900">₹39</span>
                                <span class="text-slate-500 font-medium text-sm">/ month</span>
                            </div>

                            <ul class="space-y-3.5 text-sm text-slate-700 mb-8 border-t border-slate-100 pt-6">
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span><strong>1 Website</strong> Hosting</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span><strong>10 MB</strong> Storage Limit</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>1-Click ZIP Extraction</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>Free SSL Certificate</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>Cloudflare Tunnel URL</span>
                                </li>
                            </ul>
                        </div>

                        <a href="{{ route('register') }}" class="w-full py-3.5 px-4 bg-slate-900 hover:bg-indigo-600 text-white font-bold rounded-xl text-sm transition text-center block">
                            Choose Bronze Plan
                        </a>
                    </div>

                    <!-- Plan 2: Silver (Popular) -->
                    <div class="bg-white rounded-3xl p-8 border-2 border-indigo-600 card-shadow flex flex-col justify-between relative transform lg:-translate-y-2">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-600 to-blue-600 text-white text-xs font-extrabold uppercase tracking-wider px-4 py-1 rounded-full shadow-md">
                            Most Popular Choice
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-4 mt-2">
                                <h3 class="font-heading font-extrabold text-xl text-slate-900">Business Silver</h3>
                                <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold">Growth</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-6">Designed for growing businesses &amp; multiple static sites.</p>
                            
                            <div class="mb-6">
                                <span class="font-heading text-4xl font-extrabold text-slate-900">₹99</span>
                                <span class="text-slate-500 font-medium text-sm">/ month</span>
                            </div>

                            <ul class="space-y-3.5 text-sm text-slate-700 mb-8 border-t border-slate-100 pt-6">
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span><strong>5 Websites</strong> Hosting</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span><strong>50 MB</strong> Storage Limit</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>1-Click ZIP Extraction</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>Anti-Zip Bomb Security</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>Instant Cloudflare Tunnel</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>Priority 24/7 Support</span>
                                </li>
                            </ul>
                        </div>

                        <a href="{{ route('register') }}" class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-indigo-600/30 text-center block">
                            Get Started Now
                        </a>
                    </div>

                    <!-- Plan 3: Gold -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-200 card-shadow flex flex-col justify-between hover:border-indigo-300 transition">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-heading font-extrabold text-xl text-slate-900">Pro Gold</h3>
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">Unlimited</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-6">For agencies, freelancers &amp; developers handling many sites.</p>
                            
                            <div class="mb-6">
                                <span class="font-heading text-4xl font-extrabold text-slate-900">₹199</span>
                                <span class="text-slate-500 font-medium text-sm">/ month</span>
                            </div>

                            <ul class="space-y-3.5 text-sm text-slate-700 mb-8 border-t border-slate-100 pt-6">
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span><strong>Unlimited</strong> Websites</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span><strong>250 MB</strong> High-Speed NVMe</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>Automated Security Scanning</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>Cloudflare Tunnel Public URLs</span>
                                </li>
                                <li class="flex items-center space-x-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                    <span>Real-Time Deployment Logs</span>
                                </li>
                            </ul>
                        </div>

                        <a href="{{ route('register') }}" class="w-full py-3.5 px-4 bg-slate-900 hover:bg-indigo-600 text-white font-bold rounded-xl text-sm transition text-center block">
                            Choose Gold Plan
                        </a>
                    </div>

                </div>
            </div>
        </section>

        <!-- Alternating Feature / Technology Cards Section -->
        <section id="services" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-24">

                <!-- Feature 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="space-y-6">
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">1-Click ZIP Deployment</span>
                        <h2 class="font-heading text-3xl font-extrabold text-slate-900 leading-tight">Drag, Drop &amp; Host in Seconds</h2>
                        <p class="text-slate-600 text-base leading-relaxed">
                            No complex FTP setup or server configuration. Simply pack your static website files into a <code class="bg-slate-100 text-indigo-600 px-1.5 py-0.5 rounded font-mono text-sm font-semibold">website.zip</code> archive and upload it. EcoHost automatically verifies the root <code class="bg-slate-100 text-slate-800 px-1 py-0.5 rounded font-mono text-xs">index.html</code>, extracts your assets, and serves your website instantly.
                        </p>
                        <div class="space-y-3 text-sm text-slate-700 font-medium">
                            <div class="flex items-center space-x-3">
                                <i data-lucide="check-circle-2" class="w-5 h-5 text-indigo-600"></i>
                                <span>Automatic validation for HTML, CSS, JS, images</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i data-lucide="check-circle-2" class="w-5 h-5 text-indigo-600"></i>
                                <span>Sub-second extraction speed</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-indigo-50/50 rounded-3xl p-8 border border-indigo-100">
                        <div class="bg-white rounded-2xl p-6 shadow-xl space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <span class="font-bold text-slate-800 text-sm">Upload Status</span>
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Extracted</span>
                            </div>
                            <div class="flex items-center space-x-3 bg-slate-50 p-3 rounded-xl">
                                <i data-lucide="file-archive" class="w-8 h-8 text-indigo-500"></i>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">my-landing-page.zip</p>
                                    <p class="text-[10px] text-slate-500">12 files • 1.4 MB extracted</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature 2: Cloudflare Tunnel -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="bg-slate-900 rounded-3xl p-8 text-white order-2 lg:order-1 shadow-2xl">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2 text-indigo-400 font-mono text-xs">
                                <i data-lucide="terminal" class="w-4 h-4"></i>
                                <span>cloudflared tunnel --url http://localhost:8000</span>
                            </div>
                            <div class="bg-slate-950 p-4 rounded-xl font-mono text-xs text-emerald-400 space-y-1">
                                <p class="text-slate-500"># Generated Live Public URL:</p>
                                <p class="font-bold">https://ecohost-site.trycloudflare.com/storage/sites/uuid/</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6 order-1 lg:order-2">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-widest bg-blue-50 px-3 py-1 rounded-full border border-blue-100">Global Cloudflare Tunnel</span>
                        <h2 class="font-heading text-3xl font-extrabold text-slate-900 leading-tight">Instant Public HTTPS URLs</h2>
                        <p class="text-slate-600 text-base leading-relaxed">
                            With our built-in Cloudflare Tunnel integration, your site is reachable anywhere on the web with full SSL encryption without purchasing or configuring DNS records.
                        </p>
                        <div class="space-y-3 text-sm text-slate-700 font-medium">
                            <div class="flex items-center space-x-3">
                                <i data-lucide="shield-check" class="w-5 h-5 text-blue-600"></i>
                                <span>Free automatic SSL certificate for every deployment</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i data-lucide="zap" class="w-5 h-5 text-blue-600"></i>
                                <span>Global edge caching for lightning fast load times</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Why Choose EcoHost Section -->
        <section id="why-us" class="py-20 bg-slate-50 border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">Engineered for Developers</span>
                    <h2 class="font-heading text-3xl sm:text-4xl font-extrabold text-slate-900">Why Developers Choose EcoHost</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            <i data-lucide="shield-alert" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-heading font-extrabold text-lg text-slate-900">Anti-Zip Bomb Security</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">Multi-layered validation inspects file ratios, blocks path traversal attempts, and rejects executable server-side scripts like .php, .sh, or .py.</p>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <i data-lucide="refresh-cw" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-heading font-extrabold text-lg text-slate-900">Clean Re-Deploy Engine</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">Update your website whenever you want. Re-deploying automatically clears stale files and deploys your new package with zero downtime.</p>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            <i data-lucide="terminal" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-heading font-extrabold text-lg text-slate-900">Real-Time Terminal Logs</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">Full visibility into every deployment step. Inspect log outputs right from your dashboard to debug issues instantly.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA / Support Banner -->
        <section id="support" class="py-16 bg-gradient-to-r from-indigo-700 to-blue-700 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <h2 class="font-heading text-3xl sm:text-4xl font-extrabold">Ready to Host Your Website with EcoHost?</h2>
                <p class="text-indigo-100 max-w-2xl mx-auto text-base">Join thousands of creators and developers hosting fast static sites starting at ₹39/month.</p>
                <div class="pt-2">
                    <a href="{{ route('register') }}" class="inline-flex items-center space-x-2 px-8 py-4 bg-white text-indigo-700 font-extrabold text-base rounded-2xl shadow-2xl hover:bg-indigo-50 transition transform hover:scale-105">
                        <span>Get Started Free</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold">
                            <i data-lucide="zap" class="w-5 h-5 fill-current"></i>
                        </div>
                        <span class="font-heading font-bold text-xl text-white">EcoHost</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Fast, affordable, and secure web hosting platform for static HTML, CSS &amp; JavaScript websites in India.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Product</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="#pricing" class="hover:text-white transition">Pricing Plans</a></li>
                        <li><a href="#services" class="hover:text-white transition">Cloudflare Tunnel</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Account</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Sign In</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Create Account</a></li>
                        <li><a href="{{ route('dashboard') }}" class="hover:text-white transition">User Dashboard</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Support</h4>
                    <ul class="space-y-2 text-xs">
                        <li><span>24/7 Customer Support</span></li>
                        <li><span>99.9% Uptime Guarantee</span></li>
                        <li><span>Documentation &amp; Guides</span></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} EcoHost Platform. All rights reserved.</p>
                <div class="flex space-x-6 mt-4 sm:mt-0">
                    <a href="#" class="hover:text-slate-400 transition">Privacy Policy</a>
                    <a href="#" class="hover:text-slate-400 transition">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
