@extends('layouts.app')

@section('title', 'My Bookings - DirectStay')

@section('content')
<div class="page-enter max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
                Guest Dashboard
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                My DirectStay Reservations
            </h1>
            <p class="text-xs text-slate-500">
                Logged in as <strong>{{ $user->name }}</strong> ({{ $user->email }}) &bull; Mobile: {{ $user->phone ?? 'Not set' }}
            </p>
        </div>

        <a href="{{ route('units.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Book Another Unit
        </a>
    </div>

    <section class="mb-8 rounded-3xl border border-emerald-100 bg-emerald-50 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-emerald-950">How to book a stay</h2>
                <p class="mt-1 text-xs leading-relaxed text-emerald-800">Choose a unit, select available dates and guests, then start the reservation. You will be guided to upload the security-deposit receipt and your ID. Add every person staying in the unit so the building can issue the required gate pass.</p>
            </div>
            <a href="{{ route('units.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700">Explore units</a>
        </div>
    </section>

    @if($bookings->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-sm max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                🏨
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">No bookings yet</h3>
            <p class="text-xs text-slate-500 mb-6">
                You haven't made any staycation reservations yet. Explore our verified units in Urban Deca Homes Ortigas with zero OTA markups.
            </p>
            <a href="{{ route('units.index') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition-all">
                Browse Units in Ortigas Pasig &rarr;
            </a>
        </div>
    @else
        <div class="stagger-enter space-y-6">
            @foreach($bookings as $b)
                <div class="surface-card hover-lift bg-white/90 rounded-3xl border overflow-hidden">
                    <div class="p-6 sm:p-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">

                        <!-- Left Info -->
                        <div class="flex items-start gap-5">
                            <!-- Thumbnail -->
                            <div class="w-24 h-24 rounded-2xl overflow-hidden shrink-0 bg-slate-100 border border-slate-200">
                                @if($b->unit->cover_image)
                                    <img src="{{ asset($b->unit->cover_image) }}" alt="Unit" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-bold">DS</div>
                                @endif
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                                        {{ $b->booking_code }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-semibold">
                                        {{ $b->unit->building->name }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md">
                                        {{ $b->unit->unit_number }}
                                    </span>
                                </div>

                                <h3 class="text-base font-bold text-slate-900 mb-1">
                                    {{ $b->unit->title }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        📅 {{ $b->check_in_date->format('M d, Y') }} &rarr; {{ $b->check_out_date->format('M d, Y') }} ({{ $b->nights_count }} Nights)
                                    </span>
                                    <span>&bull;</span>
                                    <span>👥 {{ $b->guest_count }} Guests</span>
                                    <span>&bull;</span>
                                    <span class="font-bold text-slate-900">Total: ₱{{ number_format($b->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Status & CTAs -->
                        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-start lg:justify-end border-t lg:border-t-0 pt-4 lg:pt-0 border-slate-100">
                            <div>
                            <div>
                                @if($b->review)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold score-badge-gold shadow-xs" title="Your rating: {{ $b->review->rating }} Stars">
                                        <span class="text-amber-600">★ {{ $b->review->rating }}.0</span>
                                        <span class="text-amber-900/60 font-semibold text-[11px]">Rated</span>
                                    </span>
                                @elseif($b->status === 'checked_out')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Completed Stay
                                    </span>
                                @elseif($b->status === 'confirmed' || $b->status === 'checked_in')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Gate Pass Ready ✓
                                    </span>
                                @elseif($b->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        Action / Verification Required
                                    </span>
                                @endif
                            </div>

                            @if($b->status === 'checked_out' && ! $b->review)
                                <button type="button" onclick="openReviewModal('{{ $b->id }}', '{{ $b->unit->title }}', '{{ $b->booking_code }}')"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-amber-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-400 hover:from-amber-400 hover:to-yellow-500 shadow-md shadow-amber-500/25 hover:scale-105 active:scale-95 transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5 fill-amber-950" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>Rate Your Stay</span>
                                </button>
                            @endif

                            <a href="{{ route('compliance.portal', $b->booking_code) }}"
                               class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                                Details &bull; Documents
                            </a>

                            @if($b->status === 'confirmed' || $b->status === 'checked_in')
                                <a href="{{ route('compliance.downloadGatePass', $b->booking_code) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all"
                                   title="Official Building Gate Pass (Deca Guest Form)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Gate Pass
                                </a>
                                <a href="{{ route('compliance.downloadRentalAgreement', $b->booking_code) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 shadow-xs transition-all"
                                   title="Official Lease Contract (Staycation Rental Agreement)">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    Rental Contract
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<!-- Interactive Rating & Review Modal -->
<div id="reviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="surface-card bg-white rounded-3xl border border-slate-200 w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">Rate Your Stay</span>
                <h3 class="text-lg font-bold text-slate-900 mt-1" id="reviewModalUnitTitle">Review Stay</h3>
                <p class="text-xs text-slate-400" id="reviewModalBookingCode"></p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                ✕
            </button>
        </div>

        <form id="reviewModalForm" method="POST" action="" class="p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Overall Stay Rating</label>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5" id="starContainer">
                        @for($star = 1; $star <= 5; $star++)
                            <button type="button" onclick="setModalRating({{ $star }})" onmouseover="hoverModalRating({{ $star }})" onmouseleave="resetModalRating()"
                                    class="star-btn text-2xl text-slate-300 transition-transform cursor-pointer" data-star="{{ $star }}">
                                ★
                            </button>
                        @endfor
                    </div>
                    <span id="ratingLabel" class="text-xs font-bold text-amber-600 ml-2">Click to select rating</span>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="5" required>
            </div>

            <!-- Category Ratings (Sub-ratings) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Cleanliness</label>
                    <select name="cleanliness_rating" class="w-full rounded-lg border-slate-200 text-xs py-1.5 px-2 bg-white">
                        <option value="5">5 ★ Spotless</option>
                        <option value="4">4 ★ Clean</option>
                        <option value="3">3 ★ Average</option>
                        <option value="2">2 ★ Below Average</option>
                        <option value="1">1 ★ Poor</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Communication</label>
                    <select name="communication_rating" class="w-full rounded-lg border-slate-200 text-xs py-1.5 px-2 bg-white">
                        <option value="5">5 ★ Responsive</option>
                        <option value="4">4 ★ Helpful</option>
                        <option value="3">3 ★ Acceptable</option>
                        <option value="2">2 ★ Slow</option>
                        <option value="1">1 ★ Unresponsive</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Accuracy</label>
                    <select name="accuracy_rating" class="w-full rounded-lg border-slate-200 text-xs py-1.5 px-2 bg-white">
                        <option value="5">5 ★ Accurate</option>
                        <option value="4">4 ★ Mostly Accurate</option>
                        <option value="3">3 ★ Fair</option>
                        <option value="2">2 ★ Inaccurate</option>
                        <option value="1">1 ★ Misleading</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Your Review & Feedback</label>
                <textarea name="comment" rows="4" maxlength="1000" placeholder="Share your experience (e.g. unit comfort, aircon, WiFi speed, amenities, check-in ease)..."
                          class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 shadow-md shadow-amber-500/25 hover:scale-105 active:scale-95 transition-all cursor-pointer">
                    Publish Review
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentRating = 5;
    const ratingDescriptions = {
        1: '1★ Needs Improvement',
        2: '2★ Fair Experience',
        3: '3★ Good Stay',
        4: '4★ Very Good!',
        5: '5★ Exceptional Staycation!'
    };

    function openReviewModal(bookingId, unitTitle, bookingCode) {
        const modal = document.getElementById('reviewModal');
        const form = document.getElementById('reviewModalForm');
        const titleEl = document.getElementById('reviewModalUnitTitle');
        const codeEl = document.getElementById('reviewModalBookingCode');

        if (modal && form) {
            form.action = '/bookings/' + bookingId + '/reviews';
            if (titleEl) titleEl.innerText = unitTitle;
            if (codeEl) codeEl.innerText = 'Reservation Reference: ' + bookingCode;
            setModalRating(5);
            modal.classList.remove('hidden');
        }
    }

    function closeReviewModal() {
        const modal = document.getElementById('reviewModal');
        if (modal) modal.classList.add('hidden');
    }

    function setModalRating(rating) {
        currentRating = rating;
        const input = document.getElementById('ratingInput');
        if (input) input.value = rating;

        updateStarDisplay(rating);
        const label = document.getElementById('ratingLabel');
        if (label) label.innerText = ratingDescriptions[rating] || (rating + ' Stars');
    }

    function hoverModalRating(rating) {
        updateStarDisplay(rating);
        const label = document.getElementById('ratingLabel');
        if (label) label.innerText = ratingDescriptions[rating] || (rating + ' Stars');
    }

    function resetModalRating() {
        updateStarDisplay(currentRating);
        const label = document.getElementById('ratingLabel');
        if (label) label.innerText = ratingDescriptions[currentRating] || (currentRating + ' Stars');
    }

    function updateStarDisplay(rating) {
        const buttons = document.querySelectorAll('#starContainer .star-btn');
        buttons.forEach((btn, index) => {
            const starValue = index + 1;
            if (starValue <= rating) {
                btn.classList.add('star-active');
                btn.classList.remove('star-inactive');
            } else {
                btn.classList.remove('star-active');
                btn.classList.add('star-inactive');
            }
        });
    }
</script>
@endpush
@endsection
