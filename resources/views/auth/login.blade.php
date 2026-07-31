@extends('layouts.app')

@section('content')
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center shadow-xl shadow-indigo-500/30 mx-auto mb-4">
            <i data-lucide="cloud-lightning" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-white tracking-tight">Welcome Back</h2>
        <p class="text-sm text-gray-400 mt-2">Sign in to manage & deploy your web projects</p>
    </div>

    <div class="bg-hostinger-card border border-hostinger-border rounded-2xl p-8 shadow-2xl glow-effect">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-medium">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </div>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-11 pr-4 py-3 bg-hostinger-dark border border-hostinger-border rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm transition">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                           class="w-full pl-11 pr-4 py-3 bg-hostinger-dark border border-hostinger-border rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm transition">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-hostinger-dark border-hostinger-border text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0">
                    <span class="text-xs font-medium text-gray-400">Remember me</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition transform active:scale-[0.99] flex items-center justify-center space-x-2">
                <span>Sign In to Console</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-hostinger-border text-center">
            <p class="text-xs text-gray-400">
                Don't have an EcoHost account? 
                <a href="{{ route('register') }}" class="font-bold text-indigo-400 hover:text-indigo-300 transition">Create Account</a>
            </p>
        </div>
    </div>
</div>
@endsection
