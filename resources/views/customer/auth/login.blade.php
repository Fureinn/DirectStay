@extends('layouts.app')

@section('title', 'Guest Sign In - DirectStay')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xl shadow-slate-200/50">
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 mb-3">
                Guest Portal
            </span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Guest Sign In</h1>
            <p class="text-xs text-slate-500 mt-1">Access your reservations, gate pass clearance, and compliance status</p>
        </div>

        <form action="{{ route('customer.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" id="emailInput" value="{{ old('email', 'customer@directstay.local') }}" required
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
                    <span>Remember me</span>
                </label>
                <button type="button" onclick="fillGuestDemo()" class="text-[11px] font-bold text-blue-600 hover:underline">
                    Autofill Demo Guest
                </button>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition-all">
                Sign In to Guest Account
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>New to DirectStay?</span>
            <a href="{{ route('customer.register') }}" class="font-bold text-blue-600 hover:underline">
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
