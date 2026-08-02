@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<div class="space-y-8">
    <!-- Welcome Header Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-50 via-white to-blue-50 border border-hostinger-border p-6 sm:p-8">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-indigo-600 font-semibold text-xs uppercase tracking-wider mb-2">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Web Hosting Management Platform</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Welcome back, {{ Auth::user()->name }} 👋
                </h2>
                <p class="text-slate-600 text-sm mt-1 max-w-xl">
                    Host, manage and deploy your static HTML, CSS, and JavaScript projects with 1-click Cloudflare Tunnel routing.
                </p>
            </div>

            <a href="{{ Route::has('websites.create') ? route('websites.create') : '#' }}" 
               class="px-5 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-md shadow-indigo-600/20 transition transform active:scale-95 flex items-center space-x-2">
                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                <span>Deploy New Website</span>
            </a>
        </div>
        <!-- Decorative background glow -->
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-indigo-50 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Metrics 4-Grid Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Websites -->
        <div class="glass-card rounded-2xl p-5 hover:border-indigo-400 hover:shadow-lg hover:shadow-indigo-500/10 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Total Websites</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-500/20 flex items-center justify-center text-indigo-600">
                    <i data-lucide="globe" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalWebsites }}</span>
                <span class="text-xs text-slate-600 ml-2">configured</span>
            </div>
        </div>

        <!-- Card 2: Active Websites -->
        <div class="glass-card rounded-2xl p-5 hover:border-emerald-400 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Active Websites</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-500/20 flex items-center justify-center text-emerald-600">
                    <i data-lucide="radio" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-emerald-600 tracking-tight">{{ $activeWebsites }}</span>
                <span class="text-xs text-slate-600 ml-2">live online</span>
            </div>
        </div>

        <!-- Card 3: Storage Used -->
        <div class="glass-card rounded-2xl p-5 hover:border-blue-400 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Storage Used</span>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                    <i data-lucide="hard-drive" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $storageUsed }}</span>
                <span class="text-xs text-slate-600 ml-2">extracted</span>
            </div>
        </div>

        <!-- Card 4: Recent Deployments -->
        <div class="glass-card rounded-2xl p-5 hover:border-purple-400 hover:shadow-lg hover:shadow-purple-500/10 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Recent Deployments</span>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $recentDeploymentsCount }}</span>
                <span class="text-xs text-slate-600 ml-2">total runs</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity Tables & Quick View -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Websites List (2 Cols) -->
        <div class="lg:col-span-2 bg-hostinger-card border border-hostinger-border rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">Your Hosted Websites</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Static site projects in your account</p>
                </div>
                <a href="{{ Route::has('websites.index') ? route('websites.index') : '#' }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center space-x-1">
                    <span>View All</span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            @if ($recentWebsites->isEmpty())
                <div class="text-center py-12 border-2 border-dashed border-hostinger-border rounded-xl">
                    <div class="w-12 h-12 rounded-xl bg-hostinger-dark flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <i data-lucide="file-archive" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600">No websites uploaded yet</p>
                    <p class="text-xs text-slate-400 mt-1 mb-4">Upload a website.zip file with index.html to deploy</p>
                    <a href="{{ Route::has('websites.create') ? route('websites.create') : '#' }}" class="inline-flex items-center space-x-2 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-500/20">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Upload website.zip</span>
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-hostinger-border text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">Name</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Size</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hostinger-border text-sm">
                            @foreach ($recentWebsites as $site)
                                <tr class="hover:bg-indigo-50 hover:text-indigo-700/50 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-500/20 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                                <i data-lucide="globe" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-900 block">{{ $site->name }}</span>
                                                <span class="text-xs text-slate-600 block font-mono">{{ $site->slug }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($site->status === 'live')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-medium border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                <span>Live</span>
                                            </span>
                                        @elseif($site->status === 'deploying')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium border border-amber-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-spin"></span>
                                                <span>Deploying</span>
                                            </span>
                                        @elseif($site->status === 'failed')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-700 text-xs font-medium border border-rose-200">
                                                <span>Failed</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-400 text-xs font-medium border border-blue-500/20">
                                                <span>Ready</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-600 font-mono">
                                        {{ $site->size_kb }} KB
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        @if ($site->live_url)
                                            <a href="{{ $site->live_url }}" target="_blank" class="inline-flex items-center space-x-1 text-xs font-bold text-emerald-600 hover:text-emerald-800">
                                                <span>Open</span>
                                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">Not deployed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Recent Deployment Activity Feed (1 Col) -->
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-1">Deployment Feed</h3>
                <p class="text-xs text-slate-600 mb-6">Latest deployment jobs</p>

                @if ($recentDeployments->isEmpty())
                    <div class="text-center py-10 text-slate-400 text-xs">
                        <i data-lucide="history" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                        <p>No deployment runs recorded</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($recentDeployments as $dep)
                            <div class="p-3.5 rounded-xl bg-hostinger-dark border border-hostinger-border flex items-start space-x-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5 {{ $dep->status === 'live' ? 'bg-emerald-50 text-emerald-600' : ($dep->status === 'failed' ? 'bg-rose-500/10 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                    <i data-lucide="{{ $dep->status === 'live' ? 'check' : ($dep->status === 'failed' ? 'x-circle' : 'loader') }}" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ $dep->website->name ?? 'Website' }}</p>
                                        <span class="text-[10px] text-slate-600 font-mono">{{ $dep->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-600 capitalize mt-0.5">Status: {{ $dep->status }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-6 pt-4 border-t border-hostinger-border">
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span>Engine: Cloudflare Tunnel</span>
                    <span class="text-emerald-600 font-medium">v1.0 MVP</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
