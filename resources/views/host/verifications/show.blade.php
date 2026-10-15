@extends('layouts.app')

@section('title', 'Review Verification: ' . $booking->booking_code . ' - DirectStay')

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('host.verifications.index') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Verifications Queue</span>
                </a>
                <span class="text-slate-300 dark:text-slate-700">|</span>
                <span class="text-xs font-mono font-black text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/80 px-2 py-0.5 rounded-md border border-blue-200 dark:border-blue-800">
                    {{ $booking->booking_code }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Guest Identity &amp; Payment Verification
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $building->name }} &bull; {{ $unit->unit_number }} &bull; Gate Pass Routing: <span class="font-mono text-slate-700 dark:text-slate-300">{{ $building->admin_email }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if($booking->status === 'confirmed' || $booking->status === 'checked_in')
                <span class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100/90 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/80 backdrop-blur-sm flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Gate Pass Confirmed</span>
                </span>
                <a href="{{ route('compliance.downloadGatePass', $booking->booking_code) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-sm shadow-blue-500/25 transition-all btn-press inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Gate Pass PDF</span>
                </a>
                <a href="{{ route('compliance.downloadRentalAgreement', $booking->booking_code) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white/70 dark:bg-slate-800/70 hover:bg-white dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-all inline-flex items-center gap-1.5 btn-press">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Rental Agreement</span>
                </a>
                <a href="{{ route('host.checkouts.show', $booking) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 bg-white/70 dark:bg-slate-800/70 hover:bg-white dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-all btn-press">
                    Checkout Inspection &rarr;
                </a>
            @else
                <form action="{{ route('host.verifications.approve', $booking) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-black text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25 transition-all hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-2 btn-press cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve &amp; Issue Gate Pass PDF</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column: Guest Summary & Stay Details -->
        <div class="lg:col-span-5 space-y-6 stagger-enter">

            <!-- Reservation Overview Card — Frosted Glass -->
            <div class="glass-surface-dense rounded-3xl p-6 sm:p-7 relative overflow-hidden border border-white/60 dark:border-white/10 shadow-lg">
                <div class="ambient-glow w-32 h-32 bg-blue-400/10 -top-8 -right-8 glow-animate"></div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-4 relative">Reservation Summary</h3>

                <div class="space-y-3 text-xs relative">
                    <div class="flex justify-between border-b border-slate-200/50 dark:border-slate-800/60 pb-2">
                        <span class="text-slate-500 dark:text-slate-400">Lead Guest</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $booking->guest_name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/50 dark:border-slate-800/60 pb-2">
                        <span class="text-slate-500 dark:text-slate-400">Contact Number</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-white">{{ $booking->guest_phone }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/50 dark:border-slate-800/60 pb-2">
                        <span class="text-slate-500 dark:text-slate-400">Email Address</span>
                        <span class="font-medium font-mono text-slate-900 dark:text-slate-200">{{ $booking->guest_email }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/50 dark:border-slate-800/60 pb-2">
                        <span class="text-slate-500 dark:text-slate-400">Condo Unit</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $building->name }} - {{ $unit->unit_number }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/50 dark:border-slate-800/60 pb-2">
                        <span class="text-slate-500 dark:text-slate-400">Check-in</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $booking->check_in_date->format('M d, Y') }} (2:00 PM)</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/50 dark:border-slate-800/60 pb-2">
                        <span class="text-slate-500 dark:text-slate-400">Check-out</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $booking->check_out_date->format('M d, Y') }} (12:00 PM)</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/50 dark:border-slate-800/60 pb-2">
                        <span class="text-slate-500 dark:text-slate-400">Duration</span>
                        <span class="font-medium text-slate-900 dark:text-white">{{ $booking->nights_count }} Night(s) &bull; {{ $booking->guest_count }} Guest(s)</span>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="mt-6 pt-4 border-t border-slate-200/60 dark:border-slate-800/60 space-y-2 text-xs relative">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Room Rate ({{ $booking->nights_count }}x):</span>
                        <span class="font-semibold tabular-nums text-slate-900 dark:text-white">₱{{ number_format($booking->base_amount, 2) }}</span>
                    </div>
                    @if($booking->add_ons_amount > 0)
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Selected Add-ons:</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">₱{{ number_format($booking->add_ons_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-blue-600 dark:text-blue-400 font-semibold">
                        <span>DirectStay 5% Platform Fee:</span>
                        <span class="tabular-nums font-mono">₱{{ number_format($booking->platform_fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-800 dark:text-slate-200 font-semibold border-t border-slate-200/50 dark:border-slate-800/60 pt-2">
                        <span>Advance Security Deposit:</span>
                        <span class="font-black tabular-nums font-mono text-emerald-600 dark:text-emerald-400">₱{{ number_format($booking->advance_deposit_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-black text-slate-900 dark:text-white border-t border-slate-200/70 dark:border-slate-700 pt-2">
                        <span>Total Checkout Due:</span>
                        <span class="text-blue-600 dark:text-blue-400 tabular-nums font-mono">₱{{ number_format($booking->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Building PDF Template Details -->
            <div class="p-5 rounded-3xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/70 dark:border-blue-900/40 backdrop-blur-md text-xs">
                <div class="font-black text-blue-950 dark:text-blue-200 mb-1.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    <span>Building Pass Template Integration</span>
                </div>
                <div class="text-blue-900 dark:text-blue-300 leading-relaxed space-y-1">
                    <div>Building: <strong>{{ $building->name }}</strong> (Tower {{ $building->code }})</div>
                    <div>Template: <code class="px-1.5 py-0.5 rounded bg-white/60 dark:bg-slate-900 text-blue-800 dark:text-blue-300 font-mono text-[11px]">{{ $building->gate_pass_template }}</code></div>
                    <div class="text-[11px] text-blue-700 dark:text-blue-400 pt-1 border-t border-blue-200/50 dark:border-blue-900/40">
                        Auto-dispatches on approval to <strong>{{ $booking->guest_email }}</strong> &amp; <strong>{{ $building->admin_email }}</strong>
                    </div>
                </div>
            </div>

            <!-- Rejection Box -->
            @if($booking->status !== 'confirmed' && $booking->status !== 'checked_out')
                <div class="glass-surface rounded-3xl p-6 border-rose-200/60 dark:border-rose-900/50 relative overflow-hidden bg-rose-50/30 dark:bg-rose-950/20">
                    <div class="ambient-glow w-24 h-24 bg-rose-400/10 -top-4 -right-4 glow-animate"></div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-2 relative">Cancel / Reject Booking</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 relative">If payment receipt is illegible or ID fails building compliance, notify guest with reason:</p>
                    <form action="{{ route('host.verifications.reject', $booking) }}" method="POST" class="space-y-3 relative">
                        @csrf
                        <textarea name="reason" rows="2" placeholder="State reason (e.g. invalid receipt, illegible ID, missing companion info)..." required
                                  class="w-full text-xs p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white/70 dark:bg-slate-900/70 text-slate-900 dark:text-white backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all"></textarea>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-100/80 dark:bg-rose-950/80 hover:bg-rose-200 dark:hover:bg-rose-900/80 border border-rose-300 dark:border-rose-800 backdrop-blur-sm transition-colors btn-press cursor-pointer">
                            Reject Reservation
                        </button>
                    </form>
                </div>
            @endif

        </div>

        <!-- Right Column: Compliance Document Previews (Vault) -->
        <div class="lg:col-span-7 space-y-6 stagger-enter">

            <!-- GCash Payment Proof Card — Frosted Glass -->
            <div class="glass-surface rounded-3xl p-6 sm:p-7 relative overflow-hidden border border-white/60 dark:border-white/10 shadow-lg">
                <div class="ambient-glow w-36 h-36 bg-emerald-400/10 -bottom-10 -left-10 glow-animate"></div>
                <div class="flex items-center justify-between mb-4 relative">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 flex items-center justify-center font-black text-xs shadow-xs">₱</span>
                        Advance Deposit Payment Proof (₱1,000.00)
                    </h3>
                    @if($paymentDoc)
                        <span class="px-2.5 py-1 rounded-xl text-[11px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            {{ $paymentDoc->notes ?? 'GCash Receipt' }}
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-xl text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            Missing
                        </span>
                    @endif
                </div>

                @if($paymentDoc)
                    <div class="bg-white/40 dark:bg-slate-900/60 p-4 rounded-2xl border border-white/60 dark:border-white/10 backdrop-blur-md text-center relative group">
                        <img src="{{ route('host.documents.stream', $paymentDoc) }}"
                             alt="Payment Proof"
                             class="max-h-80 mx-auto rounded-xl border border-white/40 dark:border-slate-700 shadow-md object-contain group-hover:scale-102 transition-transform duration-300">
                        <div class="mt-3 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-center gap-2">
                            <span>Uploaded: <strong>{{ $paymentDoc->created_at->format('M d, Y h:i A') }}</strong></span>
                            <span>&bull;</span>
                            <span class="font-mono text-[10px]">{{ $paymentDoc->original_filename }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center bg-white/30 dark:bg-slate-900/40 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 text-slate-400 dark:text-slate-500 text-xs backdrop-blur-sm">
                        Guest has not yet uploaded proof of advance deposit.
                    </div>
                @endif
            </div>

            <!-- Identity Verification Documents Card — Frosted Glass -->
            <div class="glass-surface rounded-3xl p-6 sm:p-7 relative overflow-hidden border border-white/60 dark:border-white/10 shadow-lg"
                 x-data="{ inspectionModalOpen: false, activeIdSrc: '{{ $governmentIds->first() ? route('host.documents.stream', $governmentIds->first()) : '' }}', selfieSrc: '{{ $selfieDoc ? route('host.documents.stream', $selfieDoc) : '' }}' }">
                <div class="ambient-glow w-40 h-40 bg-blue-400/10 -top-10 -right-10 glow-animate blob-animate"></div>

                <div class="flex items-center justify-between mb-4 relative">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 flex items-center justify-center font-black text-xs shadow-xs">ID</span>
                        Occupant IDs &amp; Lead Guest Selfie
                    </h3>
                    <div class="flex items-center gap-2">
                        @if($governmentIds->count() > 0 && $selfieDoc)
                            <button type="button" @click="inspectionModalOpen = true"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950 hover:bg-blue-100 dark:hover:bg-blue-900 border border-blue-200 dark:border-blue-800 transition-all btn-press shadow-xs cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                <span>Forensic Lens Comparator</span>
                            </button>
                        @endif
                        @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Vault Verified ✓
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 relative">
                    @for($occupantIndex = 0; $occupantIndex < $booking->guest_count; $occupantIndex++)
                        @php
                            $occupant = $occupantIndex === 0
                                ? $booking->guest_name.' (Lead Guest)'
                                : ($booking->guest_roster[$occupantIndex - 1]['name'] ?? 'Guest '.($occupantIndex + 1));
                            $governmentId = $governmentIds->get($occupantIndex);
                        @endphp
                        <div class="p-3.5 bg-white/40 dark:bg-slate-900/60 rounded-2xl border border-white/60 dark:border-white/10 backdrop-blur-sm text-center group hover:bg-white/60 dark:hover:bg-slate-900/80 transition-all duration-300">
                            <div class="text-xs font-black text-slate-800 dark:text-slate-200 mb-2 truncate">ID: {{ $occupant }}</div>
                            @if($governmentId)
                                <div class="relative overflow-hidden rounded-xl bg-slate-900/5 dark:bg-slate-950/40 cursor-pointer"
                                     @click="activeIdSrc = '{{ route('host.documents.stream', $governmentId) }}'; inspectionModalOpen = true">
                                    <img src="{{ route('host.documents.stream', $governmentId) }}" alt="Government ID for {{ $occupant }}"
                                         class="max-h-56 mx-auto rounded-xl border border-white/40 dark:border-slate-700 shadow-md object-contain group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-blue-600/0 group-hover:bg-blue-600/10 transition-colors flex items-center justify-center">
                                        <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900/80 text-white text-[10px] font-bold px-2.5 py-1 rounded-full backdrop-blur-md">
                                            🔍 Click to Inspect
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 text-[10px] text-slate-400 dark:text-slate-500 truncate font-mono">{{ $governmentId->original_filename }}</div>
                            @else
                                <div class="h-40 flex items-center justify-center text-xs text-slate-400 dark:text-slate-500 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl bg-white/20 dark:bg-slate-900/30">No ID uploaded</div>
                            @endif
                        </div>
                    @endfor

                    <!-- Selfie -->
                    <div class="p-3.5 bg-white/40 dark:bg-slate-900/60 rounded-2xl border border-white/60 dark:border-white/10 backdrop-blur-sm text-center group hover:bg-white/60 dark:hover:bg-slate-900/80 transition-all duration-300">
                        <div class="text-xs font-black text-slate-800 dark:text-slate-200 mb-2">Live Verification Selfie</div>
                        @if($selfieDoc)
                            <div class="relative overflow-hidden rounded-xl bg-slate-900/5 dark:bg-slate-950/40 cursor-pointer"
                                 @click="inspectionModalOpen = true">
                                <img src="{{ route('host.documents.stream', $selfieDoc) }}"
                                     alt="Verification Selfie"
                                     class="max-h-56 mx-auto rounded-xl border border-white/40 dark:border-slate-700 shadow-md object-contain group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-blue-600/0 group-hover:bg-blue-600/10 transition-colors flex items-center justify-center">
                                    <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900/80 text-white text-[10px] font-bold px-2.5 py-1 rounded-full backdrop-blur-md">
                                        🔍 Click to Inspect
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 text-[10px] text-slate-400 dark:text-slate-500 truncate font-mono">{{ $selfieDoc->original_filename }}</div>
                        @else
                            <div class="h-40 flex items-center justify-center text-xs text-slate-400 dark:text-slate-500 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl bg-white/20 dark:bg-slate-900/30">
                                No Selfie uploaded
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Side-by-Side Zoom Inspection Modal — Full Glassmorphism -->
                <div x-show="inspectionModalOpen" x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 overflow-y-auto glass-modal-overlay flex items-center justify-center p-4">
                    <div @click.away="inspectionModalOpen = false"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="glass-modal-box rounded-3xl w-full max-w-5xl overflow-hidden p-6 sm:p-8 relative">

                        <!-- Ambient glows inside modal -->
                        <div class="ambient-glow w-48 h-48 bg-blue-500/10 -top-16 -left-16 glow-animate"></div>
                        <div class="ambient-glow w-40 h-40 bg-indigo-400/10 -bottom-12 -right-12 glow-animate"></div>

                        <div class="flex items-center justify-between border-b border-slate-200/50 dark:border-slate-800/80 pb-4 mb-6 relative">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950 px-2.5 py-0.5 rounded-full border border-blue-200 dark:border-blue-800">
                                    Forensic Identity Lens
                                </span>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-1">Side-by-Side ID vs. Selfie Comparator</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Hover your cursor over either photograph to activate high-magnification 2.5x zoom inspection</p>
                            </div>
                            <button type="button" @click="inspectionModalOpen = false"
                                    class="w-9 h-9 rounded-full bg-white/70 dark:bg-slate-800/80 hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all btn-press border border-slate-200 dark:border-slate-700 shadow-sm cursor-pointer">
                                ✕
                            </button>
                        </div>

                        <!-- Side by Side Comparator Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative">
                            <!-- Left: Government ID Lens -->
                            <div class="p-4 bg-white/40 dark:bg-slate-900/60 rounded-2xl border border-white/60 dark:border-white/10 backdrop-blur-md"
                                 x-data="{ zoom: false, x: 50, y: 50 }"
                                 @mousemove="
                                    let rect = $el.getBoundingClientRect();
                                    x = ((($event.clientX - rect.left) / rect.width) * 100).toFixed(1);
                                    y = ((($event.clientY - rect.top) / rect.height) * 100).toFixed(1);
                                 "
                                 @mouseenter="zoom = true"
                                 @mouseleave="zoom = false">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-black text-slate-900 dark:text-white">Government Photo ID</span>
                                    <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800" x-text="zoom ? '🔍 Magnifier 2.5x Active' : 'Hover to Zoom'"></span>
                                </div>
                                <div class="lens-viewport h-80 rounded-xl bg-slate-950 flex items-center justify-center border border-white/10 overflow-hidden relative">
                                    <img :src="activeIdSrc" alt="Government ID"
                                         class="lens-img max-h-full max-w-full object-contain"
                                         :style="zoom ? `transform: scale(2.5); transform-origin: ${x}% ${y}%;` : 'transform: scale(1);'">
                                </div>
                            </div>

                            <!-- Right: Verification Selfie Lens -->
                            <div class="p-4 bg-white/40 dark:bg-slate-900/60 rounded-2xl border border-white/60 dark:border-white/10 backdrop-blur-md"
                                 x-data="{ zoom: false, x: 50, y: 50 }"
                                 @mousemove="
                                    let rect = $el.getBoundingClientRect();
                                    x = ((($event.clientX - rect.left) / rect.width) * 100).toFixed(1);
                                    y = ((($event.clientY - rect.top) / rect.height) * 100).toFixed(1);
                                 "
                                 @mouseenter="zoom = true"
                                 @mouseleave="zoom = false">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-black text-slate-900 dark:text-white">Live Verification Selfie</span>
                                    <span class="text-[10px] text-blue-600 dark:text-blue-400 font-bold px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800" x-text="zoom ? '🔍 Magnifier 2.5x Active' : 'Hover to Zoom'"></span>
                                </div>
                                <div class="lens-viewport h-80 rounded-xl bg-slate-950 flex items-center justify-center border border-white/10 overflow-hidden relative">
                                    <img :src="selfieSrc" alt="Live Selfie"
                                         class="lens-img max-h-full max-w-full object-contain"
                                         :style="zoom ? `transform: scale(2.5); transform-origin: ${x}% ${y}%;` : 'transform: scale(1);'">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-200/50 dark:border-slate-800/80 flex items-center justify-between relative">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Lead Guest: <strong class="text-slate-800 dark:text-white">{{ $booking->guest_name }}</strong></span>
                            <button type="button" @click="inspectionModalOpen = false"
                                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all btn-press cursor-pointer">
                                Done Inspecting
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Companion Roster — Frosted Glass -->
            <div class="glass-surface rounded-3xl p-6 sm:p-7 relative overflow-hidden border border-white/60 dark:border-white/10 shadow-lg">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-3 relative">Registered Occupants Roster</h3>
                <div class="space-y-2 text-xs relative">
                    <div class="p-3 bg-white/40 dark:bg-slate-900/60 rounded-xl border border-white/50 dark:border-white/10 backdrop-blur-sm flex items-center justify-between font-medium">
                        <span class="text-slate-900 dark:text-white">1. <strong>{{ $booking->guest_name }}</strong> (Lead Guest)</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">Primary Contact</span>
                    </div>
                    @if(!empty($booking->guest_roster) && is_array($booking->guest_roster))
                        @foreach($booking->guest_roster as $idx => $companion)
                            @if(!empty($companion['name']))
                                <div class="p-3 bg-white/40 dark:bg-slate-900/60 rounded-xl border border-white/50 dark:border-white/10 backdrop-blur-sm flex items-center justify-between">
                                    <span class="text-slate-900 dark:text-white">{{ $idx + 2 }}. <strong>{{ $companion['name'] }}</strong></span>
                                    <span class="text-slate-500 dark:text-slate-400">{{ $companion['relationship'] ?? 'Companion' }}</span>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
