@extends('layouts.app')

@section('title', 'Seller GCash Transaction Ledger - DirectStay Host')

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Seller Ledger &bull; GCash Payments Received</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Host Transaction Ledger &amp; Receipts
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Complete audit trail of all incoming GCash payments, escrow deposits, and platform fees matching every buyer's transaction receipt.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('host.dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl font-bold text-xs text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                &larr; Host Dashboard
            </a>
        </div>
    </div>

    <!-- Seller Financial Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total GCash Received</span>
            <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 tabular-nums mt-1 block">
                ₱{{ number_format((float) $totalReceived, 2) }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Completed incoming settlements</span>
        </div>

        <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Escrow Deposits Held</span>
            <span class="text-2xl font-black font-mono text-blue-600 dark:text-blue-400 tabular-nums mt-1 block">
                ₱{{ number_format((float) $escrowHeld, 2) }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">₱1,000 per stay protection</span>
        </div>

        <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Platform Service Fees (5%)</span>
            <span class="text-2xl font-black font-mono text-slate-900 dark:text-white tabular-nums mt-1 block">
                ₱{{ number_format((float) $platformFees, 2) }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">0% host commission</span>
        </div>

        <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Penalties &amp; Refunds</span>
            <span class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400 tabular-nums mt-1 block">
                ₱{{ number_format((float) $penaltiesAndRefunds, 2) }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Post-checkout settlements</span>
        </div>
    </div>

    <!-- Filter Bar & Table -->
    <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 shadow-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200/60 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white">Seller Incoming &amp; Escrow Ledger</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Traceable to every guest's account via GCash reference ID</p>
            </div>

            <div class="flex items-center gap-1.5 text-xs">
                @foreach(['all' => 'All', 'completed' => 'Received', 'pending' => 'Pending', 'refunded' => 'Refunded'] as $key => $label)
                    <a href="{{ route('host.transactions.index', ['status' => $key]) }}"
                       class="px-3 py-1.5 rounded-xl font-bold transition-colors {{ $statusFilter === $key ? 'bg-slate-900 dark:bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($transactions->isEmpty())
            <div class="p-12 text-center text-xs text-slate-500 dark:text-slate-400">
                No transaction records found for this filter.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300 border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200/60 dark:border-slate-800">
                        <tr>
                            <th class="py-3.5 px-6">Txn ID &amp; Timestamp</th>
                            <th class="py-3.5 px-6">Buyer (Guest)</th>
                            <th class="py-3.5 px-6">Booking &amp; Unit</th>
                            <th class="py-3.5 px-6">Type</th>
                            <th class="py-3.5 px-6">Method &amp; GCash Ref</th>
                            <th class="py-3.5 px-6">Amount</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($transactions as $txn)
                            @php
                                $booking = $txn->booking;
                                $typeLabels = [
                                    'advance_deposit' => 'Advance Deposit',
                                    'platform_fee' => 'Platform Fee (5%)',
                                    'stay_payment' => 'Stay Payment',
                                    'penalty_deduction' => 'Penalty Deduction',
                                    'deposit_refund' => 'Deposit Refund',
                                ];
                                $typeLabel = $typeLabels[$txn->type] ?? ucfirst(str_replace('_', ' ', $txn->type));
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="font-mono font-black text-slate-900 dark:text-white block">#TXN-{{ str_pad((string) $txn->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $txn->updated_at?->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $booking?->guest_name ?? 'Guest' }}</span>
                                    <span class="text-[11px] text-slate-400 font-mono block">{{ $booking?->guest_email }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400 block">{{ $booking?->booking_code }}</span>
                                    <span class="text-[11px] text-slate-500">{{ $booking?->unit?->unit_number }}</span>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $typeLabel }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-1">
                                        GCash
                                    </span>
                                    <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300 block">
                                        {{ $txn->reference_number ?? $booking?->paymongo_payment_id ?? 'Pending Ref' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-mono font-black text-slate-900 dark:text-white tabular-nums">
                                    ₱{{ number_format((float) $txn->amount, 2) }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($txn->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Received ✓
                                        </span>
                                    @elseif($txn->status === 'refunded')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            Refunded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                                    @if($booking)
                                        <a href="{{ route('transactions.receipt', $booking->booking_code) }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/70 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                            Receipt
                                        </a>
                                        <a href="{{ route('host.verifications.show', $booking) }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/70 hover:bg-blue-100 border border-blue-200 dark:border-blue-800 transition-colors">
                                            Booking
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
