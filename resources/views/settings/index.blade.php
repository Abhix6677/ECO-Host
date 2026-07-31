@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div x-data="{ activeTab: 'profile' }" class="space-y-8">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight flex items-center space-x-3">
                <i data-lucide="settings" class="w-7 h-7 text-indigo-400"></i>
                <span>Account &amp; System Settings</span>
            </h2>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">
                Manage your EcoHost profile, credentials, and cloud hosting engine configuration
            </p>
        </div>

        <!-- Node Status Badge -->
        <div class="shrink-0 flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs font-bold shadow-md">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <i data-lucide="cpu" class="w-4 h-4 text-emerald-400"></i>
            <span>EcoHost 24/7 Engine Connected</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-hostinger-border space-x-2 sm:space-x-4 overflow-x-auto">
        <button @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'border-indigo-500 text-white font-bold bg-indigo-500/10' : 'border-transparent text-gray-400 hover:text-gray-200'"
                class="px-5 py-3 text-xs sm:text-sm font-semibold rounded-t-xl border-b-2 transition flex items-center space-x-2 shrink-0">
            <i data-lucide="user" class="w-4 h-4"></i>
            <span>Profile Settings</span>
        </button>

        <button @click="activeTab = 'node'"
                :class="activeTab === 'node' ? 'border-indigo-500 text-white font-bold bg-indigo-500/10' : 'border-transparent text-gray-400 hover:text-gray-200'"
                class="px-5 py-3 text-xs sm:text-sm font-semibold rounded-t-xl border-b-2 transition flex items-center space-x-2 shrink-0">
            <i data-lucide="server" class="w-4 h-4"></i>
            <span>EcoHost Cloud Node</span>
        </button>

        <button @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'border-indigo-500 text-white font-bold bg-indigo-500/10' : 'border-transparent text-gray-400 hover:text-gray-200'"
                class="px-5 py-3 text-xs sm:text-sm font-semibold rounded-t-xl border-b-2 transition flex items-center space-x-2 shrink-0">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            <span>Security &amp; Password</span>
        </button>
    </div>

    <!-- TAB 1: Profile Settings -->
    <div x-show="activeTab === 'profile'" x-transition class="space-y-6">
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-6 sm:p-8 shadow-xl max-w-3xl space-y-6">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                    <i data-lucide="user-check" class="w-5 h-5 text-indigo-400"></i>
                    <span>Profile Details</span>
                </h3>
                <p class="text-xs text-gray-400 mt-1">Update your account name and email address</p>
            </div>

            <form method="POST" action="{{ route('settings.profile') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-2">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-3 bg-hostinger-dark border border-hostinger-border rounded-xl text-white text-sm focus:outline-none focus:border-indigo-500 transition">
                    @error('name')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-2">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-3 bg-hostinger-dark border border-hostinger-border rounded-xl text-white text-sm focus:outline-none focus:border-indigo-500 transition">
                    @error('email')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center space-x-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: EcoHost Cloud Node Info -->
    <div x-show="activeTab === 'node'" x-transition class="space-y-6">
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-6 sm:p-8 shadow-xl max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                        <i data-lucide="cpu" class="w-5 h-5 text-indigo-400"></i>
                        <span>EcoHost Cloud Engine Specs</span>
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Live status and configuration parameters of your hosting engine</p>
                </div>

                @if ($nodeInfo['online'])
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Online (HTTP 200)</span>
                    </span>
                @else
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-rose-500/10 text-rose-400 text-xs font-bold border border-rose-500/20">
                        <span>Offline</span>
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                <div class="bg-hostinger-dark border border-hostinger-border rounded-xl p-4 space-y-1">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Receiver Endpoint URL</span>
                    <p class="font-mono text-xs font-bold text-indigo-300 break-all">{{ $nodeInfo['url'] }}</p>
                </div>

                <div class="bg-hostinger-dark border border-hostinger-border rounded-xl p-4 space-y-1">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Storage Environment</span>
                    <p class="font-bold text-xs text-white">{{ $nodeInfo['environment'] }}</p>
                </div>

                <div class="bg-hostinger-dark border border-hostinger-border rounded-xl p-4 space-y-1">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Max ZIP Extracted Capacity</span>
                    <p class="font-bold text-xs text-white">{{ $nodeInfo['storage_limit'] }}</p>
                </div>

                <div class="bg-hostinger-dark border border-hostinger-border rounded-xl p-4 space-y-1">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Max Files Per Project</span>
                    <p class="font-bold text-xs text-white">{{ $nodeInfo['max_files'] }}</p>
                </div>

                <div class="bg-hostinger-dark border border-hostinger-border rounded-xl p-4 space-y-1 sm:col-span-2">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">API Authentication Token</span>
                    <p class="font-mono text-xs text-gray-300">{{ $nodeInfo['token_masked'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: Security & Password -->
    <div x-show="activeTab === 'security'" x-transition class="space-y-6">
        <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-6 sm:p-8 shadow-xl max-w-3xl space-y-6">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                    <i data-lucide="lock" class="w-5 h-5 text-indigo-400"></i>
                    <span>Update Password</span>
                </h3>
                <p class="text-xs text-gray-400 mt-1">Ensure your account uses a strong, random password</p>
            </div>

            <form method="POST" action="{{ route('settings.password') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-2">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required
                           class="w-full px-4 py-3 bg-hostinger-dark border border-hostinger-border rounded-xl text-white text-sm focus:outline-none focus:border-indigo-500 transition">
                    @error('current_password')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-2">New Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-4 py-3 bg-hostinger-dark border border-hostinger-border rounded-xl text-white text-sm focus:outline-none focus:border-indigo-500 transition">
                    @error('password')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full px-4 py-3 bg-hostinger-dark border border-hostinger-border rounded-xl text-white text-sm focus:outline-none focus:border-indigo-500 transition">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center space-x-2">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
