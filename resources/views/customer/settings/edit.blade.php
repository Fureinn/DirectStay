@extends('layouts.app')

@section('title', 'Account Settings - DirectStay')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-3">Guest account</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Account settings</h1>
            <p class="text-sm text-slate-500 mt-2">Keep your contact details current so new booking forms are ready to go.</p>
        </div>
        <a href="{{ route('customer.bookings') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 border border-slate-300 bg-white hover:bg-slate-50">
            Back to bookings
        </a>
    </div>

    <form method="POST" action="{{ route('customer.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Contact details</h2>
                <p class="text-xs text-slate-500 mt-1">These details pre-fill your next reservation. You can still use different lead-guest details for a particular stay.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1.5">Mobile number</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required autocomplete="tel" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>
            </div>
        </section>

        <section class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Change password</h2>
                <p class="text-xs text-slate-500 mt-1">Leave these fields blank to keep your current password.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1.5">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/20">
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-emerald-600 text-sm font-bold text-white shadow-md shadow-emerald-600/20 hover:bg-emerald-700">Save settings</button>
        </div>
    </form>
</div>
@endsection
