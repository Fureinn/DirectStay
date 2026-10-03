@extends('layouts.app')

@section('title', 'Review Verification: ' . $booking->booking_code . ' - DirectStay')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('host.verifications.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
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
                <span class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Gate Pass Confirmed & Dispatched
                </span>
                <a href="{{ route('compliance.downloadGatePass', $booking->booking_code) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Gate Pass (Guest Form)
                </a>
                <a href="{{ route('compliance.downloadRentalAgreement', $booking->booking_code) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 shadow-xs transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Rental Agreement (Contract)
                </a>
                <a href="{{ route('host.checkouts.show', $booking) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-800 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                    Checkout Inspection &rarr;
                </a>
            @else
                <form action="{{ route('host.verifications.approve', $booking) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/30 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve & Issue Gate Pass PDF</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column: Guest Summary & Stay Details -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Reservation Overview Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Reservation Summary</h3>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Lead Guest</span>
                        <span class="font-bold text-slate-900">{{ $booking->guest_name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Contact Number</span>
                        <span class="font-bold text-slate-900">{{ $booking->guest_phone }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Email Address</span>
                        <span class="font-medium text-slate-900">{{ $booking->guest_email }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Condo Unit</span>
                        <span class="font-bold text-slate-900">{{ $building->name }} - {{ $unit->unit_number }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Check-in</span>
                        <span class="font-bold text-slate-900">{{ $booking->check_in_date->format('M d, Y') }} (2:00 PM)</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Check-out</span>
                        <span class="font-bold text-slate-900">{{ $booking->check_out_date->format('M d, Y') }} (12:00 PM)</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-500">Duration</span>
                        <span class="font-medium text-slate-900">{{ $booking->nights_count }} Night(s) &bull; {{ $booking->guest_count }} Guest(s)</span>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="mt-6 pt-4 border-t border-slate-100 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Room Rate ({{ $booking->nights_count }}x):</span>
                        <span class="font-medium">₱{{ number_format($booking->base_amount, 2) }}</span>
                    </div>
                    @if($booking->add_ons_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Selected Add-ons:</span>
                            <span class="font-medium">₱{{ number_format($booking->add_ons_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-blue-600 font-semibold">
                        <span>DirectStay 5% Platform Fee:</span>
                        <span>₱{{ number_format($booking->platform_fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-800 font-semibold border-t border-slate-100 pt-2">
                        <span>Advance Security Deposit:</span>
                        <span class="font-bold">₱{{ number_format($booking->advance_deposit_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-extrabold text-slate-900 border-t border-slate-200 pt-2">
                        <span>Total Checkout Due:</span>
                        <span class="text-blue-600">₱{{ number_format($booking->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Building PDF Template Details -->
            <div class="p-5 rounded-2xl bg-blue-50/70 border border-blue-200 text-xs">
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
                <div class="bg-white rounded-3xl border border-rose-200 p-6 shadow-sm">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600 mb-2">Cancel / Reject Booking</h4>
                    <p class="text-xs text-slate-500 mb-3">If payment or ID fails compliance criteria, mark as rejected:</p>
                    <form action="{{ route('host.verifications.reject', $booking) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="reason" rows="2" placeholder="State reason (e.g. invalid receipt, illegible ID)..." required
                                  class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-rose-500"></textarea>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                            Reject Reservation
                        </button>
                    </form>
                </div>
            @endif

        </div>

        <!-- Right Column: Compliance Document Previews (Vault) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- GCash Payment Proof Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">₱</span>
                        Advance Deposit Payment Proof (₱1,000.00)
                    </h3>
                    @if($paymentDoc)
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                            {{ $paymentDoc->notes ?? 'GCash Receipt' }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">Missing</span>
                    @endif
                </div>

                @if($paymentDoc)
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-center">
                        <img src="{{ route('host.documents.stream', $paymentDoc) }}"
                             alt="Payment Proof"
                             class="max-h-80 mx-auto rounded-xl border border-slate-200 shadow-sm object-contain">
                        <div class="mt-2 text-[11px] text-slate-500">
                            Uploaded: {{ $paymentDoc->created_at->format('M d, Y h:i A') }} &bull; {{ $paymentDoc->original_filename }}
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                        Guest has not yet uploaded proof of advance deposit.
                    </div>
                @endif
            </div>

            <!-- Identity Verification Documents Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">ID</span>
                        Occupant IDs & Lead Guest Selfie
                    </h3>
                    @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                            Vault Verified
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @for($occupantIndex = 0; $occupantIndex < $booking->guest_count; $occupantIndex++)
                        @php
                            $occupant = $occupantIndex === 0
                                ? $booking->guest_name.' (Lead Guest)'
                                : ($booking->guest_roster[$occupantIndex - 1]['name'] ?? 'Guest '.($occupantIndex + 1));
                            $governmentId = $governmentIds->get($occupantIndex);
                        @endphp
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                            <div class="text-xs font-bold text-slate-700 mb-2">ID: {{ $occupant }}</div>
                            @if($governmentId)
                                <img src="{{ route('host.documents.stream', $governmentId) }}" alt="Government ID for {{ $occupant }}" class="max-h-56 mx-auto rounded-lg border border-slate-200 shadow-sm object-contain">
                                <div class="mt-2 text-[10px] text-slate-400 truncate">{{ $governmentId->original_filename }}</div>
                            @else
                                <div class="h-40 flex items-center justify-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-lg">No ID uploaded</div>
                            @endif
                        </div>
                    @endfor

                    <!-- Selfie -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                        <div class="text-xs font-bold text-slate-700 mb-2">Live Verification Selfie</div>
                        @if($selfieDoc)
                            <img src="{{ route('host.documents.stream', $selfieDoc) }}"
                                 alt="Verification Selfie"
                                 class="max-h-56 mx-auto rounded-lg border border-slate-200 shadow-sm object-contain">
                            <div class="mt-2 text-[10px] text-slate-400 truncate">{{ $selfieDoc->original_filename }}</div>
                        @else
                            <div class="h-40 flex items-center justify-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-lg">
                                No Selfie uploaded
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Companion Roster -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Registered Occupants Roster</h3>
                <div class="space-y-2 text-xs">
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between font-medium">
                        <span>1. <strong>{{ $booking->guest_name }}</strong> (Lead Guest)</span>
                        <span class="text-emerald-600 font-semibold">Primary Contact</span>
                    </div>
                    @if(!empty($booking->guest_roster) && is_array($booking->guest_roster))
                        @foreach($booking->guest_roster as $idx => $companion)
                            @if(!empty($companion['name']))
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
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
