@extends('layouts.app')

@section('title', 'My Websites')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">My Websites</h2>
            <p class="text-sm text-slate-500 mt-0.5">Manage, deploy, and monitor terminal logs for your static web projects</p>
        </div>
        <a href="{{ route('websites.create') }}"
           class="inline-flex items-center space-x-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-md shadow-indigo-600/20 transition transform active:scale-95">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>Upload New Website</span>
        </a>
    </div>

    @if ($websites->isEmpty())
        <!-- Empty State -->
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-16 text-center">
            <div class="w-20 h-20 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center mx-auto mb-5">
                <i data-lucide="file-archive" class="w-10 h-10 text-indigo-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No Websites Yet</h3>
            <p class="text-slate-500 text-sm max-w-sm mx-auto mb-6">
                Upload your first static website ZIP to get started. EcoHost will validate, extract, and host it instantly on EcoHost Cloud.
            </p>
            <a href="{{ route('websites.create') }}"
               class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-md shadow-indigo-600/20 transition transform active:scale-95">
                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                <span>Upload First Website</span>
            </a>
        </div>
    @else
        <!-- Website Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach ($websites as $site)
                <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-6 flex flex-col justify-between hover:border-indigo-400 hover:shadow-xl hover:shadow-indigo-500/10 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-200 group cursor-pointer relative"
                     x-data="{ showConfirm: false }"
                     @click="window.location='{{ route('websites.show', $site) }}'">

                    <div>
                        <!-- Site Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-600 shrink-0 group-hover:bg-indigo-600 group-hover:text-white shadow-md shadow-indigo-600/30 transition duration-200">
                                    <i data-lucide="globe" class="w-6 h-6"></i>
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('websites.show', $site) }}" 
                                       @click.stop
                                       class="font-bold text-slate-900 text-base truncate hover:text-indigo-600 transition block">
                                        {{ $site->name }}
                                    </a>
                                    <p class="text-xs text-slate-500 font-mono truncate">{{ $site->slug }}</p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            @if ($site->status === 'live')
                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-500/20 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Live</span>
                                </span>
                            @elseif ($site->status === 'deploying')
                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold border border-amber-500/20 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    <span>Deploying</span>
                                </span>
                            @elseif ($site->status === 'failed')
                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-700 text-xs font-semibold border border-rose-200 shrink-0">
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
                        <div class="mb-5 space-y-2 text-xs text-slate-500">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center space-x-1.5">
                                    <i data-lucide="hard-drive" class="w-3.5 h-3.5"></i>
                                    <span>Size</span>
                                </span>
                                <span class="font-mono font-semibold text-slate-700">
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
                                <span class="font-semibold text-slate-700">{{ $site->created_at->diffForHumans() }}</span>
                            </div>
                            @if ($site->live_url)
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center space-x-1.5">
                                        <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                        <span>URL</span>
                                    </span>
                                    <a href="{{ $site->live_url }}" target="_blank" @click.stop
                                       class="font-semibold text-indigo-600 hover:text-indigo-800 truncate max-w-[140px] transition">
                                        {{ Str::limit($site->live_url, 30) }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Footer & Actions -->
                    <div class="space-y-3 pt-4 border-t border-hostinger-border">
                        <div class="flex items-center justify-between text-xs text-indigo-600 group-hover:text-indigo-800 font-bold transition">
                            <span class="flex items-center space-x-1">
                                <i data-lucide="terminal" class="w-3.5 h-3.5"></i>
                                <span>Click card to view details &amp; live logs</span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 transform group-hover:translate-x-1 transition"></i>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2" x-data="{ isDeploying: false }">
                            {{-- Deploy Button (shown when not live) --}}
                            @if ($site->status !== 'live' && $site->status !== 'deploying')
                                <form method="POST" action="{{ Route::has('websites.deploy') ? route('websites.deploy', $site) : '#' }}" class="flex-1" @click.stop @submit="isDeploying = true">
                                    @csrf
                                    <button type="submit"
                                            :disabled="isDeploying"
                                            :class="isDeploying ? 'opacity-85 btn-pulse-glow cursor-not-allowed' : 'hover:scale-[1.02] active:scale-95'"
                                            class="w-full py-2.5 px-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition-all duration-200 flex items-center justify-center space-x-1.5">
                                        <template x-if="!isDeploying">
                                            <span class="flex items-center space-x-1.5">
                                                <i data-lucide="rocket" class="w-4 h-4"></i>
                                                <span>Deploy</span>
                                            </span>
                                        </template>
                                        <template x-if="isDeploying">
                                            <span class="flex items-center space-x-1.5">
                                                <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span>Deploying...</span>
                                            </span>
                                        </template>
                                    </button>
                                </form>
                            @else
                                {{-- Redeploy Button (shown when live) --}}
                                <form method="POST" action="{{ Route::has('websites.deploy') ? route('websites.deploy', $site) : '#' }}" class="flex-1" @click.stop @submit="isDeploying = true">
                                    @csrf
                                    <button type="submit"
                                            :disabled="isDeploying"
                                            :class="isDeploying ? 'opacity-85 btn-pulse-glow cursor-not-allowed bg-indigo-600 text-white' : 'hover:scale-[1.02] active:scale-95 bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                            class="w-full py-2.5 px-3 border border-slate-200 shadow-sm font-bold text-xs rounded-xl transition-all duration-200 flex items-center justify-center space-x-1.5">
                                        <template x-if="!isDeploying">
                                            <span class="flex items-center space-x-1.5">
                                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                                <span>Redeploy</span>
                                            </span>
                                        </template>
                                        <template x-if="isDeploying">
                                            <span class="flex items-center space-x-1.5">
                                                <svg class="animate-spin w-4 h-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span class="text-indigo-600">Redeploying...</span>
                                            </span>
                                        </template>
                                    </button>
                                </form>
                            @endif

                            {{-- Open Live URL --}}
                            @if ($site->live_url)
                                <a href="{{ $site->live_url }}" target="_blank" @click.stop
                                   class="py-2.5 px-3 bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-500/20 transition-all duration-200 hover:scale-[1.02] active:scale-95 flex items-center space-x-1.5">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                    <span>Open</span>
                                </a>
                            @endif

                            {{-- Delete Button with Confirmation Modal --}}
                            <button @click.stop="showConfirm = true"
                                    class="py-2.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition-all duration-200 hover:scale-[1.02] active:scale-95 flex items-center space-x-1.5">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div x-show="showConfirm" x-transition
                         @click.stop
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                         x-cloak>
                        <div class="bg-hostinger-card border border-rose-500/30 rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                            <div class="w-12 h-12 rounded-xl bg-rose-500/10 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-rose-700"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 text-center mb-2">Delete Website?</h3>
                            <p class="text-sm text-slate-500 text-center mb-6">
                                <strong class="text-slate-900">{{ $site->name }}</strong> and all associated files will be permanently deleted. This action cannot be undone.
                            </p>
                            <div class="flex gap-3">
                                <button @click="showConfirm = false"
                                        class="flex-1 py-2.5 px-4 text-sm font-bold text-slate-600 bg-hostinger-dark border border-hostinger-border hover:border-gray-500 rounded-xl transition">
                                    Cancel
                                </button>
                                <form method="POST" action="{{ route('websites.destroy', $site) }}" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full py-2.5 px-4 text-sm font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-sm rounded-xl transition">
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
