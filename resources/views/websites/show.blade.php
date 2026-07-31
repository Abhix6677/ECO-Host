@extends('layouts.app')

@section('title', $website->name . ' — Details')

@section('content')
<div class="space-y-8" x-data="{ showConfirm: false }">

    <!-- Header Navigation & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('websites.index') }}" class="p-2 rounded-lg bg-hostinger-card border border-hostinger-border text-gray-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-2xl font-extrabold text-white tracking-tight">{{ $website->name }}</h2>
                    <!-- Status Badge -->
                    @if ($website->status === 'live')
                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Live</span>
                        </span>
                    @elseif ($website->status === 'deploying')
                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs font-semibold border border-amber-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            <span>Deploying</span>
                        </span>
                    @elseif ($website->status === 'failed')
                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 text-xs font-semibold border border-rose-500/20">
                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                            <span>Failed</span>
                        </span>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-slate-500/10 text-slate-400 text-xs font-semibold border border-slate-500/20">
                            <span>Ready</span>
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $website->slug }}</p>
            </div>
        </div>

        <!-- Header Action Buttons -->
        <div class="flex items-center gap-3">
            {{-- Deploy / Redeploy Button --}}
            <form method="POST" action="{{ route('websites.deploy', $website) }}">
                @csrf
                <button type="submit"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center space-x-2">
                    <i data-lucide="{{ $website->status === 'live' ? 'refresh-cw' : 'rocket' }}" class="w-4 h-4"></i>
                    <span>{{ $website->status === 'live' ? 'Redeploy Site' : 'Deploy Site' }}</span>
                </button>
            </form>

            {{-- Open Live URL --}}
            @if ($website->live_url)
                <a href="{{ $website->live_url }}" target="_blank"
                   class="px-4 py-2.5 bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-400 font-bold text-xs rounded-xl border border-emerald-500/20 transition flex items-center space-x-1.5">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>Open Site</span>
                </a>
            @endif

            {{-- Delete Button --}}
            <button @click="showConfirm = true"
                    class="px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 font-bold text-xs rounded-xl border border-rose-500/20 transition flex items-center space-x-1.5">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>

    <!-- Metadata Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-1">
            <span class="text-xs text-gray-400 font-semibold flex items-center space-x-1.5">
                <i data-lucide="user" class="w-3.5 h-3.5 text-indigo-400"></i>
                <span>Owner</span>
            </span>
            <p class="font-bold text-white text-sm truncate">{{ Auth::user()->name }}</p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-1">
            <span class="text-xs text-gray-400 font-semibold flex items-center space-x-1.5">
                <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-indigo-400"></i>
                <span>Storage Size</span>
            </span>
            <p class="font-bold text-white text-sm">
                @if ($website->size_kb >= 1024)
                    {{ number_format($website->size_kb / 1024, 2) }} MB
                @else
                    {{ $website->size_kb }} KB
                @endif
            </p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-1">
            <span class="text-xs text-gray-400 font-semibold flex items-center space-x-1.5">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-400"></i>
                <span>Upload Date</span>
            </span>
            <p class="font-bold text-white text-sm" title="{{ $website->created_at->toDateTimeString() }}">
                {{ $website->created_at->format('M d, Y H:i') }}
            </p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-1">
            <span class="text-xs text-gray-400 font-semibold flex items-center space-x-1.5">
                <i data-lucide="file-archive" class="w-3.5 h-3.5 text-indigo-400"></i>
                <span>Original ZIP</span>
            </span>
            <p class="font-bold text-white text-sm truncate" title="{{ $website->original_filename }}">
                {{ Str::limit($website->original_filename, 22) }}
            </p>
        </div>
    </div>

    <!-- Live Public URL Box -->
    @if ($website->live_url)
        <div class="bg-gradient-to-r from-indigo-900/40 via-hostinger-card to-blue-900/30 border border-indigo-500/30 rounded-2xl p-6 glow-effect"
             x-data="{ copied: false, copyUrl() { navigator.clipboard.writeText('{{ $website->live_url }}'); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider flex items-center space-x-1.5">
                        <i data-lucide="globe" class="w-4 h-4"></i>
                        <span>Live Website Public Address</span>
                    </span>
                    <p class="text-lg font-mono font-bold text-white break-all">
                        {{ $website->live_url }}
                    </p>
                </div>
                <div class="flex items-center space-x-3 shrink-0">
                    <button @click="copyUrl()"
                            class="px-4 py-2.5 bg-hostinger-dark border border-hostinger-border hover:border-indigo-500 text-gray-200 hover:text-white font-bold text-xs rounded-xl transition flex items-center space-x-1.5">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                        <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                    </button>
                    <a href="{{ $website->live_url }}" target="_blank"
                       class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center space-x-1.5">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Visit Site</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Deployment History & Terminal Logs Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-white tracking-tight flex items-center space-x-2">
                <i data-lucide="history" class="w-5 h-5 text-indigo-400"></i>
                <span>Deployment History &amp; Logs</span>
            </h3>
            <span class="text-xs font-semibold text-gray-400 bg-hostinger-card px-3 py-1 rounded-full border border-hostinger-border">
                {{ $deployments->count() }} Total Deployment Runs
            </span>
        </div>

        @if ($deployments->isEmpty())
            <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-10 text-center">
                <i data-lucide="rocket" class="w-8 h-8 text-gray-500 mx-auto mb-3"></i>
                <p class="text-sm font-bold text-white">No Deployments Yet</p>
                <p class="text-xs text-gray-400 mt-1">Click the Deploy button above to run your first deployment for this website.</p>
            </div>
        @else
            <div class="bg-hostinger-card border border-hostinger-border rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-hostinger-border text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-hostinger-dark/60">
                                <th class="py-4 px-6">Deployment ID</th>
                                <th class="py-4 px-4">Status</th>
                                <th class="py-4 px-4">Target URL</th>
                                <th class="py-4 px-4">Executed</th>
                                <th class="py-4 px-6 text-right">Terminal Logs</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hostinger-border text-sm">
                            @foreach ($deployments as $dep)
                                <tr class="hover:bg-hostinger-cardHover/40 transition" x-data="{ showLog: false }">
                                    <td class="py-4 px-6 font-mono text-xs text-gray-300">
                                        {{ $dep->uuid }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if ($dep->status === 'live')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                <span>Live</span>
                                            </span>
                                        @elseif ($dep->status === 'deploying')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs font-semibold border border-amber-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                                <span>Deploying</span>
                                            </span>
                                        @elseif ($dep->status === 'failed')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 text-xs font-semibold border border-rose-500/20">
                                                <i data-lucide="x-circle" class="w-3 h-3"></i>
                                                <span>Failed</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-500/10 text-slate-400 text-xs font-semibold border border-slate-500/20">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @if ($dep->live_url)
                                            <a href="{{ $dep->live_url }}" target="_blank" class="text-xs text-indigo-400 hover:text-indigo-300 font-mono transition truncate max-w-[180px] inline-block">
                                                {{ Str::limit($dep->live_url, 30) }}
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-xs text-gray-400">
                                        {{ $dep->created_at->diffForHumans() }}
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        @if ($dep->log_output)
                                            <button @click="showLog = !showLog"
                                                    class="text-xs font-bold text-gray-300 hover:text-white bg-hostinger-dark border border-hostinger-border hover:border-gray-500 px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5 ml-auto">
                                                <i data-lucide="terminal" class="w-3.5 h-3.5 text-indigo-400"></i>
                                                <span x-text="showLog ? 'Hide Logs' : 'View Logs'"></span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Terminal Log Expansion --}}
                                @if ($dep->log_output)
                                    <tr x-show="showLog" x-collapse>
                                        <td colspan="5" class="px-6 pb-4">
                                            <div class="bg-black/80 border border-hostinger-border rounded-xl p-4 font-mono text-xs text-emerald-300 whitespace-pre-wrap leading-relaxed max-h-64 overflow-y-auto shadow-inner">{{ $dep->log_output }}</div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showConfirm" x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
         x-cloak>
        <div class="bg-hostinger-card border border-rose-500/30 rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4">
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 flex items-center justify-center mx-auto">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-rose-400"></i>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-bold text-white">Delete Website?</h3>
                <p class="text-xs text-gray-400 mt-1">
                    <strong class="text-white">{{ $website->name }}</strong> and all extracted files will be permanently deleted from local and CoCalc storage. This action cannot be undone.
                </p>
            </div>
            <div class="flex gap-3 pt-2">
                <button @click="showConfirm = false"
                        class="flex-1 py-2.5 px-4 text-xs font-bold text-gray-300 bg-hostinger-dark border border-hostinger-border hover:border-gray-500 rounded-xl transition">
                    Cancel
                </button>
                <form method="POST" action="{{ route('websites.destroy', $website) }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 px-4 text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white rounded-xl transition">
                        Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
