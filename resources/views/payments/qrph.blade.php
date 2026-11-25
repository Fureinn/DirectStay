@extends('layouts.app')

@section('title', 'Scan QR Ph to Pay - DirectStay')
@section('hideNav', 'true')

@section('content')
@php
    $exactAmountFormatted = number_format($payment->amount_centavos / 100, 2);
    $exactAmountRaw = number_format($payment->amount_centavos / 100, 2, '.', '');
@endphp

<div class="min-h-screen bg-slate-100 dark:bg-slate-950 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">

        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-blue-600 px-6 py-5 text-white flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-white/20 text-[10px] font-black uppercase tracking-wider">
                        PayMongo &bull; Static QR Ph
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-400/90 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                        Auto-Detect
                    </span>
                </div>
                <h1 class="text-lg font-black tracking-tight mt-2">DirectStay QR Ph Payment</h1>
                <p class="text-xs text-emerald-100">
                    Booking: {{ $booking->booking_code }} &bull; {{ $booking->unit->unit_number ?? 'DirectStay Unit' }}
                </p>
            </div>
            <div class="text-right shrink-0">
                <span class="text-[11px] text-emerald-100 block">Status</span>
                <span id="qrph-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-white/15 border border-white/25">
                    @if($payment->isPaid())
                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span>
                        Paid ✓
                    @else
                        <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                        Awaiting Payment
                    @endif
                </span>
            </div>
        </div>

        <div class="p-6 sm:p-7 space-y-5">

            <!-- Exact Amount Highlight -->
            <div class="p-5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border-2 border-emerald-500/70 text-center space-y-2"
                 x-data="{ copied: false }">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 block">
                    Exact Amount to Pay
                </span>
                <div class="flex items-center justify-center gap-3">
                    <span class="text-3xl sm:text-4xl font-black font-mono tabular-nums text-slate-900 dark:text-white">
                        ₱{{ $exactAmountFormatted }}
                    </span>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $exactAmountRaw }}'); copied = true; setTimeout(() => copied = false, 2000);"
                            class="px-3 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors cursor-pointer">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak>Copied!</span>
                    </button>
                </div>
                <p class="text-xs font-semibold text-amber-800 dark:text-amber-200 bg-amber-100/80 dark:bg-amber-950/60 border border-amber-300/80 dark:border-amber-800 rounded-xl px-3.5 py-2.5 text-left leading-relaxed">
                    <strong>Important:</strong> Please enter the <strong>exact centavos (₱{{ $exactAmountFormatted }})</strong> in GCash, Maya, or your banking app so our system detects and confirms your booking automatically.
                </p>
            </div>

            <!-- Static PayMongo QR Ph Image -->
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex flex-col items-center text-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Scan with GCash, Maya, or any QR Ph Banking App
                </span>
                <div class="p-3 rounded-2xl bg-white border border-slate-200 shadow-xs">
                    <img src="{{ asset('images/paymongo-static-qr.png') }}"
                         alt="PayMongo Static QR Ph Code"
                         class="w-60 h-60 sm:w-64 sm:h-64 object-contain mx-auto">
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Listening for payment confirmation every 3 seconds...</span>
                </div>
            </div>

            @if($payment->isPaid())
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-center space-y-3">
                    <p class="text-xs font-bold text-emerald-800 dark:text-emerald-200">
                        Payment received! Redirecting you to your booking confirmation...
                    </p>
                    <a href="{{ route('bookings.show', $payment->booking_id) }}"
                       class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                        View Booking Confirmation &rarr;
                    </a>
                </div>
            @endif

            <!-- Optional Sandbox Simulation Button -->
            @if($sandboxSimulation && ! $payment->isPaid())
                <form action="{{ route('payments.qrph.simulate', $payment) }}" method="POST" class="pt-1">
                    @csrf
                    <button type="submit"
                            class="w-full px-5 py-3 rounded-2xl text-xs font-black text-slate-800 dark:text-slate-100 bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/50 dark:hover:bg-amber-900/80 border border-amber-300 dark:border-amber-700 transition-all cursor-pointer">
                        Simulate Payment (Sandbox Mode) &rarr;
                    </button>
                </form>
            @endif

            <!-- Footer Navigation -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                <a href="{{ route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3]) }}"
                   class="font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    &larr; Back to Compliance Portal
                </a>
                <span class="font-mono text-[11px] text-slate-400">
                    Payment #{{ $payment->id }}
                </span>
            </div>
        </div>
    </div>
</div>

@if(! $payment->isPaid())
<script>
    (function () {
        const statusUrl = @json(route('payments.status', $payment), JSON_UNESCAPED_SLASHES);
        const pollInterval = setInterval(async () => {
            try {
                const response = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!response.ok) {
                    return;
                }
                const data = await response.json();
                if (data.status === 'paid' && data.redirect_url) {
                    clearInterval(pollInterval);
                    window.location.href = data.redirect_url;
                }
            } catch (error) {
                // Retry automatically on next 3-second interval
            }
        }, 3000);
    })();
</script>
@endif
@endsection
