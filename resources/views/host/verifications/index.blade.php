@extends('layouts.app')

@section('title', 'Verifications Queue - DirectStay Security Vault')

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Action & Title Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('host.dashboard') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Dashboard</span>
                </a>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <span class="text-xs font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">Security Clearance</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Guest Identity &amp; Payment Triage
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Audit GCash payment receipts, inspect guest government IDs, and dispatch official Urban Deca Homes gate passes
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('host.units.index') }}"
               class="px-4 py-2.5 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white/70 dark:bg-slate-800/70 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700 shadow-2xs backdrop-blur-md transition-all btn-press">
                Manage Units
            </a>
            <a href="{{ route('units.index') }}" target="_blank"
               class="px-4 py-2.5 rounded-2xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 transition-all btn-press inline-flex items-center gap-1.5">
                <span>View Guest Catalog</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- Quick Bento Summary Metrics Strip -->
    @php
        $readyCount = $bookings->filter(function($b) {
            $hasReceipt = $b->complianceDocuments->contains('document_type', 'payment_receipt');
            $idCount = $b->complianceDocuments->filter(fn($d) => str_starts_with($d->document_type, 'gov_id_'))->count();
            return $hasReceipt && $idCount >= $b->guest_count;
        })->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <!-- Metric 1: Pending Queue -->
        <div class="glass-surface-dense rounded-3xl p-5 border border-white/60 dark:border-white/10 shadow-sm relative overflow-hidden">
            <div class="ambient-glow w-28 h-28 bg-blue-500/10 -top-8 -right-8 glow-animate"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400">In Verification Queue</span>
                <span class="w-2 h-2 rounded-full bg-blue-500 status-beacon"></span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white tabular-nums">{{ $bookings->total() }}</span>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ Str::plural('booking', $bookings->total()) }} pending</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Awaiting host compliance approval or guest upload</p>
        </div>

        <!-- Metric 2: Ready for 1-Click Pass Approval -->
        <div class="glass-surface-dense rounded-3xl p-5 border border-white/60 dark:border-white/10 shadow-sm relative overflow-hidden">
            <div class="ambient-glow w-28 h-28 bg-emerald-500/10 -top-8 -right-8 glow-animate"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Ready to Approve</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                    All Files In
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $readyCount }}</span>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">ready for dispatch</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Both GCash receipt and IDs uploaded and complete</p>
        </div>

        <!-- Metric 3: Security Deposit Escrow Protection -->
        <div class="glass-surface-dense rounded-3xl p-5 border border-white/60 dark:border-white/10 shadow-sm relative overflow-hidden">
            <div class="ambient-glow w-28 h-28 bg-indigo-500/10 -top-8 -right-8 glow-animate"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400">Deposit Protection</span>
                <span class="text-xs font-extrabold text-indigo-600 dark:text-indigo-400">₱1,000 / unit</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white tabular-nums font-mono">
                    ₱{{ number_format($bookings->total() * 1000, 0) }}
                </span>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">refundable hold</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Advance deposits logged for Urban Deca security clearance</p>
        </div>
    </div>

    <!-- Main Table Container (Frosted Glassmorphism) -->
    <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 overflow-hidden shadow-xl backdrop-blur-2xl relative">
        <div class="ambient-glow w-96 h-96 bg-blue-500/10 -top-24 -right-24 glow-animate"></div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="glass-table-header uppercase font-bold text-[11px] tracking-wider border-b border-slate-200/70 dark:border-slate-800 text-slate-500 dark:text-slate-400 select-none">
                    <tr>
                        <th class="py-4 px-6">Booking Code</th>
                        <th class="py-4 px-6">Unit &amp; Tower</th>
                        <th class="py-4 px-6">Guest Profile</th>
                        <th class="py-4 px-6">Stay Dates</th>
                        <th class="py-4 px-6">Deposit (₱1,000)</th>
                        <th class="py-4 px-6">Identity Vault</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800/80 font-medium">
                    @forelse($bookings as $b)
                        @php
                            $hasReceipt = $b->complianceDocuments->contains('document_type', 'payment_receipt');
                            $governmentIdCount = $b->complianceDocuments->filter(fn ($document) => str_starts_with($document->document_type, 'gov_id_'))->count();
                            $hasAllIds = $governmentIdCount >= $b->guest_count;
                            $hasSelfie = $b->complianceDocuments->contains('document_type', 'selfie');
                            $hasWaiver = (bool) $b->waiver_accepted_at;
                            $isAllComplete = $hasReceipt && $hasAllIds && $hasSelfie && $hasWaiver;
                        @endphp
                        <tr class="glass-table-row hover:bg-white/60 dark:hover:bg-slate-800/50 transition-colors">
                            <!-- Booking Reference -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-black text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/80 px-2.5 py-1 rounded-lg border border-blue-200 dark:border-blue-800">
                                        {{ $b->booking_code }}
                                    </span>
                                    @if($isAllComplete)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 status-beacon" title="All documents verified"></span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block">
                                    Submitted {{ $b->created_at->diffForHumans() }}
                                </span>
                            </td>

                            <!-- Building & Unit -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-slate-900 dark:bg-slate-800 text-white font-extrabold text-[10px] flex items-center justify-center shrink-0">
                                        {{ $b->unit->building?->code ?? 'UDH' }}
                                    </span>
                                    <div>
                                        <span class="font-black text-slate-900 dark:text-white block">
                                            Unit {{ $b->unit->unit_number }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500 truncate block max-w-[160px]">
                                            {{ $b->unit->title }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Guest Profile -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-2xs shrink-0">
                                        {{ strtoupper(substr($b->guest_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white block">{{ $b->guest_name }}</span>
                                        <span class="text-slate-400 dark:text-slate-500 block text-[11px] font-mono">{{ $b->guest_phone ?? $b->guest_email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Dates & Duration -->
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-900 dark:text-slate-200">
                                    {{ $b->check_in_date->format('M d') }} &rarr; {{ $b->check_out_date->format('M d, Y') }}
                                </div>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500 block">
                                    {{ $b->nights_count }} Night(s) &bull; {{ $b->guest_count }} {{ Str::plural('Guest', $b->guest_count) }}
                                </span>
                            </td>

                            <!-- GCash Deposit (₱1,000) -->
                            <td class="py-4 px-6">
                                @if($hasReceipt)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        <span>Receipt Uploaded</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Awaiting GCash</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Vault Documents Checklist Chips -->
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <!-- Government IDs Chip -->
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $hasAllIds ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                        IDs {{ $governmentIdCount }}/{{ $b->guest_count }}
                                    </span>

                                    <!-- Selfie Chip -->
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $hasSelfie ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500' }}">
                                        Selfie {{ $hasSelfie ? '✓' : '—' }}
                                    </span>

                                    <!-- Waiver Chip -->
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $hasWaiver ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500' }}">
                                        Waiver {{ $hasWaiver ? '✓' : '—' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('host.verifications.show', $b) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-sm shadow-blue-500/25 hover:shadow-md transition-all btn-press">
                                    <span>Triage &amp; Review</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="max-w-md mx-auto">
                                    <div class="w-16 h-16 rounded-3xl bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner border border-blue-100 dark:border-slate-700">
                                        🛡️
                                    </div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white mb-1">Queue is Clear!</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">
                                        No reservations are currently awaiting compliance review. All guest gate passes have been audited and issued.
                                    </p>
                                    <a href="{{ route('host.dashboard') }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-2xs transition-all">
                                        &larr; Back to Host Dashboard
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-slate-200/70 dark:border-slate-800 glass-table-header">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
