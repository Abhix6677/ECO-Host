@extends('layouts.app')

@section('content')
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center shadow-xl shadow-indigo-500/30 mx-auto mb-4">
            <i data-lucide="cloud-lightning" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Create Account</h2>
        <p class="text-sm text-slate-600 font-medium mt-2">Start hosting your static websites in seconds</p>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-xl">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
            @csrf

            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Full Name</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-600">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 text-sm transition shadow-sm">
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-600">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </div>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 text-sm transition shadow-sm">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-600">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 text-sm transition shadow-sm">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirm Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-600">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 text-sm transition shadow-sm">
                </div>
            </div>

            <button type="submit" 
                    :disabled="isSubmitting"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 disabled:opacity-75 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg shadow-indigo-600/25 transition transform active:scale-[0.99] flex items-center justify-center space-x-2">
                <template x-if="!isSubmitting">
                    <span class="flex items-center space-x-2">
                        <span>Create Account</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                </template>
                <template x-if="isSubmitting">
                    <span class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span>Validating &amp; Creating Account...</span>
                    </span>
                </template>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-200 text-center">
            <p class="text-xs text-slate-600">
                Already registered? 
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-800 transition">Sign In</a>
            </p>
        </div>
    </div>
</div>
@endsection
