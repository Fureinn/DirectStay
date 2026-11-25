@extends('layouts.app')

@section('title', 'PayMongo GCash & QR Ph Test Checkout - DirectStay')
@section('hideNav', 'true')

@section('content')
<div class="min-h-screen bg-slate-100 dark:bg-slate-950 flex items-center justify-center px-4 py-10"
     x-data="{ submitting: false, selectedMethod: 'gcash' }">
    <div class="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">

        <!-- PayMongo GCash / QR Ph Branded Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 text-white flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-black uppercase tracking-wider">
                        PayMongo &bull; GCash &amp; QR Ph
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-amber-400/90 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                        Test Sandbox
                    </span>
                </div>
                <h1 class="text-lg font-black tracking-tight mt-2">DirectStay Solutions Co.</h1>
                <p class="text-xs text-blue-100">Ref: {{ $booking->booking_code }} &bull; {{ $booking->unit->unit_number }}</p>
            </div>
            <div class="text-right">
                <span class="text-[11px] text-blue-100 block">Amount Due</span>
                <span class="text-2xl font-black font-mono tabular-nums">₱{{ number_format((float) $session['amount'], 2) }}</span>
            </div>
        </div>

        <div class="p-6 sm:p-7 space-y-5">
            <!-- Order Description -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Buyer (Guest)</span>
                    <strong class="text-slate-900 dark:text-white">{{ $booking->guest_name }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Seller (Host)</span>
                    <strong class="text-slate-900 dark:text-white">{{ $booking->unit->host?->name ?? 'DirectStay Host' }}</strong>
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

            <!-- GCash & QR Ph Payment Methods -->
            <form action="{{ route('paymongo.sandbox.complete', $session['checkout_id']) }}" method="POST"
                  @submit="submitting = true" class="space-y-4">
                @csrf

                <div class="space-y-2.5">
                    <label class="block text-xs font-black text-slate-800 dark:text-slate-200">
                        Choose Payment Method
                    </label>

                    <label class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                           :class="selectedMethod === 'gcash' ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/40' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="gcash" x-model="selectedMethod" class="accent-blue-600">
                            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white font-black text-sm flex items-center justify-center shrink-0">G</div>
                            <div>
                                <span class="font-black text-slate-900 dark:text-white block text-sm">GCash E-Wallet</span>
                                <span class="text-[11px] text-blue-700 dark:text-blue-300">Direct GCash e-wallet authorization</span>
                            </div>
                        </div>
                        <span x-show="selectedMethod === 'gcash'" class="px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-600 text-white">Selected ✓</span>
                    </label>

                    <label class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all"
                           :class="selectedMethod === 'qrph' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="qrph" x-model="selectedMethod" class="accent-emerald-600">
                            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shrink-0">QR</div>
                            <div>
                                <span class="font-black text-slate-900 dark:text-white block text-sm">QR Ph (Scan to Pay)</span>
                                <span class="text-[11px] text-emerald-700 dark:text-emerald-300">Scan via GCash, Maya, BPI, UnionBank, or any QR Ph app</span>
                            </div>
                        </div>
                        <span x-show="selectedMethod === 'qrph'" x-cloak class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-600 text-white">Selected ✓</span>
                    </label>
                </div>

                <!-- Sandbox QR Ph Preview -->
                <div x-show="selectedMethod === 'qrph'" x-cloak
                     class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 flex flex-col items-center text-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                        Simulated PayMongo QR Ph Code
                    </span>
                    <div class="p-3 rounded-2xl bg-white border border-slate-200 shadow-xs">
                        <svg class="w-36 h-36 text-slate-900" viewBox="0 0 100 100" fill="currentColor" aria-label="Sandbox QR Ph Code">
                            <rect x="6" y="6" width="26" height="26" rx="3" fill="none" stroke="currentColor" stroke-width="5"/>
                            <rect x="13" y="13" width="12" height="12" rx="1"/>
                            <rect x="68" y="6" width="26" height="26" rx="3" fill="none" stroke="currentColor" stroke-width="5"/>
                            <rect x="75" y="13" width="12" height="12" rx="1"/>
                            <rect x="6" y="68" width="26" height="26" rx="3" fill="none" stroke="currentColor" stroke-width="5"/>
                            <rect x="13" y="75" width="12" height="12" rx="1"/>
                            <rect x="40" y="8" width="6" height="6"/><rect x="52" y="8" width="6" height="6"/>
                            <rect x="40" y="20" width="12" height="6"/><rect x="46" y="32" width="6" height="6"/>
                            <rect x="8" y="40" width="6" height="12"/><rect x="20" y="46" width="12" height="6"/>
                            <rect x="40" y="42" width="20" height="16" rx="2" fill="#059669"/>
                            <rect x="68" y="40" width="8" height="8"/><rect x="84" y="40" width="10" height="8"/>
                            <rect x="72" y="54" width="10" height="8"/><rect x="86" y="54" width="8" height="14"/>
                            <rect x="40" y="66" width="8" height="8"/><rect x="54" y="66" width="10" height="8"/>
                            <rect x="40" y="82" width="14" height="10"/><rect x="64" y="78" width="12" height="14"/>
                            <rect x="82" y="76" width="12" height="16"/>
                        </svg>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        In live PayMongo mode, PayMongo generates a real dynamic QR Ph image for <strong>₱{{ number_format((float) $session['amount'], 2) }}</strong>.
                    </p>
                </div>

                <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-200">
                    Local Sandbox Simulator is active because <code>PAYMONGO_SECRET_KEY</code> is blank in <code>.env</code>. Adding a real <code>sk_test_...</code> or <code>sk_live_...</code> key automatically redirects to PayMongo's live checkout page with GCash and QR Ph.
                </div>

                <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
                    <button type="submit" name="action" value="succeed" :disabled="submitting"
                            class="flex-1 px-5 py-3 rounded-2xl text-xs font-black text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/25 transition-all cursor-pointer">
                        <span x-text="selectedMethod === 'qrph' ? 'Confirm QR Ph ₱{{ number_format((float) $session['amount'], 2) }} (Succeed) →' : 'Authorize GCash ₱{{ number_format((float) $session['amount'], 2) }} (Succeed) →'"></span>
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
