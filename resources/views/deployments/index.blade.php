@extends('layouts.app')

@section('title', 'Deployments')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-5">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Deployment History</h2>
            <p class="text-sm text-gray-400 mt-0.5">Full log of all deployment jobs across your websites</p>
        </div>

        <!-- Cloudflare Tunnel Status Card -->
        <div class="shrink-0">
            @if ($isTunnelConfigured)
                <div class="flex items-center space-x-2.5 px-4 py-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <i data-lucide="cloud" class="w-4 h-4"></i>
                    <span>Cloudflare Tunnel Active</span>
                </div>
            @else
                <div class="flex items-center space-x-2.5 px-4 py-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm font-semibold">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span>Local Serving (No Tunnel)</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Tunnel Setup Info Banner (when not configured) --}}
    @if (!$isTunnelConfigured)
        <div class="p-5 rounded-2xl bg-blue-500/5 border border-blue-500/20 text-sm">
            <div class="flex items-start space-x-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-400 mt-0.5 shrink-0"></i>
                <div class="space-y-2 text-gray-300">
                    <p class="font-bold text-blue-400">Enable Cloudflare Tunnel for Public URLs</p>
                    <p class="text-xs text-gray-400">Currently sites are served locally. To get public <code class="text-indigo-300">trycloudflare.com</code> URLs, run these commands and add the tunnel URL to your <code class="text-indigo-300">.env</code>:</p>
                    <div class="space-y-1.5">
                        <div class="bg-hostinger-dark border border-hostinger-border rounded-lg px-4 py-2.5 font-mono text-xs text-gray-200">
                            # 1. Install cloudflared (if not already installed)
                        </div>
                        <div class="bg-hostinger-dark border border-hostinger-border rounded-lg px-4 py-2.5 font-mono text-xs text-emerald-300">
                            cloudflared tunnel --url http://localhost:8000
                        </div>
                        <div class="bg-hostinger-dark border border-hostinger-border rounded-lg px-4 py-2.5 font-mono text-xs text-gray-200">
                            # 2. Copy the generated URL (e.g. https://abc-xyz.trycloudflare.com)
                        </div>
                        <div class="bg-hostinger-dark border border-hostinger-border rounded-lg px-4 py-2.5 font-mono text-xs text-indigo-300">
                            CLOUDFLARE_TUNNEL_URL=https://your-tunnel.trycloudflare.com
                        </div>
                        <div class="bg-hostinger-dark border border-hostinger-border rounded-lg px-4 py-2.5 font-mono text-xs text-gray-200">
                            # 3. Redeploy your sites to get public URLs
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($deployments->isEmpty())
        <!-- Empty State -->
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-16 text-center">
            <div class="w-20 h-20 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center mx-auto mb-5">
                <i data-lucide="rocket" class="w-10 h-10 text-purple-400"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">No Deployments Yet</h3>
            <p class="text-gray-400 text-sm max-w-sm mx-auto mb-6">
                Upload a website and click Deploy to start your first deployment.
            </p>
            <a href="{{ route('websites.create') }}"
               class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition">
                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                <span>Upload & Deploy First Site</span>
            </a>
        </div>
    @else
        <!-- Deployment Log Table -->
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-hostinger-border text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-hostinger-dark/50">
                            <th class="py-4 px-6">Website</th>
                            <th class="py-4 px-4">Status</th>
                            <th class="py-4 px-4">Live URL</th>
                            <th class="py-4 px-4">Deployed</th>
                            <th class="py-4 px-4 text-right">Logs</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hostinger-border text-sm">
                        @foreach ($deployments as $dep)
                            <tr class="hover:bg-hostinger-cardHover/40 transition group"
                                x-data="{ showLog: false }">
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                                            <i data-lucide="globe" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white">{{ $dep->website->name ?? '(deleted)' }}</p>
                                            <p class="text-xs text-gray-400 font-mono">{{ $dep->uuid }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if ($dep->status === 'live')
                                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span>Live</span>
                                        </span>
                                    @elseif($dep->status === 'deploying')
                                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs font-semibold border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            <span>Deploying</span>
                                        </span>
                                    @elseif($dep->status === 'failed')
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
                                        <a href="{{ $dep->live_url }}" target="_blank"
                                           class="text-xs text-indigo-400 hover:text-indigo-300 font-mono transition flex items-center space-x-1 max-w-[200px]">
                                            <span class="truncate">{{ Str::limit($dep->live_url, 35) }}</span>
                                            <i data-lucide="external-link" class="w-3 h-3 shrink-0"></i>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-xs text-gray-400">
                                    @if ($dep->deployed_at)
                                        <span title="{{ $dep->deployed_at->toDateTimeString() }}">
                                            {{ $dep->deployed_at->diffForHumans() }}
                                        </span>
                                    @else
                                        {{ $dep->created_at->diffForHumans() }}
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    @if ($dep->log_output)
                                        <button @click="showLog = !showLog"
                                                class="text-xs font-bold text-gray-400 hover:text-white bg-hostinger-dark border border-hostinger-border hover:border-gray-500 px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5 ml-auto">
                                            <i data-lucide="terminal" class="w-3.5 h-3.5"></i>
                                            <span x-text="showLog ? 'Hide' : 'Logs'"></span>
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            {{-- Log Expansion Row --}}
                            @if ($dep->log_output)
                                <tr x-show="showLog" x-collapse>
                                    <td colspan="5" class="px-6 pb-4">
                                        <div class="bg-black/60 border border-hostinger-border rounded-xl p-4 font-mono text-xs text-emerald-300 whitespace-pre-wrap leading-relaxed max-h-64 overflow-y-auto">{{ $dep->log_output }}</div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if ($deployments->hasPages())
            <div class="flex justify-center">
                {{ $deployments->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
