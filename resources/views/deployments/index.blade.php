@extends('layouts.app')

@section('title', 'Deployments')

@section('content')
<div class="space-y-8">

    <!-- Page Header & Target Info -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center space-x-3">
                <i data-lucide="rocket" class="w-7 h-7 text-indigo-600"></i>
                <span>Deployment History</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Real-time activity logs and status of all website deployments on EcoHost Cloud Engine
            </p>
        </div>

        <!-- EcoHost Target Badge -->
        <div class="shrink-0 flex items-center space-x-3">
            @if ($isTunnelConfigured)
                <div class="flex items-center space-x-2.5 px-4 py-2.5 rounded-xl bg-emerald-100 border border-emerald-500/30 text-emerald-700 text-xs font-bold shadow-md">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <i data-lucide="server" class="w-4 h-4 text-emerald-700"></i>
                    <span>EcoHost 24/7 Engine Active</span>
                </div>
            @else
                <div class="flex items-center space-x-2.5 px-4 py-2.5 rounded-xl bg-amber-100 border border-amber-500/30 text-amber-700 text-xs font-bold shadow-md">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-700"></i>
                    <span>EcoHost Engine Offline</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-2 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Total Deployment Runs</span>
                <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-600">
                    <i data-lucide="history" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ $deployments->total() }}</p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-2 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Live Sites Deployed</span>
                <div class="p-2 rounded-xl bg-emerald-100 text-emerald-700">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">
                {{ $deployments->where('status', 'live')->count() }}
            </p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-2 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Failed Deployments</span>
                <div class="p-2 rounded-xl bg-rose-500/10 text-rose-700">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">
                {{ $deployments->where('status', 'failed')->count() }}
            </p>
        </div>

        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-5 space-y-2 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Hosting Environment</span>
                <div class="p-2 rounded-xl bg-blue-500/10 text-blue-400">
                    <i data-lucide="cpu" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-sm font-bold text-indigo-700 truncate" title="EcoHost Cloud Engine">
                EcoHost 24/7
            </p>
        </div>
    </div>

    <!-- Active Receiver Info Banner -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-50 via-white to-blue-50 border border-indigo-200 shadow-sm text-xs sm:text-sm">
        <div class="flex items-start space-x-3.5">
            <div class="p-2 rounded-xl bg-indigo-500/20 text-indigo-600 shrink-0 mt-0.5">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div class="space-y-2 text-slate-600">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="font-bold text-slate-900 text-sm">Target Hosting Node: EcoHost Cloud Engine</p>
                    <span class="text-xs font-mono text-indigo-700 font-bold bg-indigo-50/80 px-3 py-1 rounded-lg border border-indigo-200 shadow-sm">
                        {{ config('services.cocalc.receiver_url') }}
                    </span>
                </div>
                <p class="text-xs text-slate-600 font-medium leading-relaxed">
                    All static websites are extracted and served remotely from EcoHost Cloud storage. Deployments run in 24/7 background mode.
                </p>
            </div>
        </div>
    </div>

    @if ($deployments->isEmpty())
        <!-- Empty State -->
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-16 text-center shadow-xl">
            <div class="w-20 h-20 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center mx-auto mb-5">
                <i data-lucide="rocket" class="w-10 h-10 text-indigo-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No Deployments Recorded</h3>
            <p class="text-slate-500 text-xs sm:text-sm max-w-sm mx-auto mb-6">
                Upload a website project and trigger your first deployment to view logs and history here.
            </p>
            <a href="{{ route('websites.create') }}"
               class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                <span>Upload &amp; Deploy Website</span>
            </a>
        </div>
    @else
        <!-- Deployment Log Table -->
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-hostinger-border text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-hostinger-dark/70">
                            <th class="py-4 px-6">Website Project</th>
                            <th class="py-4 px-4">Status</th>
                            <th class="py-4 px-4">Live URL</th>
                            <th class="py-4 px-4">Deployment Time</th>
                            <th class="py-4 px-6 text-right">Terminal Log</th>
                        </tr>
                    </thead>

                    @foreach ($deployments as $dep)
                        <tbody x-data="{ showLog: false, copied: false, copyLog(text) { navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => this.copied = false, 2000); } }" 
                               class="divide-y divide-hostinger-border text-sm">
                            <tr class="hover:bg-indigo-50 hover:text-indigo-700/50 transition">
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-600 shrink-0">
                                            <i data-lucide="globe" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            @if ($dep->website)
                                                <a href="{{ route('websites.show', $dep->website) }}" 
                                                   class="font-bold text-slate-900 hover:text-indigo-800 transition flex items-center space-x-1.5">
                                                    <span>{{ $dep->website->name }}</span>
                                                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-400"></i>
                                                </a>
                                            @else
                                                <span class="font-bold text-slate-500 italic">(Deleted Website)</span>
                                            @endif
                                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $dep->uuid }}</p>
                                        </div>
                                    </div>
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
                                            Deployed
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    @if ($dep->live_url)
                                        <a href="{{ $dep->live_url }}" target="_blank"
                                           class="text-xs text-indigo-600 hover:text-indigo-800 font-mono transition flex items-center space-x-1 max-w-[200px]">
                                            <span class="truncate">{{ Str::limit($dep->live_url, 32) }}</span>
                                            <i data-lucide="external-link" class="w-3.5 h-3.5 shrink-0"></i>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-xs text-slate-500">
                                    @if ($dep->deployed_at)
                                        <span title="{{ $dep->deployed_at->toDateTimeString() }}">
                                            {{ $dep->deployed_at->diffForHumans() }}
                                        </span>
                                    @else
                                        {{ $dep->created_at->diffForHumans() }}
                                    @endif
                                </td>

                                <td class="py-4 px-6 text-right">
                                    @if ($dep->log_output)
                                        <button @click="showLog = !showLog"
                                                class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100/90 border border-slate-300 hover:border-indigo-500 px-3.5 py-1.5 rounded-xl transition flex items-center space-x-1.5 ml-auto shadow-sm">
                                            <i data-lucide="terminal" class="w-3.5 h-3.5 text-indigo-600"></i>
                                            <span x-text="showLog ? 'Hide Logs' : 'View Logs'"></span>
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            {{-- Inline Log Expansion Drawer --}}
                            @if ($dep->log_output)
                                <tr x-show="showLog" x-collapse>
                                    <td colspan="5" class="px-6 py-4 bg-slate-50">
                                        <div class="bg-[#0f172a] border border-slate-700/80 rounded-xl overflow-hidden shadow-xl">
                                            <div class="bg-[#1e293b] px-4 py-2.5 border-b border-slate-700/80 flex items-center justify-between">
                                                <div class="flex items-center space-x-2 text-xs font-mono text-slate-200">
                                                    <i data-lucide="terminal" class="w-3.5 h-3.5 text-emerald-400"></i>
                                                    <span class="font-bold">ecohost_cloud_deployment_run_{{ substr($dep->uuid, 0, 8) }}.log</span>
                                                </div>
                                                <button @click="copyLog(@js($dep->log_output))"
                                                        class="text-[11px] font-mono text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 px-2.5 py-1 rounded-lg border border-slate-600 transition flex items-center space-x-1 shadow-sm">
                                                    <i data-lucide="copy" class="w-3 h-3 text-indigo-400"></i>
                                                    <span x-text="copied ? 'Copied' : 'Copy Log'"></span>
                                                </button>
                                            </div>
                                            <div class="p-4 font-mono text-xs text-emerald-400 whitespace-pre-wrap leading-relaxed max-h-64 overflow-y-auto bg-[#090d16]">
                                                {{ $dep->log_output }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if ($deployments->hasPages())
            <div class="flex justify-center pt-2">
                {{ $deployments->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
