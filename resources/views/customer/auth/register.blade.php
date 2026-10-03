@extends('layouts.app')

@section('title', 'Guest Registration - DirectStay')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xl shadow-slate-200/50">
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 mb-3">
                Direct Guest Account
            </span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Create Guest Account</h1>
            <p class="text-xs text-slate-500 mt-1">Book directly, manage gate passes, and track staycation reservations</p>
        </div>

        <form action="{{ route('customer.register.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="As shown on Government ID" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="For gate pass delivery" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 09171234567" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Min 6 characters"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition-all mt-2">
                Register as Guest
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            Already have an account?
            <a href="{{ route('customer.login') }}" class="font-bold text-blue-600 hover:underline">
                Sign In
            </a>
        </div>
    </div>
</div>
@endsection
