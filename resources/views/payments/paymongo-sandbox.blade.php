@extends('layouts.app')

@section('title', 'PayMongo Test Checkout - DirectStay')
@section('hideNav', 'true')

@section('content')
<div class="min-h-screen bg-slate-100 dark:bg-slate-950 flex items-center justify-center px-4 py-10"
     x-data="{ method: 'gcash', submitting: false }">
    <div class="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">

        <!-- PayMongo Branded Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5 text-white flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-black uppercase tracking-wider">
                        PayMongo Checkout
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-amber-400/90 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                        Test Sandbox
                    </span>
                </div>
                <h1 class="text-lg font-black tracking-tight mt-2">DirectStay Solutions Co.</h1>
                <p class="text-xs text-emerald-100">Ref: {{ $booking->booking_code }} &bull; {{ $booking->unit->unit_number }}</p>
            </div>
            <div class="text-right">
                <span class="text-[11px] text-emerald-100 block">Amount Due</span>
                <span class="text-2xl font-black font-mono tabular-nums">₱{{ number_format((float) $session['amount'], 2) }}</span>
            </div>
        </div>

        <div class="p-6 sm:p-7 space-y-5">
            <!-- Order Description -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Guest</span>
                    <strong class="text-slate-900 dark:text-white">{{ $booking->guest_name }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Description</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200 text-right">{{ $session['description'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Session ID</span>
                    <span class="font-mono text-[11px] text-slate-500">{{ $session['checkout_id'] }}</span>
                </div>
            </div>

            <!-- Simulated Payment Form -->
            <form action="{{ route('paymongo.sandbox.complete', $session['checkout_id']) }}" method="POST"
                  @submit="submitting = true" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-black text-slate-800 dark:text-slate-200 mb-2">
                        Select PayMongo Payment Method
                    </label>
                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition-all"
                               :class="method === 'gcash' ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/40 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" name="payment_method" value="gcash" x-model="method" class="accent-blue-600">
                            <span class="font-bold text-slate-900 dark:text-white">GCash</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition-all"
                               :class="method === 'paymaya' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" name="payment_method" value="paymaya" x-model="method" class="accent-emerald-600">
                            <span class="font-bold text-slate-900 dark:text-white">Maya</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition-all"
                               :class="method === 'card' ? 'border-indigo-500 bg-indigo-50/70 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" name="payment_method" value="card" x-model="method" class="accent-indigo-600">
                            <span class="font-bold text-slate-900 dark:text-white">Credit / Debit Card</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition-all"
                               :class="method === 'grab_pay' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700'">
                            <input type="radio" name="payment_method" value="grab_pay" x-model="method" class="accent-emerald-600">
                            <span class="font-bold text-slate-900 dark:text-white">GrabPay</span>
                        </label>
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-200">
                    Local Sandbox Simulator is active because <code>PAYMONGO_SECRET_KEY</code> is blank in <code>.env</code>. Adding a real <code>sk_test_...</code> or <code>sk_live_...</code> key automatically redirects to PayMongo's live checkout page.
                </div>

                <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
                    <button type="submit" name="action" value="succeed" :disabled="submitting"
                            class="flex-1 px-5 py-3 rounded-2xl text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/25 transition-all cursor-pointer">
                        Pay ₱{{ number_format((float) $session['amount'], 2) }} (Succeed) &rarr;
                    </button>
                    <button type="submit" name="action" value="fail" :disabled="submitting"
                            class="px-5 py-3 rounded-2xl text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 border border-rose-200 dark:border-rose-800 transition-all cursor-pointer">
                        Cancel / Fail
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
