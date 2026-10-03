@extends('layouts.app')

@section('title', 'Reservation Status - ' . $booking->booking_code)

@section('content')
<div class="page-enter max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="surface-card soft-panel rounded-3xl border p-8 text-center">

        @if($booking->status === 'confirmed' || $booking->status === 'checked_in')
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-2">
                Gate Pass Dispatched & Cleared
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">
                Your Staycation is Fully Confirmed!
            </h1>
            <p class="text-xs text-slate-500 max-w-lg mx-auto mb-8">
                Your payment and government ID have been validated by the host. Your official building move-in gate pass has been compiled and emailed to you and the lobby desk.
            </p>

            <!-- Download Official Building Documents (Gate Pass & Rental Agreement) -->
            <div class="p-6 bg-slate-50/80 rounded-2xl border border-slate-200 mb-8 max-w-xl mx-auto text-center">
                <div class="text-xs font-bold text-slate-900 mb-1">Official Documents Issued & Cleared</div>
                <p class="text-[11px] text-slate-500 mb-4">
                    Your reservation documents have been officially certified. You can download both the building gate pass (guest form) for security entry and your signed rental agreement contract:
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('compliance.downloadGatePass', ['bookingCode' => $booking->booking_code]) }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Download Gate Pass (Guest Form)</span>
                    </a>

                    <a href="{{ route('compliance.downloadRentalAgreement', ['bookingCode' => $booking->booking_code]) }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs text-slate-800 bg-white border border-slate-300 hover:bg-slate-50 shadow-sm transition-all">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Download Rental Agreement (Contract)</span>
                    </a>
                </div>

                <p class="text-[11px] text-slate-400 mt-3">
                    Please present the Gate Pass PDF along with original Gov IDs to the lobby security upon arrival.
                </p>
            </div>

        @else
            <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 animate-pulse">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 mb-2">
                Documents Under Review
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">
                Verification in Progress
            </h1>
            <p class="text-xs text-slate-500 max-w-lg mx-auto mb-8">
                Your reservation details, payment proof, and identification are queued for host verification. Once verified, your building gate pass will be automatically generated and emailed.
            </p>
        @endif

        <!-- Reservation Details Summary Box -->
        <div class="text-left bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-3 text-xs mb-6">
            <div class="flex justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-400">Booking Reference</span>
                <span class="font-mono font-bold text-slate-900">{{ $booking->booking_code }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-400">Condominium Unit</span>
                <span class="font-bold text-slate-900">{{ $building->name }} - {{ $unit->unit_number }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-400">Check-in</span>
                <span class="font-medium text-slate-900">{{ $booking->check_in_date->format('l, F d, Y') }} (2:00 PM)</span>
            </div>
            <div class="flex justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-400">Check-out</span>
                <span class="font-medium text-slate-900">{{ $booking->check_out_date->format('l, F d, Y') }} (12:00 PM)</span>
            </div>
            <div class="flex justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-400">Lead Guest</span>
                <span class="font-semibold text-slate-900">{{ $booking->guest_name }} ({{ $booking->guest_phone }})</span>
            </div>
            <div class="flex justify-between border-b border-slate-200 pb-2">
                <span class="text-slate-400">Advance Security Deposit</span>
                <span class="font-bold text-slate-900">₱{{ number_format($booking->advance_deposit_amount, 2) }}</span>
            </div>
            <div class="flex justify-between pt-1">
                <span class="text-slate-400">Total Paid / Due</span>
                <span class="font-extrabold text-blue-600 text-sm">₱{{ number_format($booking->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Staycation Guest Dossier & House Rules (A-F Staycation @ Deca Homes) -->
        <div class="text-left bg-gradient-to-br from-slate-50 to-blue-50/30 rounded-2xl border border-slate-200 p-6 space-y-4 text-xs mb-8">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-base">🏠</span>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-sm">Guest Stay Briefing & House Reminders</h4>
                        <p class="text-[11px] text-slate-500">Official instructions for {{ $unit->unit_number }} (A-F Staycation)</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">Deca Ortigas</span>
            </div>

            <!-- WiFi & Connection Credentials -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-white rounded-xl border border-slate-200">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">High-Speed WiFi Network</span>
                    <span class="font-mono font-bold text-slate-900 text-xs">
                        {{ str_contains($unit->unit_number, 'N') ? 'AFSaltLifePadDos' : 'AFSaltLifePadUno2g' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">WiFi Password</span>
                    <span class="font-mono font-bold text-blue-600 text-xs select-all">Unodos.020933</span>
                </div>
            </div>

            <!-- Key Check-in & Security Policies -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-600">
                <div class="flex items-start gap-2">
                    <span class="text-emerald-500 font-bold shrink-0">✓</span>
                    <span><strong>Check-in:</strong> 2:00 PM onwards | <strong>Checkout:</strong> 12:00 NN</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-emerald-500 font-bold shrink-0">✓</span>
                    <span><strong>Rider / Delivery:</strong> Meetup between Bldgs M & N parking garages</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-amber-500 font-bold shrink-0">⚠</span>
                    <span><strong>Garbage:</strong> Back of {{ str_contains($unit->unit_number, 'N') ? 'Bldg N' : 'Bldg P' }} (₱500 fine for unthrown trash)</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-rose-500 font-bold shrink-0">⛔</span>
                    <span><strong>No Smoking:</strong> Complex fine ₱2,000 (Smoking area at back of bldg)</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-amber-500 font-bold shrink-0">🔑</span>
                    <span><strong>Key / Elevator RFID Pass:</strong> ₱500 replacement fee each if lost</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-slate-500 font-bold shrink-0">🔇</span>
                    <span><strong>Quiet Hours:</strong> Sun–Thu 10 PM–8 AM | Fri–Sat 12 AM–9 AM</span>
                </div>
            </div>

            <!-- Host & Emergency Contacts Table -->
            <div class="pt-3 border-t border-slate-200">
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 mb-2">Important Contacts</div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                    <div class="p-2 bg-white rounded-lg border border-slate-200">
                        <span class="text-slate-400 block text-[9px]">Host / GCash</span>
                        <span class="font-bold text-slate-800">09478847463</span>
                    </div>
                    <div class="p-2 bg-white rounded-lg border border-slate-200">
                        <span class="text-slate-400 block text-[9px]">{{ str_contains($unit->unit_number, 'N') ? 'Bldg N' : 'Bldg P' }} Landline</span>
                        <span class="font-bold text-slate-800">{{ str_contains($unit->unit_number, 'N') ? '(02) 8642-8735' : '(02) 8659-4229' }}</span>
                    </div>
                    <div class="p-2 bg-white rounded-lg border border-slate-200">
                        <span class="text-slate-400 block text-[9px]">Admin Office</span>
                        <span class="font-bold text-slate-800">(02) 8539-2340</span>
                    </div>
                    <div class="p-2 bg-white rounded-lg border border-slate-200">
                        <span class="text-slate-400 block text-[9px]">Security / Viber</span>
                        <span class="font-bold text-slate-800">09530694221</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Rating & Review Section -->
        @if($booking->review)
            <div class="text-left bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent rounded-2xl border border-amber-500/20 p-6 mb-8">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 text-lg">★</span>
                        <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">Your Rating & Review</h4>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        {{ $booking->review->rating }}.0 / 5.0 Rating
                    </span>
                </div>
                <div class="flex items-center gap-1 text-amber-500 text-base mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <span>{{ $i <= $booking->review->rating ? '★' : '☆' }}</span>
                    @endfor
                </div>
                @if($booking->review->comment)
                    <p class="text-xs text-slate-600 dark:text-slate-300 italic mb-3">"{{ $booking->review->comment }}"</p>
                @endif
                <div class="flex flex-wrap gap-4 text-[11px] text-slate-500 pt-2 border-t border-amber-500/15">
                    @if($booking->review->cleanliness_rating)
                        <span>Cleanliness: <strong>{{ $booking->review->cleanliness_rating }}/5</strong></span>
                    @endif
                    @if($booking->review->communication_rating)
                        <span>Host Communication: <strong>{{ $booking->review->communication_rating }}/5</strong></span>
                    @endif
                    @if($booking->review->accuracy_rating)
                        <span>Accuracy: <strong>{{ $booking->review->accuracy_rating }}/5</strong></span>
                    @endif
                    @if($booking->review->value_rating)
                        <span>Value: <strong>{{ $booking->review->value_rating }}/5</strong></span>
                    @endif
                </div>
            </div>
        @elseif($booking->canBeReviewed())
            <div class="text-left bg-gradient-to-r from-blue-500/10 via-amber-500/5 to-transparent rounded-2xl border border-blue-500/20 p-6 mb-8">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✨</span>
                        <div>
                            <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">How was your stay at {{ $unit->unit_number }}?</h4>
                            <p class="text-[11px] text-slate-500">Your rating helps future guests and supports direct staycation hosts.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        ★ Verified Guest Rating
                    </span>
                </div>

                <form action="{{ route('reviews.store', $booking) }}" method="POST" class="space-y-4 pt-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Overall Rating *</label>
                        <div class="flex items-center gap-2" id="status-star-rating">
                            <input type="hidden" name="rating" id="status_rating_value" value="5" required>
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" onclick="setStatusRating({{ $i }})" class="status-star text-2xl text-amber-400 hover:scale-125 transition-transform" data-star="{{ $i }}">★</button>
                            @endfor
                            <span id="status-rating-label" class="text-xs font-bold text-slate-600 dark:text-slate-400 ml-2">5 - Exceptional</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Cleanliness</label>
                            <select name="cleanliness_rating" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white py-1.5 px-2">
                                <option value="5">5 ★ Exceptional</option>
                                <option value="4">4 ★ Good</option>
                                <option value="3">3 ★ Average</option>
                                <option value="2">2 ★ Below Average</option>
                                <option value="1">1 ★ Poor</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Communication</label>
                            <select name="communication_rating" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white py-1.5 px-2">
                                <option value="5">5 ★ Exceptional</option>
                                <option value="4">4 ★ Good</option>
                                <option value="3">3 ★ Average</option>
                                <option value="2">2 ★ Below</option>
                                <option value="1">1 ★ Poor</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Listing Accuracy</label>
                            <select name="accuracy_rating" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white py-1.5 px-2">
                                <option value="5">5 ★ Exceptional</option>
                                <option value="4">4 ★ Accurate</option>
                                <option value="3">3 ★ Mixed</option>
                                <option value="2">2 ★ Inaccurate</option>
                                <option value="1">1 ★ Poor</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Value for Money</label>
                            <select name="value_rating" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white py-1.5 px-2">
                                <option value="5">5 ★ Great Value</option>
                                <option value="4">4 ★ Good</option>
                                <option value="3">3 ★ Fair</option>
                                <option value="2">2 ★ Overpriced</option>
                                <option value="1">1 ★ Poor</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Leave your feedback or tips for future guests</label>
                        <textarea name="comment" rows="2" placeholder="e.g. Host was very responsive, WiFi was fast for Netflix, very smooth check-in at Deca Homes!" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white p-2.5 focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 shadow-md shadow-amber-500/20 transition-all">
                            <span>Submit Review & Rating</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </form>
            </div>

            <script>
                function setStatusRating(rating) {
                    document.getElementById('status_rating_value').value = rating;
                    const stars = document.querySelectorAll('.status-star');
                    const labels = {
                        1: '1 - Poor',
                        2: '2 - Fair',
                        3: '3 - Good',
                        4: '4 - Very Good',
                        5: '5 - Exceptional'
                    };
                    document.getElementById('status-rating-label').textContent = labels[rating] || (rating + ' Stars');
                    stars.forEach((star, idx) => {
                        if (idx < rating) {
                            star.textContent = '★';
                            star.classList.add('text-amber-400');
                            star.classList.remove('text-slate-300');
                        } else {
                            star.textContent = '☆';
                            star.classList.remove('text-amber-400');
                            star.classList.add('text-slate-300');
                        }
                    });
                }
            </script>
        @endif
            <a href="{{ route('compliance.portal', ['bookingCode' => $booking->booking_code]) }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                &larr; View Compliance Vault
            </a>
            <a href="{{ route('units.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                Browse More Units
            </a>
        </div>
    </div>

</div>
@endsection
