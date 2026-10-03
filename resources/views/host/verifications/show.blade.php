@extends('layouts.app')

@section('title', 'Review Verification: ' . $booking->booking_code . ' - DirectStay')

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('host.verifications.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
                    &larr; Verifications Queue
                </a>
                <span class="text-slate-300">|</span>
                <span class="text-xs font-mono font-bold text-blue-600">{{ $booking->booking_code }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Guest Identity & Payment Verification
            </h1>
            <p class="text-xs text-slate-500">
                {{ $building->name }} &bull; {{ $unit->unit_number }} &bull; {{ $building->admin_email }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($booking->status === 'confirmed' || $booking->status === 'checked_in')
                <span class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-200/60 backdrop-blur-sm flex items-center gap-1.5 shadow-sm shadow-emerald-200/30">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Gate Pass Confirmed & Dispatched
                </span>
                <a href="{{ route('compliance.downloadGatePass', $booking->booking_code) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-600/25 transition-all hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-1.5 btn-press">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Gate Pass (Guest Form)
                </a>
                <a href="{{ route('compliance.downloadRentalAgreement', $booking->booking_code) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 glass-surface hover:bg-white/60 transition-all flex items-center gap-1.5 btn-press">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Rental Agreement (Contract)
                </a>
                <a href="{{ route('host.checkouts.show', $booking) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-800 glass-surface hover:bg-white/60 transition-all btn-press">
                    Checkout Inspection &rarr;
                </a>
            @else
                <form action="{{ route('host.verifications.approve', $booking) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-lg shadow-emerald-600/25 transition-all hover:shadow-xl hover:-translate-y-0.5 flex items-center gap-2 btn-press cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve & Issue Gate Pass PDF</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column: Guest Summary & Stay Details -->
        <div class="lg:col-span-5 space-y-6 stagger-enter">

            <!-- Reservation Overview Card — Frosted Glass -->
            <div class="glass-surface-dense rounded-3xl p-6 relative overflow-hidden">
                <div class="ambient-glow w-32 h-32 bg-blue-400/10 -top-8 -right-8 glow-animate"></div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 relative">Reservation Summary</h3>

                <div class="space-y-3 text-xs relative">
                    <div class="flex justify-between border-b border-white/30 pb-2">
                        <span class="text-slate-500">Lead Guest</span>
                        <span class="font-bold text-slate-900">{{ $booking->guest_name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-white/30 pb-2">
                        <span class="text-slate-500">Contact Number</span>
                        <span class="font-bold text-slate-900">{{ $booking->guest_phone }}</span>
                    </div>
                    <div class="flex justify-between border-b border-white/30 pb-2">
                        <span class="text-slate-500">Email Address</span>
                        <span class="font-medium text-slate-900">{{ $booking->guest_email }}</span>
                    </div>
                    <div class="flex justify-between border-b border-white/30 pb-2">
                        <span class="text-slate-500">Condo Unit</span>
                        <span class="font-bold text-slate-900">{{ $building->name }} - {{ $unit->unit_number }}</span>
                    </div>
                    <div class="flex justify-between border-b border-white/30 pb-2">
                        <span class="text-slate-500">Check-in</span>
                        <span class="font-bold text-slate-900">{{ $booking->check_in_date->format('M d, Y') }} (2:00 PM)</span>
                    </div>
                    <div class="flex justify-between border-b border-white/30 pb-2">
                        <span class="text-slate-500">Check-out</span>
                        <span class="font-bold text-slate-900">{{ $booking->check_out_date->format('M d, Y') }} (12:00 PM)</span>
                    </div>
                    <div class="flex justify-between border-b border-white/30 pb-2">
                        <span class="text-slate-500">Duration</span>
                        <span class="font-medium text-slate-900">{{ $booking->nights_count }} Night(s) &bull; {{ $booking->guest_count }} Guest(s)</span>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="mt-6 pt-4 border-t border-white/30 space-y-2 text-xs relative">
                    <div class="flex justify-between text-slate-600">
                        <span>Room Rate ({{ $booking->nights_count }}x):</span>
                        <span class="font-medium tabular-nums">₱{{ number_format($booking->base_amount, 2) }}</span>
                    </div>
                    @if($booking->add_ons_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Selected Add-ons:</span>
                            <span class="font-medium tabular-nums">₱{{ number_format($booking->add_ons_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-blue-600 font-semibold">
                        <span>DirectStay 5% Platform Fee:</span>
                        <span class="tabular-nums">₱{{ number_format($booking->platform_fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-800 font-semibold border-t border-white/30 pt-2">
                        <span>Advance Security Deposit:</span>
                        <span class="font-bold tabular-nums">₱{{ number_format($booking->advance_deposit_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-extrabold text-slate-900 border-t border-white/40 pt-2">
                        <span>Total Checkout Due:</span>
                        <span class="text-blue-600 tabular-nums font-mono">₱{{ number_format($booking->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Building PDF Template Details -->
            <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-200/50 backdrop-blur-sm text-xs">
                <div class="font-bold text-blue-950 mb-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    Building Pass Template Integration
                </div>
                <div class="text-blue-900 leading-relaxed">
                    Building: <strong>{{ $building->name }}</strong> ({{ $building->code }})<br>
                    Template: <code>{{ $building->gate_pass_template }}</code><br>
                    Dispatched on Approval: <strong>{{ $booking->guest_email }}</strong> &amp; <strong>{{ $building->admin_email }}</strong>
                </div>
            </div>

            <!-- Rejection Box -->
            @if($booking->status !== 'confirmed' && $booking->status !== 'checked_out')
                <div class="glass-surface rounded-3xl p-6 border-rose-200/50 relative overflow-hidden" style="border-color: rgb(254 205 211 / 0.5);">
                    <div class="ambient-glow w-24 h-24 bg-rose-400/10 -top-4 -right-4 glow-animate"></div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600 mb-2 relative">Cancel / Reject Booking</h4>
                    <p class="text-xs text-slate-500 mb-3 relative">If payment or ID fails compliance criteria, mark as rejected:</p>
                    <form action="{{ route('host.verifications.reject', $booking) }}" method="POST" class="space-y-3 relative">
                        @csrf
                        <textarea name="reason" rows="2" placeholder="State reason (e.g. invalid receipt, illegible ID)..." required
                                  class="w-full text-xs p-2.5 rounded-xl border border-white/40 bg-white/40 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white/60 transition-all"></textarea>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50/70 hover:bg-rose-100/80 border border-rose-200/60 backdrop-blur-sm transition-colors btn-press">
                            Reject Reservation
                        </button>
                    </form>
                </div>
            @endif

        </div>

        <!-- Right Column: Compliance Document Previews (Vault) -->
        <div class="lg:col-span-7 space-y-6 stagger-enter">

            <!-- GCash Payment Proof Card — Frosted Glass -->
            <div class="glass-surface rounded-3xl p-6 relative overflow-hidden">
                <div class="ambient-glow w-36 h-36 bg-emerald-400/10 -bottom-10 -left-10 glow-animate"></div>
                <div class="flex items-center justify-between mb-4 relative">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-emerald-100/80 text-emerald-700 flex items-center justify-center font-bold text-xs shadow-sm shadow-emerald-200/40 backdrop-blur-sm">₱</span>
                        Advance Deposit Payment Proof (₱1,000.00)
                    </h3>
                    @if($paymentDoc)
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-100/80 text-emerald-800 backdrop-blur-sm border border-emerald-200/50">
                            {{ $paymentDoc->notes ?? 'GCash Receipt' }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-amber-100/80 text-amber-800 backdrop-blur-sm border border-amber-200/50">Missing</span>
                    @endif
                </div>

                @if($paymentDoc)
                    <div class="bg-white/30 p-4 rounded-2xl border border-white/40 backdrop-blur-sm text-center relative">
                        <img src="{{ route('host.documents.stream', $paymentDoc) }}"
                             alt="Payment Proof"
                             class="max-h-80 mx-auto rounded-xl border border-white/40 shadow-lg shadow-slate-200/30 object-contain">
                        <div class="mt-2 text-[11px] text-slate-500">
                            Uploaded: {{ $paymentDoc->created_at->format('M d, Y h:i A') }} &bull; {{ $paymentDoc->original_filename }}
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center bg-white/25 rounded-2xl border border-dashed border-white/40 text-slate-400 text-xs backdrop-blur-sm">
                        Guest has not yet uploaded proof of advance deposit.
                    </div>
                @endif
            </div>

            <!-- Identity Verification Documents Card — Frosted Glass -->
            <div class="glass-surface rounded-3xl p-6 relative overflow-hidden"
                 x-data="{ inspectionModalOpen: false, activeIdSrc: '{{ $governmentIds->first() ? route('host.documents.stream', $governmentIds->first()) : '' }}', selfieSrc: '{{ $selfieDoc ? route('host.documents.stream', $selfieDoc) : '' }}' }">
                <div class="ambient-glow w-40 h-40 bg-blue-400/10 -top-10 -right-10 glow-animate blob-animate"></div>

                <div class="flex items-center justify-between mb-4 relative">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-blue-100/80 text-blue-700 flex items-center justify-center font-black text-xs shadow-sm shadow-blue-200/40 backdrop-blur-sm">ID</span>
                        Occupant IDs &amp; Lead Guest Selfie
                    </h3>
                    <div class="flex items-center gap-2">
                        @if($governmentIds->count() > 0 && $selfieDoc)
                            <button type="button" @click="inspectionModalOpen = true"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-700 bg-blue-50/70 hover:bg-blue-100/80 border border-blue-200/60 backdrop-blur-sm transition-all btn-press shadow-sm shadow-blue-200/30 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                <span>Inspect ID vs Selfie Lens</span>
                            </button>
                        @endif
                        @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100/80 text-emerald-800 backdrop-blur-sm border border-emerald-200/50">
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
                        <div class="p-3 bg-white/30 rounded-2xl border border-white/40 backdrop-blur-sm text-center group hover:bg-white/45 transition-all duration-300">
                            <div class="text-xs font-bold text-slate-700 mb-2 truncate">ID: {{ $occupant }}</div>
                            @if($governmentId)
                                <div class="relative overflow-hidden rounded-xl bg-slate-900/5">
                                    <img src="{{ route('host.documents.stream', $governmentId) }}" alt="Government ID for {{ $occupant }}"
                                         class="max-h-56 mx-auto rounded-xl border border-white/30 shadow-lg shadow-slate-200/30 object-contain group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="mt-2 text-[10px] text-slate-400 truncate font-mono">{{ $governmentId->original_filename }}</div>
                            @else
                                <div class="h-40 flex items-center justify-center text-xs text-slate-400 border border-dashed border-white/40 rounded-xl bg-white/15 backdrop-blur-sm">No ID uploaded</div>
                            @endif
                        </div>
                    @endfor

                    <!-- Selfie -->
                    <div class="p-3 bg-white/30 rounded-2xl border border-white/40 backdrop-blur-sm text-center group hover:bg-white/45 transition-all duration-300">
                        <div class="text-xs font-bold text-slate-700 mb-2">Live Verification Selfie</div>
                        @if($selfieDoc)
                            <div class="relative overflow-hidden rounded-xl bg-slate-900/5">
                                <img src="{{ route('host.documents.stream', $selfieDoc) }}"
                                     alt="Verification Selfie"
                                     class="max-h-56 mx-auto rounded-xl border border-white/30 shadow-lg shadow-slate-200/30 object-contain group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="mt-2 text-[10px] text-slate-400 truncate font-mono">{{ $selfieDoc->original_filename }}</div>
                        @else
                            <div class="h-40 flex items-center justify-center text-xs text-slate-400 border border-dashed border-white/40 rounded-xl bg-white/15 backdrop-blur-sm">
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
                        <div class="ambient-glow w-40 h-40 bg-indigo-400/10 -bottom-12 -right-12 glow-animate" style="animation-delay: 2s;"></div>

                        <div class="flex items-center justify-between border-b border-white/30 pb-4 mb-6 relative">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 bg-blue-50/70 px-2.5 py-0.5 rounded-full border border-blue-200/50 backdrop-blur-sm">Forensic Identity Lens</span>
                                <h3 class="text-xl font-black text-slate-900 mt-1">Side-by-Side ID vs. Selfie Comparator</h3>
                                <p class="text-xs text-slate-500">Hover your cursor over either photograph to activate high-magnification zoom inspection</p>
                            </div>
                            <button type="button" @click="inspectionModalOpen = false"
                                    class="w-9 h-9 rounded-full bg-white/50 hover:bg-white/70 backdrop-blur-sm text-slate-600 flex items-center justify-center transition-all btn-press border border-white/40 shadow-sm">
                                ✕
                            </button>
                        </div>

                        <!-- Side by Side Comparator Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative">
                            <!-- Left: Government ID Lens -->
                            <div class="p-4 bg-white/30 rounded-2xl border border-white/40 backdrop-blur-sm"
                                 x-data="{ zoom: false, x: 50, y: 50 }"
                                 @mousemove="
                                    let rect = $el.getBoundingClientRect();
                                    x = ((($event.clientX - rect.left) / rect.width) * 100).toFixed(1);
                                    y = ((($event.clientY - rect.top) / rect.height) * 100).toFixed(1);
                                 "
                                 @mouseenter="zoom = true"
                                 @mouseleave="zoom = false">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-extrabold text-slate-900">Government Photo ID</span>
                                    <span class="text-[10px] text-blue-600 font-semibold px-2 py-0.5 rounded-full bg-blue-50/60 border border-blue-100/40" x-text="zoom ? '🔍 Magnifier 2.5x Active' : 'Hover to Zoom'"></span>
                                </div>
                                <div class="lens-viewport h-80 rounded-xl bg-slate-950/80 flex items-center justify-center border border-white/20 overflow-hidden">
                                    <img :src="activeIdSrc" alt="Government ID"
                                         class="lens-img max-h-full max-w-full object-contain"
                                         :style="zoom ? `transform: scale(2.5); transform-origin: ${x}% ${y}%;` : 'transform: scale(1);'">
                                </div>
                            </div>

                            <!-- Right: Verification Selfie Lens -->
                            <div class="p-4 bg-white/30 rounded-2xl border border-white/40 backdrop-blur-sm"
                                 x-data="{ zoom: false, x: 50, y: 50 }"
                                 @mousemove="
                                    let rect = $el.getBoundingClientRect();
                                    x = ((($event.clientX - rect.left) / rect.width) * 100).toFixed(1);
                                    y = ((($event.clientY - rect.top) / rect.height) * 100).toFixed(1);
                                 "
                                 @mouseenter="zoom = true"
                                 @mouseleave="zoom = false">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-extrabold text-slate-900">Live Verification Selfie</span>
                                    <span class="text-[10px] text-blue-600 font-semibold px-2 py-0.5 rounded-full bg-blue-50/60 border border-blue-100/40" x-text="zoom ? '🔍 Magnifier 2.5x Active' : 'Hover to Zoom'"></span>
                                </div>
                                <div class="lens-viewport h-80 rounded-xl bg-slate-950/80 flex items-center justify-center border border-white/20 overflow-hidden">
                                    <img :src="selfieSrc" alt="Live Selfie"
                                         class="lens-img max-h-full max-w-full object-contain"
                                         :style="zoom ? `transform: scale(2.5); transform-origin: ${x}% ${y}%;` : 'transform: scale(1);'">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-white/30 flex items-center justify-between relative">
                            <span class="text-xs text-slate-500">Lead Guest: <strong>{{ $booking->guest_name }}</strong></span>
                            <button type="button" @click="inspectionModalOpen = false"
                                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-white/50 hover:bg-white/70 backdrop-blur-sm border border-white/40 transition-all btn-press">
                                Done Inspecting
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Companion Roster — Frosted Glass -->
            <div class="glass-surface rounded-3xl p-6 relative overflow-hidden">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 relative">Registered Occupants Roster</h3>
                <div class="space-y-2 text-xs relative">
                    <div class="p-2.5 bg-white/35 rounded-xl border border-white/40 backdrop-blur-sm flex items-center justify-between font-medium">
                        <span>1. <strong>{{ $booking->guest_name }}</strong> (Lead Guest)</span>
                        <span class="text-emerald-600 font-semibold">Primary Contact</span>
                    </div>
                    @if(!empty($booking->guest_roster) && is_array($booking->guest_roster))
                        @foreach($booking->guest_roster as $idx => $companion)
                            @if(!empty($companion['name']))
                                <div class="p-2.5 bg-white/30 rounded-xl border border-white/35 backdrop-blur-sm flex items-center justify-between">
                                    <span>{{ $idx + 2 }}. <strong>{{ $companion['name'] }}</strong></span>
                                    <span class="text-slate-500">{{ $companion['relationship'] ?? 'Companion' }}</span>
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
