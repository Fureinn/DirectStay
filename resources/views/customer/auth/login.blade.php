@extends('layouts.app')

@section('title', 'Guest Sign In - DirectStay Ortigas')

@section('content')
<div class="page-enter max-w-md mx-auto px-4 py-16 relative">
    <!-- Ambient decorative glow -->
    <div class="ambient-glow w-64 h-64 bg-blue-500/15 -top-12 -right-12 glow-animate"></div>
    <div class="ambient-glow w-48 h-48 bg-emerald-500/10 -bottom-10 -left-10 glow-animate"></div>

    <div class="glass-surface-dense rounded-3xl border border-white/70 dark:border-white/10 p-8 shadow-2xl backdrop-blur-2xl relative z-10">
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-blue-700 flex items-center justify-center text-white font-black text-base shadow-md shadow-blue-500/25 mx-auto mb-3">
                DS
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-2">
                Guest Direct Portal
            </span>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Welcome Back</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Access your reservations, gate pass clearance, and compliance status</p>
        </div>

        @if(session('info'))
            <div class="mb-5 p-3.5 rounded-2xl bg-blue-50/90 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-200 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-200 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @error('email')
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50/90 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <form action="{{ route('customer.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                <input type="email" name="email" id="emailInput" value="{{ old('email', 'customer@directstay.local') }}" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/70 dark:bg-slate-800/80 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Password</label>
                <input type="password" name="password" id="passwordInput" value="password" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/70 dark:bg-slate-800/80 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400">
                    <input type="checkbox" name="remember" checked class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
                    <span>Remember me</span>
                </label>
                <button type="button" onclick="fillGuestDemo()" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                    Autofill Demo Guest
                </button>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl text-xs font-black text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 transition-all btn-press cursor-pointer">
                Sign In to Guest Account
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>New to DirectStay?</span>
            <a href="{{ route('customer.register') }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                Create an Account &rarr;
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function fillGuestDemo() {
        document.getElementById('emailInput').value = 'customer@directstay.local';
        document.getElementById('passwordInput').value = 'password';
    }
</script>
@endpush
@endsection
