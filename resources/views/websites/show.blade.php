@extends('layouts.app')

@section('title', $website->name . ' — Details')

@section('content')
@php
    $latestLog = $deployments->isNotEmpty() ? $deployments->first()->log_output : 'No deployment logs recorded yet.';
    if (!empty($visitorLogs)) {
        $latestLog .= "\n\n--- 🌐 LIVE VISITOR TRAFFIC LOGS (ECOHOST CLOUD) ---\n" . implode("\n", $visitorLogs);
    }
@endphp

<div x-data="terminalApp()" x-init="startPolling()" class="space-y-8">

    <!-- Header Navigation & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('websites.index') }}" 
               class="p-2.5 rounded-xl bg-hostinger-card border border-hostinger-border text-slate-500 hover:text-slate-900 hover:border-indigo-400 hover:shadow-xl hover:shadow-indigo-500/10 transition shadow-md">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $website->name }}</h2>
                    <!-- Status Badge -->
                    @if ($website->status === 'live')
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-500/20 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Live</span>
                        </span>
                    @elseif ($website->status === 'deploying')
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold border border-amber-500/20 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            <span>Deploying</span>
                        </span>
                    @elseif ($website->status === 'failed')
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-rose-500/10 text-rose-700 text-xs font-semibold border border-rose-200 shadow-sm">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>Failed</span>
                        </span>
                    @else
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-slate-500/10 text-slate-400 text-xs font-semibold border border-slate-500/20 shadow-sm">
                            <span>Ready</span>
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $website->slug }}</p>
            </div>
        </div>

        <!-- Header Action Buttons -->
        <div class="flex flex-wrap items-center gap-3" x-data="{ isDeploying: false, isDeleting: false }">
            {{-- Toggle Logs Button --}}
            <button @click="showLiveConsole = !showLiveConsole"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-indigo-700 border border-indigo-200 font-bold text-xs rounded-xl shadow-sm transition-all duration-200 active:scale-95 flex items-center space-x-2">
                <i data-lucide="terminal" class="w-4 h-4 text-indigo-600"></i>
                <span x-text="showLiveConsole ? 'Hide Terminal Logs' : 'View Terminal Logs'"></span>
            </button>

            {{-- Deploy / Redeploy Button with Spinning Animation --}}
            <form method="POST" action="{{ route('websites.deploy', $website) }}" @submit="isDeploying = true">
                @csrf
                <button type="submit"
                        :disabled="isDeploying"
                        :class="isDeploying ? 'opacity-85 btn-pulse-glow cursor-not-allowed' : 'hover:scale-[1.02] active:scale-95'"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition-all duration-200 flex items-center space-x-2">
                    <template x-if="!isDeploying">
                        <i data-lucide="{{ $website->status === 'live' ? 'refresh-cw' : 'rocket' }}" class="w-4 h-4"></i>
                    </template>
                    <template x-if="isDeploying">
                        <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isDeploying ? 'Deploying to Cloud...' : '{{ $website->status === 'live' ? 'Redeploy Site' : 'Deploy Site' }}'"></span>
                </button>
            </form>

            {{-- Open Live URL --}}
            @if ($website->live_url)
                <a href="{{ $website->live_url }}" target="_blank"
                   class="px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200 shadow-sm transition-all duration-200 hover:scale-[1.02] active:scale-95 flex items-center space-x-1.5">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>Open Site</span>
                </a>
            @endif

            {{-- Delete Button --}}
            <button @click="showConfirm = true"
                    class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition-all duration-200 hover:scale-[1.02] active:scale-95 flex items-center space-x-1.5">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>

    <!-- Metadata Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-1">
            <span class="text-xs text-slate-500 font-semibold flex items-center space-x-1.5">
                <i data-lucide="user" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>Owner</span>
            </span>
            <p class="font-bold text-slate-900 text-sm truncate">{{ Auth::user()->name }}</p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-1">
            <span class="text-xs text-slate-500 font-semibold flex items-center space-x-1.5">
                <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>Storage Size</span>
            </span>
            <p class="font-bold text-slate-900 text-sm">
                @if ($website->size_kb >= 1024)
                    {{ number_format($website->size_kb / 1024, 2) }} MB
                @else
                    {{ $website->size_kb }} KB
                @endif
            </p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-1">
            <span class="text-xs text-slate-500 font-semibold flex items-center space-x-1.5">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>Upload Date</span>
            </span>
            <p class="font-bold text-slate-900 text-sm" title="{{ $website->created_at->toDateTimeString() }}">
                {{ $website->created_at->format('M d, Y H:i') }}
            </p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-1">
            <span class="text-xs text-slate-500 font-semibold flex items-center space-x-1.5">
                <i data-lucide="file-archive" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>Original ZIP</span>
            </span>
            <p class="font-bold text-slate-900 text-sm truncate" title="{{ $website->original_filename }}">
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
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider flex items-center space-x-1.5">
                        <i data-lucide="globe" class="w-4 h-4"></i>
                        <span>Live Website Public Address</span>
                    </span>
                    <p class="text-lg font-mono font-bold text-slate-900 break-all">
                        {{ $website->live_url }}
                    </p>
                </div>
                <div class="flex items-center space-x-3 shrink-0">
                    <button @click="copyUrl()"
                            class="px-4 py-2.5 bg-hostinger-dark border border-hostinger-border hover:border-indigo-500 text-slate-700 hover:text-slate-900 font-bold text-xs rounded-xl transition flex items-center space-x-1.5">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                        <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                    </button>
                    <a href="{{ $website->live_url }}" target="_blank"
                       class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center space-x-1.5">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Visit Site</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Big Screen Live Terminal Console (Auto-Syncing Deployment & Visitor HTTP Logs) -->
    <div x-show="showLiveConsole" x-collapse x-ref="terminalBox" class="space-y-3">
        <div class="bg-[#0f172a] border border-slate-700/80 rounded-2xl overflow-hidden shadow-2xl">
            
            <!-- Terminal Window Titlebar -->
            <div class="bg-[#1e293b] px-4 py-3 border-b border-slate-700/80 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <div class="flex space-x-1.5">
                        <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    </div>
                    <div class="flex items-center space-x-2 text-xs font-mono text-slate-200">
                        <i data-lucide="terminal" class="w-4 h-4 text-emerald-400"></i>
                        <span class="font-bold">ecohost-cloud-engine ~ deployment_&amp;_traffic_logs.log</span>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Live Sync Indicator -->
                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-[11px] font-mono border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" :class="isPolling ? 'animate-pulse' : 'bg-amber-400'"></span>
                        <span x-text="isPolling ? 'Live Auto-Sync (3s)' : 'Sync Paused'"></span>
                    </span>

                    <!-- Sync Toggle Button -->
                    <button @click="togglePolling()"
                            class="px-2.5 py-1 text-xs font-mono text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-lg transition flex items-center space-x-1 shadow-sm">
                        <i data-lucide="refresh-cw" class="w-3 h-3" :class="isFetching ? 'animate-spin text-indigo-400' : ''"></i>
                        <span x-text="isPolling ? 'Pause Sync' : 'Resume Live Sync'"></span>
                    </button>

                    <!-- Refresh Now Button -->
                    <button @click="fetchLogs()"
                            class="px-2.5 py-1 text-xs font-mono text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-lg transition flex items-center space-x-1 shadow-sm">
                        <i data-lucide="rotate-cw" class="w-3 h-3 text-indigo-400"></i>
                        <span>Refresh</span>
                    </button>

                    <!-- Copy Logs -->
                    <button @click="copyLogs()"
                            class="px-2.5 py-1 text-xs font-mono text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-600 rounded-lg transition flex items-center space-x-1 shadow-sm">
                        <i data-lucide="copy" class="w-3 h-3 text-slate-400"></i>
                        <span x-text="copiedLog ? 'Copied' : 'Copy'"></span>
                    </button>

                    <!-- Close Panel -->
                    <button @click="showLiveConsole = false" class="text-slate-400 hover:text-white p-1 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Terminal Output Container -->
            <div class="p-6 font-mono text-xs sm:text-sm text-emerald-400 leading-relaxed whitespace-pre-wrap max-h-96 overflow-y-auto bg-[#090d16] scrollbar-thin scrollbar-thumb-indigo-600 shadow-inner"
                 x-ref="logConsole">
                <template x-if="logOutput">
                    <span x-text="logOutput"></span>
                </template>
            </div>
            
            <div class="bg-[#1e293b]/90 px-4 py-2 border-t border-slate-700/80 flex items-center justify-between text-[11px] font-mono text-slate-400">
                <span>EcoHost Realtime Stream</span>
                <span>Log Mode: <span class="text-indigo-400 font-bold" x-text="lastUpdated"></span></span>
            </div>
        </div>
    </div>

    <!-- Deployment History & Logs Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 tracking-tight flex items-center space-x-2">
                <i data-lucide="history" class="w-5 h-5 text-indigo-600"></i>
                <span>Deployment History &amp; Log Runs</span>
            </h3>
            <span class="text-xs font-semibold text-slate-500 bg-hostinger-card px-3 py-1 rounded-full border border-hostinger-border">
                {{ $deployments->count() }} Total Deployment Runs
            </span>
        </div>

        @if ($deployments->isEmpty())
            <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-10 text-center">
                <i data-lucide="rocket" class="w-8 h-8 text-slate-400 mx-auto mb-3"></i>
                <p class="text-sm font-bold text-slate-900">No Deployments Yet</p>
                <p class="text-xs text-slate-500 mt-1">Click the Deploy button above to run your first deployment for this website.</p>
            </div>
        @else
            <div class="bg-hostinger-card border border-hostinger-border rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-hostinger-border text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-hostinger-dark/60">
                                <th class="py-4 px-6">Deployment ID</th>
                                <th class="py-4 px-4">Status</th>
                                <th class="py-4 px-4">Target URL</th>
                                <th class="py-4 px-4">Executed</th>
                                <th class="py-4 px-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hostinger-border text-sm">
                            @foreach ($deployments as $dep)
                                <tr class="hover:bg-indigo-50 hover:text-indigo-700/40 transition">
                                    <td class="py-4 px-6 font-mono text-xs text-slate-600">
                                        {{ $dep->uuid }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if ($dep->status === 'live')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                <span>Live</span>
                                            </span>
                                        @elseif ($dep->status === 'deploying')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold border border-amber-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                                <span>Deploying</span>
                                            </span>
                                        @elseif ($dep->status === 'failed')
                                            <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-700 text-xs font-semibold border border-rose-200">
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
                                            <a href="{{ $dep->live_url }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 font-mono transition truncate max-w-[180px] inline-block">
                                                {{ Str::limit($dep->live_url, 30) }}
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-500">
                                        {{ $dep->created_at->diffForHumans() }}
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        @if ($dep->log_output)
                                            <button @click="viewRunLog(@js($dep->log_output))"
                                                    class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-hostinger-dark border border-hostinger-border hover:border-indigo-500 px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5 ml-auto">
                                                <i data-lucide="terminal" class="w-3.5 h-3.5 text-indigo-600"></i>
                                                <span>Inspect Run Logs</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
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
                <i data-lucide="alert-triangle" class="w-6 h-6 text-rose-700"></i>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-bold text-slate-900">Delete Website?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    <strong class="text-slate-900">{{ $website->name }}</strong> and all extracted files will be permanently deleted from EcoHost storage. This action cannot be undone.
                </p>
            </div>
            <div class="flex gap-3 pt-2">
                <button @click="showConfirm = false"
                        class="flex-1 py-2.5 px-4 text-xs font-bold text-slate-600 bg-hostinger-dark border border-hostinger-border hover:border-gray-500 rounded-xl transition">
                    Cancel
                </button>
                <form method="POST" action="{{ route('websites.destroy', $website) }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 px-4 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-sm rounded-xl transition">
                        Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function terminalApp() {
    return {
        showConfirm: false,
        showLiveConsole: true,
        isPolling: true,
        isFetching: false,
        lastUpdated: 'Live Stream',
        copiedLog: false,
        logOutput: @json($latestLog),
        pollTimer: null,

        startPolling() {
            this.pollTimer = setInterval(() => {
                if (this.isPolling && this.showLiveConsole) {
                    this.fetchLogs();
                }
            }, 3000);
        },

        async fetchLogs() {
            this.isFetching = true;
            try {
                const res = await fetch('{{ route('websites.logs', $website) }}');
                const data = await res.json();
                if (data.status === 'success' && data.log_output) {
                    this.logOutput = data.log_output;
                    this.lastUpdated = 'Live Stream (' + data.updated_at + ')';
                }
            } catch (e) {
                console.error('Failed to poll logs:', e);
            } finally {
                this.isFetching = false;
            }
        },

        togglePolling() {
            this.isPolling = !this.isPolling;
        },

        viewRunLog(runLogText) {
            this.logOutput = runLogText;
            this.showLiveConsole = true;
            this.isPolling = false; // Pause auto-sync so user can inspect specific past run log
            this.lastUpdated = 'Inspecting Past Run';
            this.$nextTick(() => {
                this.$refs.terminalBox?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        },

        copyLogs() {
            navigator.clipboard.writeText(this.logOutput);
            this.copiedLog = true;
            setTimeout(() => this.copiedLog = false, 2000);
        }
    }
}
</script>
@endsection
