@extends('layouts.app')

@section('title', 'My Websites')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">My Websites</h2>
            <p class="text-sm text-gray-400 mt-0.5">Manage and deploy your static web projects</p>
        </div>
        <a href="{{ route('websites.create') }}"
           class="inline-flex items-center space-x-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition transform active:scale-95">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>Upload New Website</span>
        </a>
    </div>

    @if ($websites->isEmpty())
        <!-- Empty State -->
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-16 text-center">
            <div class="w-20 h-20 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center mx-auto mb-5">
                <i data-lucide="file-archive" class="w-10 h-10 text-indigo-400"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">No Websites Yet</h3>
            <p class="text-gray-400 text-sm max-w-sm mx-auto mb-6">
                Upload your first static website ZIP to get started. EcoHost will validate, extract, and host it instantly.
            </p>
            <a href="{{ route('websites.create') }}"
               class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition transform active:scale-95">
                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                <span>Upload First Website</span>
            </a>
        </div>
    @else
        <!-- Website Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach ($websites as $site)
                <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-6 flex flex-col justify-between hover:border-indigo-500/30 transition-all duration-200 group"
                     x-data="{ showConfirm: false }">

                    <!-- Site Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                                <i data-lucide="globe" class="w-6 h-6"></i>
                            </div>
                                <a href="{{ route('websites.show', $site) }}" class="font-bold text-white text-sm truncate hover:text-indigo-400 transition block">
                                    {{ $site->name }}
                                </a>
                                <p class="text-xs text-gray-400 font-mono truncate">{{ $site->slug }}</p>
                        </div>

                        <!-- Status Badge -->
                        @if ($site->status === 'live')
                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Live</span>
                            </span>
                        @elseif ($site->status === 'deploying')
                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs font-semibold border border-amber-500/20 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                <span>Deploying</span>
                            </span>
                        @elseif ($site->status === 'failed')
                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 text-xs font-semibold border border-rose-500/20 shrink-0">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                <span>Failed</span>
                            </span>
                        @elseif ($site->status === 'uploading')
                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-400 text-xs font-semibold border border-blue-500/20 shrink-0">
                                <span>Uploading</span>
                            </span>
                        @else
                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-slate-500/10 text-slate-400 text-xs font-semibold border border-slate-500/20 shrink-0">
                                <span>Ready</span>
                            </span>
                        @endif
                    </div>

                    <!-- Metadata Row -->
                    <div class="mb-5 space-y-2 text-xs text-gray-400">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center space-x-1.5">
                                <i data-lucide="hard-drive" class="w-3.5 h-3.5"></i>
                                <span>Size</span>
                            </span>
                            <span class="font-mono font-semibold text-gray-200">
                                @if ($site->size_kb >= 1024)
                                    {{ number_format($site->size_kb / 1024, 2) }} MB
                                @else
                                    {{ $site->size_kb }} KB
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center space-x-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <span>Uploaded</span>
                            </span>
                            <span class="font-semibold text-gray-200">{{ $site->created_at->diffForHumans() }}</span>
                        </div>
                        @if ($site->live_url)
                            <div class="flex items-center justify-between">
                                <span class="flex items-center space-x-1.5">
                                    <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                    <span>URL</span>
                                </span>
                                <a href="{{ $site->live_url }}" target="_blank"
                                   class="font-semibold text-indigo-400 hover:text-indigo-300 truncate max-w-[140px] transition">
                                    {{ Str::limit($site->live_url, 30) }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 pt-4 border-t border-hostinger-border">
                        {{-- Deploy Button (shown when not live) --}}
                        @if ($site->status !== 'live' && $site->status !== 'deploying')
                            <form method="POST" action="{{ Route::has('websites.deploy') ? route('websites.deploy', $site) : '#' }}" class="flex-1">
                                @csrf
                                <button type="submit"
                                        class="w-full py-2.5 px-3 bg-indigo-600/80 hover:bg-indigo-600 text-white font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                                    <i data-lucide="rocket" class="w-4 h-4"></i>
                                    <span>Deploy</span>
                                </button>
                            </form>
                        @else
                            {{-- Redeploy Button (shown when live) --}}
                            <form method="POST" action="{{ Route::has('websites.deploy') ? route('websites.deploy', $site) : '#' }}" class="flex-1">
                                @csrf
                                <button type="submit"
                                        class="w-full py-2.5 px-3 bg-slate-600/50 hover:bg-slate-600 text-white font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    <span>Redeploy</span>
                                </button>
                            </form>
                        @endif

                        {{-- Open Live URL --}}
                        @if ($site->live_url)
                            <a href="{{ $site->live_url }}" target="_blank"
                               class="py-2.5 px-3 bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-400 font-bold text-xs rounded-xl border border-emerald-500/20 transition flex items-center space-x-1.5">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                <span>Open</span>
                            </a>
                        @endif

                        {{-- Delete Button with Confirmation Modal --}}
                        <button @click="showConfirm = true"
                                class="py-2.5 px-3 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 font-bold text-xs rounded-xl border border-rose-500/20 transition flex items-center space-x-1.5">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div x-show="showConfirm" x-transition
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                         x-cloak>
                        <div class="bg-hostinger-card border border-rose-500/30 rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                            <div class="w-12 h-12 rounded-xl bg-rose-500/10 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-rose-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-white text-center mb-2">Delete Website?</h3>
                            <p class="text-sm text-gray-400 text-center mb-6">
                                <strong class="text-white">{{ $site->name }}</strong> and all associated files will be permanently deleted. This action cannot be undone.
                            </p>
                            <div class="flex gap-3">
                                <button @click="showConfirm = false"
                                        class="flex-1 py-2.5 px-4 text-sm font-bold text-gray-300 bg-hostinger-dark border border-hostinger-border hover:border-gray-500 rounded-xl transition">
                                    Cancel
                                </button>
                                <form method="POST" action="{{ route('websites.destroy', $site) }}" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full py-2.5 px-4 text-sm font-bold bg-rose-600 hover:bg-rose-500 text-white rounded-xl transition">
                                        Yes, Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if ($websites->hasPages())
            <div class="flex justify-center">
                {{ $websites->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
