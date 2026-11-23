@extends('layouts.app')

@section('title', 'My GCash Transactions - DirectStay')

@section('content')
<div class="page-enter max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                <span>Buyer Account Ledger &bull; GCash Only</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                My GCash Transactions &amp; Receipts
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Account: <strong class="text-slate-800 dark:text-slate-200">{{ $user->name }}</strong> ({{ $user->email }}) &bull; Every GCash payment transmitted is recorded and traceable on both buyer and seller accounts.
            </p>
        </div>

        <a href="{{ route('customer.bookings') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl font-bold text-xs text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            &larr; Back to My Bookings
        </a>
    </div>

    <!-- Summary Bento Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Transmitted (GCash)</span>
            <span class="text-2xl font-black font-mono text-blue-600 dark:text-blue-400 tabular-nums mt-1 block">
                ₱{{ number_format((float) $totalPaid, 2) }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Verified outgoing payments</span>
        </div>

        <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Security Deposits Held</span>
            <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 tabular-nums mt-1 block">
                ₱{{ number_format((float) $depositsInEscrow, 2) }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Refundable escrow protection</span>
        </div>

        <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Deposit Refunds</span>
            <span class="text-2xl font-black font-mono text-slate-900 dark:text-white tabular-nums mt-1 block">
                ₱{{ number_format((float) $totalRefunded, 2) }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Returned post-checkout</span>
        </div>

        <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Verified Records</span>
            <span class="text-2xl font-black font-mono text-slate-900 dark:text-white tabular-nums mt-1 block">
                {{ $verifiedCount }}
            </span>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Synced with seller ledger</span>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 shadow-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200/60 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white">Buyer Payment History</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Official GCash ledger proof for your staycation bookings</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 self-start sm:self-auto">
                Payment Channel: GCash Only
            </span>
        </div>

        @if($transactions->isEmpty())
            <div class="p-12 text-center">
                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">No GCash transactions recorded yet</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-5">Reserve a condo unit and settle your deposit via GCash to view your traceable payment receipt here.</p>
                <a href="{{ route('units.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-2xl text-xs font-black text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    Explore Condo Units &rarr;
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300 border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200/60 dark:border-slate-800">
                        <tr>
                            <th class="py-3.5 px-6">Txn ID &amp; Date</th>
                            <th class="py-3.5 px-6">Booking &amp; Seller (Host)</th>
                            <th class="py-3.5 px-6">Transaction Type</th>
                            <th class="py-3.5 px-6">Method &amp; GCash Ref</th>
                            <th class="py-3.5 px-6">Amount</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Confirmation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($transactions as $txn)
                            @php
                                $booking = $txn->booking;
                                $typeLabels = [
                                    'advance_deposit' => 'Advance Security Deposit',
                                    'platform_fee' => 'DirectStay Service Fee (5%)',
                                    'stay_payment' => 'Full Stay Room & Add-ons',
                                    'penalty_deduction' => 'Checkout Penalty Deduction',
                                    'deposit_refund' => 'Security Deposit Refund',
                                ];
                                $typeLabel = $typeLabels[$txn->type] ?? ucfirst(str_replace('_', ' ', $txn->type));
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="font-mono font-black text-slate-900 dark:text-white block">#TXN-{{ str_pad((string) $txn->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $txn->updated_at?->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400 block">{{ $booking?->booking_code }}</span>
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $booking?->unit?->unit_number }}</span>
                                    <span class="text-[11px] text-slate-400 block truncate max-w-[200px]">Seller: {{ $booking?->unit?->host?->name ?? 'DirectStay Host' }}</span>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $typeLabel }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-1">
                                        GCash
                                    </span>
                                    <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300 block">
                                        {{ $txn->reference_number ?? $booking?->paymongo_payment_id ?? 'Awaiting Payment' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-mono font-black text-slate-900 dark:text-white tabular-nums">
                                    ₱{{ number_format((float) $txn->amount, 2) }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($txn->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Transmitted ✓
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
                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                    @if($booking)
                                        <a href="{{ route('transactions.receipt', $booking->booking_code) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/70 hover:bg-blue-100 border border-blue-200 dark:border-blue-800 transition-colors">
                                            View Receipt &rarr;
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
