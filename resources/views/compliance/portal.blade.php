@extends('layouts.app')

@section('title', 'Security Compliance & ID Portal - ' . $booking->booking_code)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header & Status Tracker -->
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-6 mb-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    DirectStay Security Compliance Vault
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                    Guest Compliance & Document Portal
                </h1>
                <p class="text-xs text-slate-500">
                    Booking Ref: <strong class="text-slate-800 font-mono">{{ $booking->booking_code }}</strong> &bull;
                    {{ $building->name }} ({{ $unit->unit_number }})
                </p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block mb-1">Reservation Status</span>
                @if($booking->status === 'confirmed')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Gate Pass Cleared ✓
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        Pending Verification
                    </span>
                @endif
            </div>
        </div>

        <!-- Compliance Stepper Indicator -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-semibold">
            <!-- Step 1 -->
            <div class="p-4 rounded-2xl border {{ $paymentDoc ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs {{ $paymentDoc ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }}">
                        {{ $paymentDoc ? '✓' : '1' }}
                    </span>
                    <span>Deposit Proof</span>
                </div>
                <p class="text-[11px] {{ $paymentDoc ? 'text-emerald-700' : 'text-slate-400' }}">
                    {{ $paymentDoc ? 'GCash Receipt Uploaded' : 'Upload ₱1,000 Advance Deposit' }}
                </p>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-2xl border {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }}">
                        {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? '✓' : '2' }}
                    </span>
                    <span>ID & Selfie Verification</span>
                </div>
                <p class="text-[11px] {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'text-emerald-700' : 'text-slate-400' }}">
                    {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'All IDs & Selfie Secure in Vault' : 'Upload an ID for every guest and the lead guest selfie' }}
                </p>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-2xl border {{ $booking->waiver_accepted_at ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs {{ $booking->waiver_accepted_at ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }}">
                        {{ $booking->waiver_accepted_at ? '✓' : '3' }}
                    </span>
                    <span>Rental Contract & Roster</span>
                </div>
                <p class="text-[11px] {{ $booking->waiver_accepted_at ? 'text-emerald-700' : 'text-slate-400' }}">
                    {{ $booking->waiver_accepted_at ? 'Contract Signed & Roster Logged' : 'Sign Rental Agreement & Roster' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">

        <!-- Form 1: Payment Upload -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">1</span>
                        Advance Deposit Payment
                    </h2>
                    @if($paymentDoc)
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">Uploaded</span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Please transfer the mandatory <strong>₱{{ number_format($booking->advance_deposit_amount, 2) }} Advance Deposit</strong> via GCash or Online Bank Transfer and upload your confirmation screenshot:
                </p>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs mb-4">
                    <div class="font-bold text-slate-800 mb-1">Host GCash Transfer Details:</div>
                    <div class="text-slate-600">Account Name: <strong>{{ $booking->unit->host->name ?? 'Aurelio J. Budol Sr. (A-F Staycation)' }}</strong></div>
                    <div class="text-slate-600">GCash Mobile: <strong>{{ $booking->unit->host->phone ?? '09478847463' }}</strong></div>
                    <div class="text-slate-600">Amount: <strong>₱{{ number_format($booking->advance_deposit_amount, 2) }}</strong></div>
                </div>

                @if($paymentDoc)
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-xs text-emerald-800 flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Receipt submitted: <strong>{{ $paymentDoc->original_filename }}</strong> ({{ $paymentDoc->notes ?? 'GCash Upload' }})</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('compliance.payment', ['bookingCode' => $booking->booking_code]) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-3 border-t border-slate-100">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">GCash Reference No. (Optional)</label>
                    <input type="text" name="reference_number" placeholder="e.g. 1002 9384 1928"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Proof of Payment Screenshot (JPEG, PNG)</label>
                    <input type="file" name="payment_receipt" accept="image/*" required
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors">
                    {{ $paymentDoc ? 'Re-upload Payment Screenshot' : 'Upload Payment Receipt' }}
                </button>
            </form>
        </div>

        <!-- Form 2: Identity & Selfie Verification -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">2</span>
                        Identity Verification
                    </h2>
                    @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">Secured</span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Building Administration ({{ $building->code }}) requires one valid government photo ID for every registered occupant. The lead guest must also provide a live verification selfie before an entrance gate pass can be issued.
                </p>

                <div class="p-3 bg-blue-50 rounded-2xl border border-blue-100 text-[11px] text-blue-900 space-y-1 mb-4">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Private Storage Guarantee
                    </div>
                    <div>Your identity files are encrypted in Laravel's non-public vault (<code>storage/app/private</code>) and only accessible to authorized building security.</div>
                </div>

                @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-xs text-emerald-800 space-y-1 mb-4">
                        <div>✓ Government IDs: <strong>{{ $governmentIds->count() }} of {{ $booking->guest_count }}</strong> uploaded</div>
                        <div>✓ Selfie: <strong>{{ $selfieDoc->original_filename }}</strong></div>
                    </div>
                @endif
            </div>

            <form action="{{ route('compliance.identity', ['bookingCode' => $booking->booking_code]) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-3 border-t border-slate-100">
                @csrf
                <div class="space-y-3">
                    <p class="text-xs font-semibold text-slate-700">Government IDs — {{ $booking->guest_count }} required</p>
                    @for($occupantIndex = 0; $occupantIndex < $booking->guest_count; $occupantIndex++)
                        @php
                            $occupant = $occupantIndex === 0
                                ? $booking->guest_name.' (Lead Guest)'
                                : ($booking->guest_roster[$occupantIndex - 1]['name'] ?? 'Guest '.($occupantIndex + 1));
                        @endphp
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Guest {{ $occupantIndex + 1 }}: {{ $occupant }}</label>
                            <input type="file" name="gov_ids[{{ $occupantIndex }}]" accept="image/*" required class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                    @endfor
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Real-time Verification Selfie</label>
                    <input type="file" name="selfie" accept="image/*" capture="user" required
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors">
                    {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'Replace All IDs & Selfie' : 'Upload All IDs & Selfie' }}
                </button>
            </form>
        </div>

    </div>

    <!-- Form 3: Staycation Rental Agreement & Companion Roster -->
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">3</span>
                    A-F Staycation Rental Agreement & Guest Roster
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Official lease contract for {{ $unit->unit_number }} between Tenant and Landlord (Ferlyn Miranda & Aurelio J. Budol Sr.)
                </p>
            </div>
            @if($booking->waiver_accepted_at)
                <span class="px-2.5 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-800">Contract Signed</span>
            @endif
        </div>

        <form action="{{ route('compliance.waiver', ['bookingCode' => $booking->booking_code]) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Rental Agreement Summary Box -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 space-y-3">
                <div class="flex items-center justify-between font-bold text-slate-900 border-b border-slate-200 pb-2">
                    <span class="uppercase tracking-wider text-[11px] text-blue-600">Contract Terms Summary (Sections I - XII)</span>
                    <span class="text-slate-500 font-mono text-[11px]">{{ $unit->title }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[11px] text-slate-700">
                    <div><strong>I. The Parties:</strong> Tenant {{ $booking->guest_name }} & Landlord Ferlyn Miranda / Aurelio J. Budol Sr.</div>
                    <div><strong>II. Premises:</strong> 2 Bedrooms, 1 Bathroom ({{ $unit->unit_number }})</div>
                    <div><strong>III. Lease Term:</strong> {{ $booking->check_in_date->format('M d, Y') }} (2 PM) &rarr; {{ $booking->check_out_date->format('M d, Y') }} (12 NN)</div>
                    <div><strong>IV. Occupants:</strong> Total {{ $booking->guest_count }} Authorized Guests/Visitors</div>
                    <div><strong>V. Rent:</strong> ₱1,000 Advance Deposit (Non-refundable upon cancellation; no rescheduling)</div>
                    <div><strong>VI. Security Deposit:</strong> ₱1,000 for faithful performance, returned less itemized deductions</div>
                    <div><strong>VII. Smoking Policy:</strong> Strictly Prohibited inside unit (Violation Fine: ₱2,000.00)</div>
                    <div><strong>VIII. Pets:</strong> Not allowed within premises (except tropical fishes)</div>
                    <div><strong>IX. Cleanliness (CLAYGO):</strong> Dispose of trash at back of bldg. Unthrown trash fine: ₱500.00</div>
                    <div><strong>X. Key / Elevator RFID Pass:</strong> ₱500.00 replacement fee each if lost</div>
                </div>
            </div>

            <!-- Companion Roster Inputs -->
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-2">
                    Accompanying Guests Roster (Max {{ $unit->max_guests }} Total Occupants):
                </label>
                <p class="text-[11px] text-slate-400 mb-3">
                    The lead guest, <strong>{{ $booking->guest_name }}</strong>, is automatically included. Add every other person who will stay in the unit. This list is required by building security and is copied to the gate pass; it is not a list of account users.
                </p>

                <div class="space-y-2">
                    @for($i = 1; $i < $booking->guest_count; $i++)
                        @php
                            $existingCompanion = $booking->guest_roster[$i - 1] ?? null;
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Companion {{ $i + 1 }} Legal Name</label>
                                <input type="text" name="companion_names[]" value="{{ $existingCompanion['name'] ?? '' }}" placeholder="Full Name"
                                       class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Relationship / Designation</label>
                                <input type="text" name="companion_relationships[]" value="{{ $existingCompanion['relationship'] ?? 'Companion' }}" placeholder="e.g. Spouse, Friend, Child"
                                       class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Digital Rental Agreement Checkbox -->
            <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200 flex items-start gap-3">
                <input type="checkbox" name="agree_rules" id="agreeRules" value="1" {{ $booking->waiver_accepted_at ? 'checked' : '' }} required
                       class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 mt-0.5">
                <label for="agreeRules" class="text-xs text-slate-700 leading-relaxed cursor-pointer">
                    I, <strong>{{ $booking->guest_name }}</strong>, certify that all provided details and government documents are authentic. I hereby digitally execute and agree to the <strong>A-F Staycation Rental Agreement</strong> and all house rules of <strong>{{ $building->name }}</strong>, and acknowledge that infractions (such as ₱500 unthrown garbage fee or ₱500 lost key fee) will be deducted from the ₱1,000 security deposit upon checkout inspection.
                </label>
            </div>

            <!-- Submit Final Step -->
            <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                <span>Sign Rental Agreement & Complete Submission</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
        </form>
    </div>

</div>
@endsection
