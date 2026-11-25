@extends('layouts.app')

@section('title', 'GCash Transaction Receipt - ' . $booking->booking_code)

@section('content')
@php
    $primaryRef = $booking->paymongo_payment_id
        ?? $booking->transactions->firstWhere('reference_number', '!=', null)?->reference_number
        ?? $booking->booking_code;
    $paidTotal = (float) ($booking->paid_amount ?? $booking->transactions->where('status', 'completed')->whereIn('type', ['advance_deposit', 'platform_fee', 'stay_payment'])->sum('amount'));
    $isVerified = $booking->isPaid();
    $paymentMethodLabel = \App\Services\PayMongoService::formatPaymentMethodLabel($booking->payment_method);
@endphp

<div class="page-enter max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Top Actions -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 print:hidden">
        <a href="{{ $isSeller ? route('host.transactions.index') : route('customer.transactions') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition-colors">
            &larr; Back to {{ $isSeller ? 'Seller Ledger' : 'My Transactions' }}
        </a>

        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/25 transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print / Save Receipt</span>
        </button>
    </div>

    <!-- Official Receipt Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">

        <!-- Banner Header -->
        <div class="bg-gradient-to-r {{ $isVerified ? 'from-emerald-600 to-teal-600' : 'from-amber-600 to-orange-600' }} px-6 sm:px-8 py-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-white/20 text-[10px] font-black uppercase tracking-wider">
                        {{ $isSeller ? 'Seller Receipt (Payment Received)' : 'Buyer Receipt (Payment Transmitted)' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-md bg-blue-500 text-white text-[10px] font-black uppercase tracking-wider">
                        {{ $paymentMethodLabel }} Verified Channel
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight">
                    Official {{ $paymentMethodLabel }} Transaction Confirmation
                </h1>
                <p class="text-xs text-white/85 mt-0.5 font-mono">
                    Booking Ref: {{ $booking->booking_code }} &bull; Transaction Ref: {{ $primaryRef }}
                </p>
            </div>

            <div class="sm:text-right">
                <span class="text-[11px] uppercase tracking-wider text-white/80 block">
                    {{ $isVerified ? 'Amount Verified' : 'Amount Due' }}
                </span>
                <span class="text-3xl font-black font-mono tabular-nums">
                    ₱{{ number_format($paidTotal > 0 ? $paidTotal : (float) $booking->advance_deposit_amount, 2) }}
                </span>
                <span class="block text-[11px] font-bold text-white/90 mt-0.5">
                    Status: {{ strtoupper($booking->payment_status) }}
                </span>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">

            <!-- Dual-Account Traceability Cards (Buyer & Seller Side-by-Side) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 space-y-1.5">
                    <div class="flex items-center justify-between border-b border-blue-200/60 dark:border-blue-800/60 pb-2 mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-blue-700 dark:text-blue-300">
                            Buyer Account (Payment Sent)
                        </span>
                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">Sender</span>
                    </div>
                    <div class="font-black text-slate-900 dark:text-white text-sm">{{ $booking->guest_name }}</div>
                    <div class="text-slate-600 dark:text-slate-300 font-mono">{{ $booking->user?->email ?? $booking->guest_email }}</div>
                    <div class="text-slate-500 dark:text-slate-400 font-mono">GCash / Mobile: {{ $booking->guest_phone }}</div>
                </div>

                <div class="p-4 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 space-y-1.5">
                    <div class="flex items-center justify-between border-b border-emerald-200/60 dark:border-emerald-800/60 pb-2 mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                            Seller Account (Payment Received)
                        </span>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Recipient</span>
                    </div>
                    <div class="font-black text-slate-900 dark:text-white text-sm">{{ $seller?->name ?? 'DirectStay Host' }}</div>
                    <div class="text-slate-600 dark:text-slate-300 font-mono">{{ $seller?->email ?? 'host@directstay.local' }}</div>
                    <div class="text-slate-500 dark:text-slate-400 font-mono">GCash Account: {{ $seller?->phone ?? '09478847463' }}</div>
                </div>
            </div>

            <!-- Payment Metadata Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Payment Method</span>
                    <strong class="text-blue-600 dark:text-blue-400 font-black">{{ $paymentMethodLabel }}</strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Reference No.</span>
                    <strong class="font-mono text-slate-900 dark:text-white break-all">{{ $primaryRef }}</strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Condo Unit</span>
                    <strong class="text-slate-900 dark:text-white">{{ $building->code }} &bull; {{ $unit->unit_number }}</strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 block">Paid / Verified At</span>
                    <strong class="text-slate-900 dark:text-white">
                        {{ ($booking->paid_at ?? $booking->verified_at ?? $booking->updated_at)?->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                    </strong>
                </div>
            </div>

            <!-- Itemized Ledger Table -->
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
                    Traceable Ledger Entries ({{ $booking->transactions->count() }})
                </h2>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="py-3 px-4">Ledger ID</th>
                                <th class="py-3 px-4">Description / Type</th>
                                <th class="py-3 px-4">GCash Reference</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($booking->transactions as $txn)
                                @php
                                    $labels = [
                                        'advance_deposit' => 'Advance Security Deposit Escrow',
                                        'platform_fee' => 'DirectStay Service Fee (5%)',
                                        'stay_payment' => 'Room Rate & Selected Add-ons',
                                        'penalty_deduction' => 'Checkout Penalty Deduction',
                                        'deposit_refund' => 'Security Deposit Refund',
                                    ];
                                @endphp
                                <tr>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                        #TXN-{{ str_pad((string) $txn->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $labels[$txn->type] ?? ucfirst(str_replace('_', ' ', $txn->type)) }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[11px]">
                                        {{ $txn->reference_number ?? $primaryRef }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $txn->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                            {{ ucfirst($txn->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-black text-slate-900 dark:text-white tabular-nums">
                                        ₱{{ number_format((float) $txn->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Booking Total Summary -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span>Stay Dates: <strong class="text-slate-800 dark:text-slate-200">{{ $booking->check_in_date->format('M d, Y') }} &rarr; {{ $booking->check_out_date->format('M d, Y') }}</strong> ({{ $booking->nights_count }} night(s))</span>
                <span>Booking Total: <strong class="font-mono text-sm text-slate-900 dark:text-white">₱{{ number_format((float) $booking->total_amount, 2) }}</strong></span>
            </div>
        </div>
    </div>
</div>
@endsection
