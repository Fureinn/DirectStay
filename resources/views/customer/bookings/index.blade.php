@extends('layouts.app')

@section('title', 'My Bookings - DirectStay Itinerary')

@section('content')
<div class="page-enter max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Top Header & User Meta -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                <span>Guest Travel Hub</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                My DirectStay Reservations
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Active account: <strong class="text-slate-800 dark:text-slate-200">{{ $user->name }}</strong> ({{ $user->email }}) &bull; Mobile: <span class="font-mono text-slate-700 dark:text-slate-300">{{ $user->phone ?? 'Not set' }}</span>
            </p>
        </div>

        <a href="{{ route('units.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl font-black text-xs text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 transition-all btn-press">
            <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>Book Another Staycation</span>
        </a>
    </div>

    <!-- Booking Guidance Banner -->
    <div class="mb-8 rounded-3xl border border-emerald-200/80 dark:border-emerald-900/60 bg-emerald-50/80 dark:bg-emerald-950/40 p-5 sm:p-6 backdrop-blur-md relative overflow-hidden shadow-xs">
        <div class="ambient-glow w-32 h-32 bg-emerald-400/15 -top-8 -right-8 glow-animate"></div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
            <div>
                <h2 class="text-sm font-black text-emerald-950 dark:text-emerald-200 flex items-center gap-2">
                    <span class="text-base">🛡️</span>
                    <span>Direct-Booking Clearance Checklist</span>
                </h2>
                <p class="mt-1 text-xs leading-relaxed text-emerald-800 dark:text-emerald-300 max-w-2xl">
                    For seamless check-in at Urban Deca Homes Ortigas: upload your <strong>₱1,000 security deposit receipt</strong>, government ID for all occupants, and sign the digital waiver. The official PMO gate pass PDF will be dispatched automatically upon approval.
                </p>
            </div>
            <a href="{{ route('units.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-emerald-600 dark:bg-emerald-500 px-4 py-2 text-xs font-black text-white shadow-xs hover:bg-emerald-700 transition-all btn-press">
                Explore Units &rarr;
            </a>
        </div>
    </div>

    @if($bookings->isEmpty())
        <!-- Empty State -->
        <div class="glass-surface-dense rounded-3xl border border-white/60 dark:border-white/10 p-12 text-center shadow-lg max-w-lg mx-auto relative overflow-hidden">
            <div class="ambient-glow w-40 h-40 bg-blue-500/10 -top-10 -right-10 glow-animate"></div>
            <div class="w-16 h-16 rounded-3xl bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner border border-blue-100 dark:border-slate-700">
                🏨
            </div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">No Reservations Yet</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                You haven't made any staycation reservations yet. Browse verified direct condo suites across Building N &amp; P in Pasig with zero OTA markups and instant gate pass clearance.
            </p>
            <a href="{{ route('units.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-xs font-black text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 transition-all btn-press">
                <span>Browse Condos in Deca Ortigas</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    @else
        <div class="stagger-enter space-y-6">
            @foreach($bookings as $b)
                @php
                    $isConfirmed = in_array($b->status, ['confirmed', 'checked_in']);
                    $isCheckedOut = $b->status === 'checked_out';
                    $isCancelled = $b->status === 'cancelled';
                    $hasIds = $b->complianceDocuments->filter(fn($d) => str_starts_with($d->document_type, 'gov_id_'))->count() >= $b->guest_count;
                    $hasReceipt = $b->complianceDocuments->contains('document_type', 'payment_receipt');
                @endphp
                <div class="glass-surface rounded-3xl border border-white/60 dark:border-white/10 hover-lift overflow-hidden shadow-lg relative p-6 sm:p-8 group">
                    <div class="ambient-glow w-48 h-48 bg-blue-500/10 -bottom-12 -right-12 glow-animate"></div>

                    <!-- Top Card Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-slate-200/60 dark:border-slate-800/80">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-black text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/80 px-2.5 py-1 rounded-lg border border-blue-200 dark:border-blue-800">
                                {{ $b->booking_code }}
                            </span>
                            <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300">
                                {{ $b->unit->building?->name ?? 'Urban Deca Homes Ortigas' }}
                            </span>
                            <span class="text-xs font-black text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                                Tower {{ $b->unit->building?->code ?? 'UDH' }} &bull; Unit {{ $b->unit->unit_number }}
                            </span>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($b->review)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black score-badge-gold shadow-xs">
                                    <span class="text-amber-600">★ {{ $b->review->rating }}.0</span>
                                    <span class="text-amber-900/60 font-semibold text-[11px]">Reviewed</span>
                                </span>
                            @elseif($isCheckedOut)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    Stay Completed
                                </span>
                            @elseif($isConfirmed)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-xs">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 status-beacon"></span>
                                    Gate Pass Issued ✓
                                </span>
                            @elseif($isCancelled)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                    Cancelled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-xs">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Action Required &bull; Upload Vault Docs
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Main Itinerary Content Grid -->
                    <div class="py-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                        <!-- Left: Thumbnail & Details -->
                        <div class="lg:col-span-7 flex items-start gap-4 sm:gap-5">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden shrink-0 bg-slate-900 border border-slate-200/80 dark:border-slate-700 shadow-xs group-hover:scale-102 transition-transform duration-300">
                                @if($b->unit->cover_image)
                                    <img src="{{ asset($b->unit->cover_image) }}" alt="{{ $b->unit->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-bold bg-slate-900 text-white">DS</div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mb-1.5 truncate">
                                    {{ $b->unit->title }}
                                </h3>

                                <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="font-bold text-slate-900 dark:text-white">
                                            {{ $b->check_in_date->format('M d, Y') }} &rarr; {{ $b->check_out_date->format('M d, Y') }}
                                        </span>
                                        <span class="text-slate-400 dark:text-slate-500">({{ $b->nights_count }} Nights)</span>
                                    </div>

                                    <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400">
                                        <span>👥 {{ $b->guest_count }} {{ Str::plural('Guest', $b->guest_count) }}</span>
                                        <span>&bull;</span>
                                        <span>Sec Deposit: ₱{{ number_format($b->advance_deposit_amount, 0) }}</span>
                                        <span>&bull;</span>
                                        <span class="font-black text-slate-900 dark:text-white tabular-nums font-mono">Total: ₱{{ number_format($b->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Action CTAs -->
                        <div class="lg:col-span-5 flex flex-wrap items-center gap-2.5 justify-start lg:justify-end">
                            @if($isCheckedOut && ! $b->review)
                                <button type="button" onclick="openReviewModal('{{ $b->id }}', '{{ addslashes($b->unit->title) }}', '{{ $b->booking_code }}')"
                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-black text-amber-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-400 hover:from-amber-400 hover:to-yellow-500 shadow-md shadow-amber-500/25 transition-all btn-press cursor-pointer">
                                    <span class="text-amber-950">★</span>
                                    <span>Rate Your Stay</span>
                                </button>
                            @endif

                            <a href="{{ route('compliance.portal', $b->booking_code) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white/80 dark:bg-slate-800/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700 shadow-2xs transition-all btn-press">
                                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Security Vault</span>
                            </a>

                            @if($isConfirmed)
                                <a href="{{ route('compliance.downloadGatePass', $b->booking_code) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-black text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 transition-all btn-press"
                                   title="Official Building Gate Pass (Deca Guest Form)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Gate Pass PDF</span>
                                </a>
                                <a href="{{ route('compliance.downloadRentalAgreement', $b->booking_code) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white/80 dark:bg-slate-800/80 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700 transition-all btn-press"
                                   title="Official Lease Contract (Staycation Rental Agreement)">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Rental Contract</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Stay Progress Timeline Track -->
                    <div class="pt-4 border-t border-slate-200/60 dark:border-slate-800/60">
                        <div class="grid grid-cols-4 gap-2 text-center text-[11px]">
                            <!-- Step 1 -->
                            <div class="flex flex-col items-center">
                                <span class="w-5 h-5 rounded-full bg-emerald-500 text-white font-bold text-[10px] flex items-center justify-center mb-1">✓</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">1. Reserved</span>
                            </div>

                            <!-- Step 2 -->
                            <div class="flex flex-col items-center">
                                <span class="w-5 h-5 rounded-full {{ $hasReceipt && $hasIds ? 'bg-emerald-500 text-white' : 'bg-blue-600 text-white animate-pulse' }} font-bold text-[10px] flex items-center justify-center mb-1">
                                    {{ $hasReceipt && $hasIds ? '✓' : '2' }}
                                </span>
                                <span class="font-bold {{ $hasReceipt && $hasIds ? 'text-slate-800 dark:text-slate-200' : 'text-blue-600 dark:text-blue-400' }}">
                                    2. ID &amp; Payment
                                </span>
                            </div>

                            <!-- Step 3 -->
                            <div class="flex flex-col items-center">
                                <span class="w-5 h-5 rounded-full {{ $isConfirmed ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }} font-bold text-[10px] flex items-center justify-center mb-1">
                                    {{ $isConfirmed ? '✓' : '3' }}
                                </span>
                                <span class="font-bold {{ $isConfirmed ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500' }}">
                                    3. Gate Pass
                                </span>
                            </div>

                            <!-- Step 4 -->
                            <div class="flex flex-col items-center">
                                <span class="w-5 h-5 rounded-full {{ $isCheckedOut ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }} font-bold text-[10px] flex items-center justify-center mb-1">
                                    {{ $isCheckedOut ? '✓' : '4' }}
                                </span>
                                <span class="font-bold {{ $isCheckedOut ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500' }}">
                                    4. Check-in (2PM)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<!-- Interactive Rating & Review Modal -->
<div id="reviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4">
    <div class="glass-modal-box bg-white dark:bg-slate-900 rounded-3xl border border-white/60 dark:border-white/10 w-full max-w-lg overflow-hidden shadow-2xl relative">
        <div class="ambient-glow w-32 h-32 bg-amber-400/10 -top-8 -right-8 glow-animate"></div>

        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between relative z-10">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 dark:bg-amber-950/80 px-2.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">
                    Rate Your Stay
                </span>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mt-1" id="reviewModalUnitTitle">Review Stay</h3>
                <p class="text-xs text-slate-400 font-mono mt-0.5" id="reviewModalBookingCode"></p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors cursor-pointer">
                ✕
            </button>
        </div>

        <form id="reviewModalForm" method="POST" action="" class="p-6 space-y-5 relative z-10">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Overall Stay Rating</label>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5" id="starContainer">
                        @for($star = 1; $star <= 5; $star++)
                            <button type="button" onclick="setModalRating({{ $star }})" onmouseover="hoverModalRating({{ $star }})" onmouseleave="resetModalRating()"
                                    class="star-btn text-2xl text-slate-300 dark:text-slate-600 transition-transform cursor-pointer" data-star="{{ $star }}">
                                ★
                            </button>
                        @endfor
                    </div>
                    <span id="ratingLabel" class="text-xs font-bold text-amber-600 dark:text-amber-400 ml-2">Click to select rating</span>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="5" required>
            </div>

            <!-- Category Ratings (Sub-ratings) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 bg-slate-50 dark:bg-slate-800/80 rounded-2xl border border-slate-100 dark:border-slate-700 text-xs">
                <div>
                    <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Cleanliness</label>
                    <select name="cleanliness_rating" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 text-xs py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="5">5 ★ Spotless</option>
                        <option value="4">4 ★ Clean</option>
                        <option value="3">3 ★ Average</option>
                        <option value="2">2 ★ Below Average</option>
                        <option value="1">1 ★ Poor</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Communication</label>
                    <select name="communication_rating" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 text-xs py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="5">5 ★ Responsive</option>
                        <option value="4">4 ★ Helpful</option>
                        <option value="3">3 ★ Acceptable</option>
                        <option value="2">2 ★ Slow</option>
                        <option value="1">1 ★ Unresponsive</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Accuracy</label>
                    <select name="accuracy_rating" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 text-xs py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="5">5 ★ Accurate</option>
                        <option value="4">4 ★ Mostly Accurate</option>
                        <option value="3">3 ★ Fair</option>
                        <option value="2">2 ★ Inaccurate</option>
                        <option value="1">1 ★ Misleading</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Your Review &amp; Feedback</label>
                <textarea name="comment" rows="4" maxlength="1000" placeholder="Share your experience (e.g. unit comfort, aircon, WiFi speed, amenities, check-in ease)..."
                          class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-amber-950 bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-500 hover:to-yellow-500 shadow-md shadow-amber-500/25 transition-all btn-press cursor-pointer">
                    Publish Verified Review
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
                btn.classList.add('text-amber-400');
                btn.classList.remove('text-slate-300', 'dark:text-slate-600');
            } else {
                btn.classList.remove('text-amber-400');
                btn.classList.add('text-slate-300', 'dark:text-slate-600');
            }
        });
    }
</script>
@endpush
@endsection
