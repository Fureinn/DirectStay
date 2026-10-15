@extends('layouts.app')

@section('title', 'Account Settings - DirectStay')

@section('content')
<div class="page-enter max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative">
    <div class="ambient-glow w-64 h-64 bg-blue-500/10 -top-12 -right-12 glow-animate"></div>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-3">
                Guest Profile
            </span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">Account settings</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Keep your contact details current so new booking forms and gate passes are pre-cleared.</p>
        </div>
        <a href="{{ route('customer.bookings') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 bg-white/70 dark:bg-slate-800/70 hover:bg-white dark:hover:bg-slate-800 backdrop-blur-md shadow-2xs transition-all btn-press">
            &larr; Back to Bookings
        </a>
    </div>

    <form method="POST" action="{{ route('customer.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Contact Details Card -->
        <section class="glass-surface-dense rounded-3xl border border-white/60 dark:border-white/10 p-6 sm:p-8 shadow-sm">
            <div class="mb-6">
                <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-black">1</span>
                    Contact Information
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">These details pre-fill your next reservation roster. You can still use custom occupant details for any stay.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Full Legal Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"
                           class="w-full rounded-xl border @error('name') border-rose-400 bg-rose-50/30 @else border-slate-200 dark:border-slate-700 @enderror bg-white/70 dark:bg-slate-800/80 px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    @error('name')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email"
                           class="w-full rounded-xl border @error('email') border-rose-400 bg-rose-50/30 @else border-slate-200 dark:border-slate-700 @enderror bg-white/70 dark:bg-slate-800/80 px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    @error('email')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Mobile Number</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required autocomplete="tel"
                           class="w-full rounded-xl border @error('phone') border-rose-400 bg-rose-50/30 @else border-slate-200 dark:border-slate-700 @enderror bg-white/70 dark:bg-slate-800/80 px-3.5 py-2.5 text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    @error('phone')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <!-- Security / Password Card -->
        <section class="glass-surface-dense rounded-3xl border border-white/60 dark:border-white/10 p-6 sm:p-8 shadow-sm">
            <div class="mb-6">
                <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black">2</span>
                    Security &amp; Password
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Leave these fields blank if you do not wish to change your current password.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label for="current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Current Password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                           class="w-full rounded-xl border @error('current_password') border-rose-400 bg-rose-50/30 @else border-slate-200 dark:border-slate-700 @enderror bg-white/70 dark:bg-slate-800/80 px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    @error('current_password')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">New Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password"
                           class="w-full rounded-xl border @error('password') border-rose-400 bg-rose-50/30 @else border-slate-200 dark:border-slate-700 @enderror bg-white/70 dark:bg-slate-800/80 px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    @error('password')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white/70 dark:bg-slate-800/80 px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                </div>
            </div>
        </section>

        <div class="flex justify-end pt-2">
            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 text-xs font-black text-white shadow-md shadow-blue-500/25 transition-all btn-press cursor-pointer">
                Save Profile Changes
            </button>
        </div>
    </form>
</div>
@endsection
