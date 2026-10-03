@extends('layouts.app')

@section('title', 'Host Portal Login - DirectStay')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-lg shadow-slate-200/50">
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white font-extrabold text-2xl flex items-center justify-center mx-auto mb-3 shadow-md shadow-blue-500/20">
                DS
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Host Management Portal</h1>
            <p class="text-xs text-slate-500 mt-1">DirectStay Condominium Lessor & Security Administration</p>
        </div>

        <form action="{{ route('host.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Host Email Address</label>
                <input type="email" name="email" id="emailInput" value="{{ old('email', 'host@directstay.local') }}" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" id="passwordInput" value="password" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                    <input type="checkbox" name="remember" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>Keep me logged in</span>
                </label>
                <span class="text-[11px] text-blue-600 font-semibold cursor-pointer" onclick="fillDemo()">Use Demo Host</span>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-md transition-all">
                Sign In to Host Portal
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center">
            <p class="text-[11px] text-slate-400">
                DirectStay Host Account: <code>host@directstay.local</code> / <code>password</code>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function fillDemo() {
        document.getElementById('emailInput').value = 'host@directstay.local';
        document.getElementById('passwordInput').value = 'password';
    }
</script>
@endpush
@endsection
