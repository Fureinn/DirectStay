@extends('layouts.app')

@section('title', $unit->title . ' - DirectStay Editorial')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .leaflet-container {
        font-family: 'Instrument Sans', sans-serif !important;
        background: #f1f5f9;
        border-radius: 1.25rem;
    }
</style>
@endpush

@section('content')
@php
    $photos = $unit->images ?? [];
    if (empty($photos) && $unit->cover_image) {
        $photos = [$unit->cover_image];
    }
@endphp

<!-- ======================================================================= -->
<!-- 1. EDGE-TO-EDGE EDITORIAL HERO SECTION -->
<!-- ======================================================================= -->
<div class="relative w-full h-[460px] sm:h-[540px] lg:h-[640px] bg-slate-950 overflow-hidden">
    <!-- Featured Background Imagery with Smooth Transition -->
    <img id="mainGalleryImage"
         src="{{ !empty($photos) ? asset($photos[0]) : asset('images/units/unit_n412.jpg') }}"
         alt="{{ $unit->title }}"
         class="w-full h-full object-cover object-center transition-all duration-500 scale-100">

    <!-- Editorial Dark Gradient Vignettes for High Typography Contrast -->
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-black/50 pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-slate-950/70 via-transparent to-black/40 pointer-events-none"></div>

    <!-- Hero Content Overlay (Breadcrumbs & Hero Badges) -->
    <div class="absolute inset-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex flex-col justify-between">
        
        <!-- Top Navigation / Badges -->
        <div class="flex items-center justify-between gap-4">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs font-semibold text-white/80 backdrop-blur-md bg-black/30 px-3.5 py-1.5 rounded-full border border-white/15">
                <a href="{{ route('units.index') }}" class="hover:text-white transition-colors">Catalog</a>
                <span class="text-white/40">&rsaquo;</span>
                <span class="text-white/70">{{ $unit->building ? $unit->building->code : 'UDH' }}</span>
                <span class="text-white/40">&rsaquo;</span>
                <span class="text-white font-bold">Unit {{ $unit->unit_number }}</span>
            </nav>

            <!-- Gallery Counter & Switcher Pill -->
            <div class="flex items-center gap-2">
                <span id="photoCounter" class="px-3.5 py-1.5 rounded-full text-xs font-bold text-white bg-black/40 backdrop-blur-md border border-white/20">
                    Photo 1 of {{ max(count($photos), 1) }}
                </span>
            </div>
        </div>

        <!-- Bottom Left: Unit Editorial Headline & Badges (Visible on Hero) -->
        <div class="max-w-2xl pb-16 sm:pb-24 lg:pb-36">
            <div class="flex items-center gap-2.5 mb-3">
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-white/20 text-white backdrop-blur-md border border-white/30">
                    {{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-600/90 text-white backdrop-blur-md border border-blue-400/40">
                    Tower {{ $unit->building ? $unit->building->code : 'UDH' }} &bull; Unit {{ $unit->unit_number }}
                </span>
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-white bg-amber-500/80 backdrop-blur-md">
                    <span>★ {{ number_format($unit->averageRating(), 1) }}</span>
                    <span class="text-white/70">({{ $unit->reviewsCount() }})</span>
                </div>
            </div>

            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight drop-shadow-md">
                {{ $unit->title }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-200 mt-2 flex items-center gap-1.5 drop-shadow-xs">
                <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ $unit->building ? $unit->building->address : 'KM 19 Ortigas Avenue Extension, Pasig City' }}</span>
            </p>
        </div>

    </div>

    <!-- Editorial Horizontal Thumbnails Bar -->
    @if(!empty($photos) && count($photos) > 1)
        <div class="absolute bottom-4 left-4 sm:left-8 z-10 flex items-center gap-2 overflow-x-auto max-w-sm sm:max-w-md p-1.5 rounded-2xl bg-black/40 backdrop-blur-md border border-white/20">
            @foreach($photos as $index => $photo)
                <button type="button"
                        onclick="switchMainPhoto('{{ asset($photo) }}', {{ $index + 1 }})"
                        class="thumb-btn relative h-12 w-16 sm:h-14 sm:w-20 rounded-xl overflow-hidden border-2 transition-all cursor-pointer shrink-0 {{ $index === 0 ? 'border-white ring-2 ring-white/50' : 'border-white/30 opacity-60 hover:opacity-100' }}"
                        data-index="{{ $index + 1 }}">
                    <img src="{{ asset($photo) }}" alt="Photo {{ $index + 1 }}" class="w-full h-full object-cover">
                </button>
            @endforeach
        </div>
    @endif
</div>

<!-- ======================================================================= -->
<!-- 2. EDITORIAL FLOAT CONTENT GRID (OVERLAPPING BOTTOM-RIGHT OF HERO) -->
<!-- ======================================================================= -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative -mt-24 sm:-mt-32 lg:-mt-44 z-20 pb-28 lg:pb-16">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- =================================================================== -->
        <!-- LEFT COLUMN: UNIT DETAILS, AMENITIES, REVIEWS & NEIGHBORHOOD MAP -->
        <!-- =================================================================== -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Primary Unit Specifications Bento -->
            <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6 sm:p-8 relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                        {{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }}
                    </span>
                    <span class="text-xs font-black text-slate-800 bg-slate-50 border border-slate-200 px-3 py-1 rounded-xl">
                        Unit {{ $unit->unit_number }}
                    </span>
                </div>

                <!-- Rate / Specs Grid with tabular-nums -->
                <div class="grid grid-cols-3 gap-3 p-4 bg-slate-50/80 rounded-2xl border border-slate-100 text-center mb-6">
                    <div>
                        <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Rate / Night</span>
                        <span class="text-lg sm:text-xl font-extrabold text-slate-900 tabular-nums tracking-tight">
                            ₱{{ number_format($unit->base_price_per_night, 0) }}
                        </span>
                    </div>
                    <div class="border-x border-slate-200">
                        <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Capacity</span>
                        <span class="text-lg sm:text-xl font-extrabold text-slate-900 tabular-nums tracking-tight">
                            {{ $unit->max_guests }} Guests
                        </span>
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Escrow Deposit</span>
                        <span class="text-lg sm:text-xl font-extrabold text-slate-900 tabular-nums tracking-tight">
                            ₱{{ number_format($unit->advance_deposit_required, 0) }}
                        </span>
                    </div>
                </div>

                <!-- Unit Description -->
                <div class="text-sm text-slate-600 leading-relaxed mb-6 space-y-3">
                    <p>{{ $unit->description }}</p>
                </div>

                <!-- Included Amenities -->
                @if(!empty($unit->inventory_items))
                    <div class="mb-6">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Included Amenities &amp; Furnishings</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                            @foreach($unit->inventory_items as $item)
                                <div class="flex items-center gap-2.5 p-3 bg-slate-50 rounded-2xl border border-slate-100 text-slate-700 font-medium hover:bg-slate-100/70 transition-all">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Building Security House Rules Policy -->
                <div class="p-5 rounded-2xl bg-amber-50/60 border border-amber-200/60 text-xs text-slate-800 relative">
                    <div class="flex items-center gap-2 font-bold text-sm text-slate-900 mb-1.5">
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Building Security &amp; Gate Pass Policy (Pasig PMO)
                    </div>
                    <p class="text-slate-600 leading-relaxed font-sans">
                        {{ $unit->building ? $unit->building->house_rules : 'All guests must submit valid government-issued IDs for security gate pass clearance prior to building entry.' }}
                    </p>
                </div>
            </div>

            <!-- Guest Ratings & Verified Reviews Section -->
            <div id="reviewsSection" class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6 sm:p-8 relative">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2.5 mb-1">
                            <span class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-1.5 tabular-nums">
                                <span class="text-amber-500 text-2xl">★</span>
                                <span>{{ number_format($unit->averageRating(), 1) }}</span>
                            </span>
                            <span class="text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-amber-50 text-amber-900 border border-amber-200">
                                Guest Favorite
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">
                            Based on {{ $unit->reviewsCount() }} verified guest review{{ $unit->reviewsCount() === 1 ? '' : 's' }}
                        </p>
                    </div>

                    @auth
                        @php
                            $userEligibleBooking = \App\Models\Booking::where('unit_id', $unit->id)
                                ->where('user_id', auth()->id())
                                ->whereIn('status', ['checked_out', 'confirmed', 'checked_in'])
                                ->whereDoesntHave('review')
                                ->latest()
                                ->first();
                        @endphp
                        @if($userEligibleBooking)
                            <button type="button" onclick="openReviewModal('{{ $userEligibleBooking->id }}', '{{ $unit->title }}', '{{ $userEligibleBooking->booking_code }}')"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl text-xs font-bold text-amber-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-400 hover:from-amber-400 hover:to-yellow-500 shadow-sm transition-all cursor-pointer">
                                <span>★ Rate Your Stay</span>
                            </button>
                        @endif
                    @endauth
                </div>

                <!-- Rating Criteria Breakdown Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-6 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
                    <div>
                        <div class="flex justify-between font-semibold text-slate-700 mb-1">
                            <span>Cleanliness</span>
                            <span class="font-bold text-slate-900 tabular-nums">5.0</span>
                        </div>
                        <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 100%;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between font-semibold text-slate-700 mb-1">
                            <span>Host Communication</span>
                            <span class="font-bold text-slate-900 tabular-nums">5.0</span>
                        </div>
                        <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 100%;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between font-semibold text-slate-700 mb-1">
                            <span>Amenities &amp; WiFi Speed</span>
                            <span class="font-bold text-slate-900 tabular-nums">4.9</span>
                        </div>
                        <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 98%;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between font-semibold text-slate-700 mb-1">
                            <span>Value for Money</span>
                            <span class="font-bold text-slate-900 tabular-nums">5.0</span>
                        </div>
                        <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Individual Reviews Feed -->
                <div class="space-y-3.5">
                    @forelse($unit->reviews as $review)
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/60 transition-all">
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($review->guest_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900">{{ $review->guest_name }}</span>
                                            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-bold border border-emerald-200">Verified Stay</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-0.5 text-amber-500 text-xs">
                                    @for($s = 1; $s <= 5; $s++)
                                        <span>{{ $s <= $review->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            </div>
                            @if($review->comment)
                                <p class="text-xs text-slate-600 leading-relaxed pl-11">
                                    &ldquo;{{ $review->comment }}&rdquo;
                                </p>
                            @endif
                        </div>
                    @empty
                        <p class="text-center py-6 text-slate-400 text-xs">
                            No guest reviews logged yet. Be the first to book and review!
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- Location & Neighborhood Map Section -->
            <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6 sm:p-7 relative overflow-hidden">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Where You'll Be
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }} &bull; {{ $unit->unit_number }}
                        </p>
                    </div>

                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(($unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas') . ' ' . ($unit->building->address ?? 'KM 19 Ortigas Avenue Extension Pasig City')) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-blue-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Directions</span>
                    </a>
                </div>

                <!-- Unit Location Leaflet Map Container -->
                <div id="unitLocationMap" class="w-full h-72 sm:h-80 rounded-2xl border border-slate-200 shadow-inner relative z-10 mb-4"></div>

                <!-- Neighborhood Transit & Distances Guide -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Deca Main Entrance</span>
                        <strong class="text-slate-900 block truncate">Ortigas Ave Ext Gate</strong>
                        <span class="text-[10px] text-blue-600 font-bold">Pass Clearance Point</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Commercial Hub</span>
                        <strong class="text-slate-900 block truncate">SM City East Ortigas</strong>
                        <span class="text-[10px] text-emerald-600 font-bold">1.2 km &bull; 5 mins</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Destination Estate</span>
                        <strong class="text-slate-900 block truncate">Bridgetowne / Victor</strong>
                        <span class="text-[10px] text-emerald-600 font-bold">2.5 km &bull; 8 mins</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- =================================================================== -->
        <!-- RIGHT COLUMN: FLOATING GLASS DATE PICKER & CHECKOUT COMPONENT -->
        <!-- =================================================================== -->
        <div class="lg:col-span-5" id="reservationWidget">
            <!-- Floating Glass Card overlapping bottom right of hero -->
            <div class="backdrop-blur-xl bg-white/70 shadow-2xl rounded-3xl border border-white/60 p-6 sm:p-7 sticky top-24 relative overflow-hidden transition-all duration-300">
                <!-- Soft Glow Blob behind card -->
                <div class="absolute -top-20 -right-20 w-52 h-52 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Price Header with tabular-nums & tracking-tight -->
                <div class="flex items-baseline justify-between mb-5">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl sm:text-4xl font-black text-slate-900 tabular-nums tracking-tight">
                            ₱{{ number_format($unit->base_price_per_night, 0) }}
                        </span>
                        <span class="text-sm font-semibold text-slate-500">/ night</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-700 font-semibold bg-white/80 border border-slate-200/80 px-2.5 py-1 rounded-full shadow-2xs backdrop-blur-sm">
                        <span class="text-amber-500 font-bold">★</span>
                        <span class="tabular-nums">{{ number_format($unit->averageRating(), 1) }}</span>
                        <span class="text-slate-300">&bull;</span>
                        <a href="#reviewsSection" class="text-slate-600 hover:text-slate-900 underline">{{ $unit->reviewsCount() }} reviews</a>
                    </div>
                </div>

                <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm" class="space-y-4"
                      x-data="{ showCalendar: false, breakdownOpen: false }">
                    @csrf
                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">

                    <!-- Direct Stay Protection Badge -->
                    <div class="rounded-2xl bg-blue-50/60 border border-blue-200/60 backdrop-blur-sm p-3.5 relative">
                        <p class="text-xs font-black text-blue-950 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            DirectStay Safe Escrow &amp; Gate Pass Clearance
                        </p>
                        <ol class="mt-2 grid grid-cols-3 gap-2 text-[10px] leading-snug text-blue-900 font-medium">
                            <li><span class="font-bold text-blue-700">1.</span> Dates</li>
                            <li><span class="font-bold text-blue-700">2.</span> Reserve</li>
                            <li><span class="font-bold text-blue-700">3.</span> ID Pass</li>
                        </ol>
                    </div>

                    <!-- Visual Date Picker Header & Toggle -->
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Stay Schedule</span>
                        <button type="button" @click="showCalendar = !showCalendar"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="showCalendar ? 'Hide Calendar' : 'View Calendar'"></span>
                        </button>
                    </div>

                    <!-- Interactive Calendar Viewport (Collapsible) -->
                    <div x-show="showCalendar" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="p-4 bg-white/70 rounded-2xl border border-white/80 backdrop-blur-md text-xs relative shadow-xs">
                        
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-slate-900" id="calMonthTitle">{{ date('F Y') }}</span>
                            <div class="flex items-center gap-3 text-[10px] font-semibold text-slate-500">
                                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> Available</span>
                                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-slate-300"></span> Booked</span>
                            </div>
                        </div>

                        <!-- Days of week -->
                        <div class="grid grid-cols-7 gap-1 text-center font-bold text-[10px] text-slate-400 mb-1">
                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                        </div>

                        <!-- Dynamic Calendar Grid Days -->
                        <div id="interactiveCalendarDays" class="grid grid-cols-7 gap-1 text-center text-xs">
                            <!-- Populated by JS -->
                        </div>
                        <p class="text-[10px] text-slate-400 text-center mt-2">
                            Click available dates to set check-in &amp; check-out
                        </p>
                    </div>

                    <!-- Airbnb-style Grouped Date/Guest Picker Box -->
                    <div class="rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-500/20 transition-all bg-white/80 backdrop-blur-md">
                        <div class="grid grid-cols-2 divide-x divide-slate-200/80 border-b border-slate-200/80">
                            <!-- Check-in -->
                            <div class="p-3 bg-white/50 hover:bg-white transition cursor-pointer">
                                <label for="checkInInput" class="block text-[9px] font-black uppercase tracking-wider text-slate-500">Check-in</label>
                                <input type="date" name="check_in_date" id="checkInInput"
                                       min="{{ date('Y-m-d') }}"
                                       value="{{ old('check_in_date', date('Y-m-d')) }}"
                                       required
                                       class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer tabular-nums tracking-tight font-mono">
                            </div>
                            <!-- Checkout -->
                            <div class="p-3 bg-white/50 hover:bg-white transition cursor-pointer">
                                <label for="checkOutInput" class="block text-[9px] font-black uppercase tracking-wider text-slate-500">Checkout</label>
                                <input type="date" name="check_out_date" id="checkOutInput"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       value="{{ old('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                       required
                                       class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer tabular-nums tracking-tight font-mono">
                            </div>
                        </div>

                        <!-- Guests Selector -->
                        <div class="p-3 bg-white/50 hover:bg-white transition">
                            <label for="guestCountSelect" class="block text-[9px] font-black uppercase tracking-wider text-slate-500">Guests</label>
                            <select name="guest_count" id="guestCountSelect"
                                    class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                @for($i = 1; $i <= $unit->max_guests; $i++)
                                    <option value="{{ $i }}" {{ old('guest_count') == $i ? 'selected' : '' }}>
                                        {{ $i }} guest{{ $i > 1 ? 's' : '' }} (Max {{ $unit->max_guests }})
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    @error('check_in_date')
                        <p class="text-xs text-rose-600 font-semibold p-2 bg-rose-50 border border-rose-200 rounded-xl">{{ $message }}</p>
                    @enderror
                    @error('check_out_date')
                        <p class="text-xs text-rose-600 font-semibold p-2 bg-rose-50 border border-rose-200 rounded-xl">{{ $message }}</p>
                    @enderror
                    @error('guest_count')
                        <p class="text-xs text-rose-600 font-semibold p-2 bg-rose-50 border border-rose-200 rounded-xl">{{ $message }}</p>
                    @enderror

                    <!-- Date Conflict Alert -->
                    <div id="dateAlert" class="hidden text-xs text-rose-600 font-bold p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Selected dates overlap with an existing reservation. Please choose another date.</span>
                    </div>

                    <!-- Step 2: Lead guest details (revealed after clicking Reserve or if validation fails) -->
                    <div id="confirmAndPaySection" class="{{ ($errors->any() || old('guest_name')) ? '' : 'hidden' }} space-y-3.5 pt-3 border-t border-slate-200/80">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Lead Guest Information</h3>
                            @auth
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Pre-filled</span>
                            @else
                                <a href="{{ route('customer.login') }}" class="text-[10px] text-blue-600 hover:text-blue-800 font-bold underline">Sign in</a>
                            @endauth
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Full Legal Name</label>
                                <input type="text" name="guest_name" id="guestNameInput"
                                       value="{{ old('guest_name', auth()->check() ? auth()->user()->name : '') }}"
                                       placeholder="Must match government ID" required
                                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('guest_name') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                                @error('guest_name')
                                    <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Email Address</label>
                                    <input type="email" name="guest_email" id="guestEmailInput"
                                           value="{{ old('guest_email', auth()->check() ? auth()->user()->email : '') }}"
                                           placeholder="For gate pass" required
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('guest_email') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                                    @error('guest_email')
                                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Mobile Phone</label>
                                    <input type="text" name="guest_phone" id="guestPhoneInput"
                                           value="{{ old('guest_phone', auth()->check() ? (auth()->user()->phone ?? '') : '') }}"
                                           placeholder="0917XXXXXXX" required
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('guest_phone') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                                    @error('guest_phone')
                                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Optional Add-ons -->
                        @if($addOns->count() > 0)
                            <div class="pt-2">
                                <label class="block text-[11px] font-bold text-slate-800 mb-2">Optional Stay Add-ons</label>
                                <div class="space-y-2">
                                    @foreach($addOns as $addon)
                                        <div class="flex items-center justify-between p-2.5 bg-white/70 rounded-xl border border-slate-200/80 text-xs">
                                            <div>
                                                <span class="font-bold text-slate-800">{{ $addon->name }}</span>
                                                <span class="text-slate-400 block text-[11px] tabular-nums tracking-tight">+₱{{ number_format($addon->price, 2) }}</span>
                                            </div>
                                            <input type="number" name="add_ons[{{ $addon->id }}]" min="0" max="10" value="0"
                                                   data-price="{{ $addon->price }}"
                                                   class="addon-input w-14 text-center text-xs font-bold py-1 px-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-600 tabular-nums">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Primary Action Buttons -->
                    <div class="pt-2">
                        <button type="button" id="reserveStepBtn" onclick="handleReserveClick()"
                                class="{{ ($errors->any() || old('guest_name')) ? 'hidden' : '' }} w-full py-3.5 px-4 rounded-2xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span id="btnText">Reserve Unit</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>

                        <button type="submit" id="confirmAndPayBtn"
                                class="{{ ($errors->any() || old('guest_name')) ? '' : 'hidden' }} w-full py-3.5 px-4 rounded-2xl text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span>Proceed to Security Clearance</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>

                        <p class="text-[11px] text-center text-slate-500 mt-2 font-medium" id="notChargedNotice">
                            No upfront card charge. Deposit details and ID gate pass vault on the next step.
                        </p>
                    </div>

                    <!-- Dynamic Price Summary & Collapsible Breakdown (with tabular-nums & tracking-tight) -->
                    <div class="pt-4 border-t border-slate-200/80 space-y-3">
                        <!-- Total Row -->
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-sm font-extrabold text-slate-900">Total Amount</span>
                                <button type="button" @click="breakdownOpen = !breakdownOpen"
                                        class="block text-[11px] font-bold text-blue-600 hover:text-blue-800 transition-colors mt-0.5 cursor-pointer">
                                    <span x-text="breakdownOpen ? 'Hide price breakdown ▲' : 'Show price breakdown ▼'"></span>
                                </button>
                            </div>
                            <span class="text-2xl font-black text-slate-900 tabular-nums tracking-tight font-mono" id="calcGrandTotal">₱0.00</span>
                        </div>

                        <!-- Itemized Breakdown with Alpine transition -->
                        <div x-show="breakdownOpen" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span>₱{{ number_format($unit->base_price_per_night, 0) }} x <span id="calcNights">1</span> night(s)</span>
                                <span class="font-bold text-slate-900 tabular-nums tracking-tight font-mono" id="calcRoomTotal">₱0.00</span>
                            </div>

                            <div id="addonsRow" class="hidden flex justify-between">
                                <span>Add-ons Subtotal</span>
                                <span class="font-bold text-slate-900 tabular-nums tracking-tight font-mono" id="calcAddonsTotal">₱0.00</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="flex items-center gap-1">
                                    DirectStay platform fee (5%)
                                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1 rounded">Save vs OTA</span>
                                </span>
                                <span class="font-bold text-blue-600 tabular-nums tracking-tight font-mono" id="calcPlatformFee">₱0.00</span>
                            </div>

                            <div class="flex justify-between border-t border-slate-100 pt-2">
                                <span class="font-medium text-slate-700">Refundable security deposit</span>
                                <span class="font-bold text-slate-900 tabular-nums tracking-tight font-mono">₱{{ number_format($unit->advance_deposit_required, 2) }}</span>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-normal">
                                * The ₱{{ number_format($unit->advance_deposit_required, 0) }} deposit is held safely in escrow and refunded after checkout inspection.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Mobile Airbnb-Style Fixed Bottom Floating Bar -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 glass-bottom-bar px-4 py-3 flex items-center justify-between">
    <div>
        <div class="flex items-baseline gap-1">
            <span class="text-lg font-black text-slate-900 tabular-nums tracking-tight">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
            <span class="text-xs text-slate-500">/ night</span>
        </div>
        <div class="text-[11px] font-semibold text-slate-700 underline" id="mobileDateSummary">
            Select dates
        </div>
    </div>
    <button type="button" onclick="scrollAndOpenReserve()"
            class="px-6 py-2.5 rounded-2xl font-bold text-sm text-white bg-slate-900 shadow-md">
        Reserve
    </button>
</div>

@push('scripts')
<script>
    const basePrice = {{ $unit->base_price_per_night }};
    const advanceDeposit = {{ $unit->advance_deposit_required }};
    const platformFeeRate = 0.05; // 5% Guest Platform Fee
    const blockedDates = @json($blockedDates);
    const totalPhotos = {{ max(count($photos), 1) }};

    function switchMainPhoto(src, index) {
        const mainImg = document.getElementById('mainGalleryImage');
        const counter = document.getElementById('photoCounter');
        
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 150);

        if (counter) {
            counter.textContent = `Photo ${index} of ${totalPhotos}`;
        }

        document.querySelectorAll('.thumb-btn').forEach(btn => {
            if (parseInt(btn.dataset.index) === index) {
                btn.classList.add('border-white', 'ring-2', 'ring-white/50');
                btn.classList.remove('border-white/30', 'opacity-60');
            } else {
                btn.classList.remove('border-white', 'ring-2', 'ring-white/50');
                btn.classList.add('border-white/30', 'opacity-60');
            }
        });
    }

    const checkInInput = document.getElementById('checkInInput');
    const checkOutInput = document.getElementById('checkOutInput');
    const addonInputs = document.querySelectorAll('.addon-input');
    const dateAlert = document.getElementById('dateAlert');
    const reserveStepBtn = document.getElementById('reserveStepBtn');
    const confirmAndPayBtn = document.getElementById('confirmAndPayBtn');
    const confirmAndPaySection = document.getElementById('confirmAndPaySection');
    const notChargedNotice = document.getElementById('notChargedNotice');

    function calculate() {
        const inDate = new Date(checkInInput.value);
        const outDate = new Date(checkOutInput.value);

        let nights = 1;
        if (checkInInput.value && checkOutInput.value && outDate > inDate) {
            const diffTime = Math.abs(outDate - inDate);
            nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        }

        let isBlocked = false;
        let cur = new Date(inDate);
        while (cur < outDate) {
            const dateStr = cur.toISOString().split('T')[0];
            if (blockedDates.includes(dateStr)) {
                isBlocked = true;
                break;
            }
            cur.setDate(cur.getDate() + 1);
        }

        if (isBlocked) {
            dateAlert.classList.remove('hidden');
            reserveStepBtn.disabled = true;
            reserveStepBtn.classList.add('opacity-50', 'cursor-not-allowed');
            confirmAndPayBtn.disabled = true;
            confirmAndPayBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            dateAlert.classList.add('hidden');
            reserveStepBtn.disabled = false;
            reserveStepBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            confirmAndPayBtn.disabled = false;
            confirmAndPayBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        const roomTotal = nights * basePrice;
        const platformFee = Math.round(roomTotal * platformFeeRate * 100) / 100;

        let addonsTotal = 0;
        addonInputs.forEach(input => {
            const qty = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            addonsTotal += qty * price;
        });

        const grandTotal = roomTotal + addonsTotal + advanceDeposit + platformFee;

        // Text update with tabular-nums highlight
        const priceEls = [
            { el: document.getElementById('calcNights'), val: nights },
            { el: document.getElementById('calcRoomTotal'), val: '₱' + roomTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
            { el: document.getElementById('calcPlatformFee'), val: '₱' + platformFee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
            { el: document.getElementById('calcAddonsTotal'), val: '₱' + addonsTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
            { el: document.getElementById('calcGrandTotal'), val: '₱' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
        ];

        priceEls.forEach(item => {
            if (item.el && item.el.innerText !== item.val.toString()) {
                item.el.classList.add('scale-105', 'text-blue-600');
                item.el.innerText = item.val;
                setTimeout(() => {
                    item.el.classList.remove('scale-105');
                    if (item.el.id !== 'calcPlatformFee') {
                        item.el.classList.remove('text-blue-600');
                    }
                }, 200);
            }
        });

        const addonsRow = document.getElementById('addonsRow');
        if (addonsRow) {
            if (addonsTotal > 0) {
                addonsRow.classList.remove('hidden');
            } else {
                addonsRow.classList.add('hidden');
            }
        }

        // Mobile date summary label
        const mobileDateSummary = document.getElementById('mobileDateSummary');
        if (mobileDateSummary && checkInInput.value && checkOutInput.value) {
            mobileDateSummary.innerText = `${checkInInput.value} – ${checkOutInput.value} (${nights} night${nights > 1 ? 's' : ''})`;
        }

        renderInteractiveCalendar();
    }

    // Dynamic Visual Month Calendar Builder
    function renderInteractiveCalendar() {
        const calContainer = document.getElementById('interactiveCalendarDays');
        if (!calContainer) return;

        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const todayStr = now.toISOString().split('T')[0];

        const selectedIn = checkInInput.value;
        const selectedOut = checkOutInput.value;

        let html = '';

        for (let i = 0; i < firstDay; i++) {
            html += `<div class="h-8"></div>`;
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const dayMonth = (month + 1).toString().padStart(2, '0');
            const dayNum = d.toString().padStart(2, '0');
            const dateStr = `${year}-${dayMonth}-${dayNum}`;
            const isPast = dateStr < todayStr;
            const isBlocked = blockedDates.includes(dateStr);
            const isCheckIn = dateStr === selectedIn;
            const isCheckOut = dateStr === selectedOut;
            const isInRange = selectedIn && selectedOut && dateStr > selectedIn && dateStr < selectedOut;

            let cellClass = 'h-8 rounded-lg flex items-center justify-center font-bold text-xs transition-all relative ';

            if (isPast) {
                cellClass += 'text-slate-300 opacity-40 cursor-not-allowed';
                html += `<div class="${cellClass}">${d}</div>`;
            } else if (isBlocked) {
                cellClass += 'bg-slate-200/80 text-slate-400 line-through cursor-not-allowed';
                html += `<div class="${cellClass}" title="Unavailable">${d}</div>`;
            } else if (isCheckIn || isCheckOut) {
                cellClass += 'bg-blue-600 text-white shadow-xs cursor-pointer ring-2 ring-blue-600/30';
                html += `<button type="button" onclick="selectCalendarDate('${dateStr}')" class="${cellClass}">${d}</button>`;
            } else if (isInRange) {
                cellClass += 'bg-blue-100 text-blue-900 cursor-pointer font-extrabold';
                html += `<button type="button" onclick="selectCalendarDate('${dateStr}')" class="${cellClass}">${d}</button>`;
            } else {
                cellClass += 'text-slate-700 hover:bg-slate-100 cursor-pointer';
                html += `<button type="button" onclick="selectCalendarDate('${dateStr}')" class="${cellClass}">${d}</button>`;
            }
        }

        calContainer.innerHTML = html;
    }

    let datePickStep = 1;
    function selectCalendarDate(dateStr) {
        if (datePickStep === 1) {
            checkInInput.value = dateStr;
            const nextDay = new Date(dateStr);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.min = nextDay.toISOString().split('T')[0];
            if (checkOutInput.value <= dateStr) {
                checkOutInput.value = nextDay.toISOString().split('T')[0];
            }
            datePickStep = 2;
        } else {
            if (dateStr > checkInInput.value) {
                checkOutInput.value = dateStr;
                datePickStep = 1;
            } else {
                checkInInput.value = dateStr;
                datePickStep = 2;
            }
        }
        calculate();
    }

    function handleReserveClick() {
        const inDate = checkInInput.value;
        const outDate = checkOutInput.value;

        if (!inDate || !outDate) {
            alert('Please select valid check-in and checkout dates.');
            return;
        }

        confirmAndPaySection.classList.remove('hidden');
        reserveStepBtn.classList.add('hidden');
        confirmAndPayBtn.classList.remove('hidden');
        notChargedNotice.innerText = 'Deposit escrow details and ID gate pass vault on the next step.';

        const nameInput = document.getElementById('guestNameInput');
        if (nameInput) {
            nameInput.focus();
        }
    }

    function scrollAndOpenReserve() {
        const widget = document.getElementById('reservationWidget');
        if (widget) {
            widget.scrollIntoView({ behavior: 'smooth' });
            setTimeout(() => {
                handleReserveClick();
            }, 400);
        }
    }

    checkInInput.addEventListener('change', () => {
        const nextDay = new Date(checkInInput.value);
        nextDay.setDate(nextDay.getDate() + 1);
        checkOutInput.min = nextDay.toISOString().split('T')[0];
        if (checkOutInput.value <= checkInInput.value) {
            checkOutInput.value = nextDay.toISOString().split('T')[0];
        }
        calculate();
    });

    checkOutInput.addEventListener('change', calculate);
    addonInputs.forEach(input => input.addEventListener('input', calculate));

    // Run initial price calculation on page load
    calculate();

    // Review Modal Functions
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
        const modalBox = document.getElementById('reviewModalBox');
        const form = document.getElementById('reviewModalForm');
        const titleEl = document.getElementById('reviewModalUnitTitle');
        const codeEl = document.getElementById('reviewModalBookingCode');

        if (modal && form) {
            form.action = '/bookings/' + bookingId + '/reviews';
            if (titleEl) titleEl.innerText = unitTitle;
            if (codeEl) codeEl.innerText = 'Reservation Reference: ' + bookingCode;
            setModalRating(5);
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                if (modalBox) {
                    modalBox.classList.remove('scale-95');
                    modalBox.classList.add('scale-100');
                }
            }, 20);
        }
    }

    function closeReviewModal() {
        const modal = document.getElementById('reviewModal');
        const modalBox = document.getElementById('reviewModalBox');
        if (modal) {
            modal.classList.add('opacity-0');
            if (modalBox) {
                modalBox.classList.remove('scale-100');
                modalBox.classList.add('scale-95');
            }
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }
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
                btn.classList.add('star-active', 'text-amber-400');
                btn.classList.remove('star-inactive', 'text-slate-300');
            } else {
                btn.classList.remove('star-active', 'text-amber-400');
                btn.classList.add('star-inactive', 'text-slate-300');
            }
        });
    }
</script>

<!-- Interactive Rating & Review Modal -->
<div id="reviewModal" class="hidden opacity-0 fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-4 transition-all duration-300">
    <div id="reviewModalBox" class="bg-white rounded-3xl w-full max-w-lg overflow-hidden transition-all duration-300 transform scale-95 relative shadow-2xl border border-slate-100">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">Rate Your Stay</span>
                <h3 class="text-base font-bold text-slate-900 mt-1" id="reviewModalUnitTitle">Review Stay</h3>
                <p class="text-xs text-slate-400 font-mono" id="reviewModalBookingCode"></p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-all">
                ✕
            </button>
        </div>

        <form id="reviewModalForm" method="POST" action="" class="p-6 space-y-4">
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

            <!-- Sub-ratings -->
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
                <label class="block text-xs font-bold text-slate-700 mb-1">Your Review &amp; Feedback</label>
                <textarea name="comment" rows="3" maxlength="1000" placeholder="Share your experience (e.g. WiFi speed, cleanliness, host check-in assistance)..."
                          class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer">
                    Publish Review
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Initialize Single Unit Location Map
    document.addEventListener('DOMContentLoaded', function () {
        const mapContainer = document.getElementById('unitLocationMap');
        if (!mapContainer) return;

        const unitLat = {{ $unit->latitude }};
        const unitLng = {{ $unit->longitude }};

        const map = L.map('unitLocationMap', {
            center: [unitLat, unitLng],
            zoom: 17,
            scrollWheelZoom: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        const pricePin = L.divIcon({
            className: 'price-pin-wrapper',
            html: `<div class="map-price-marker active" style="background:#2563eb;color:#ffffff;">
                        <span>₱{{ number_format($unit->base_price_per_night, 0) }}/night</span>
                   </div>`,
            iconSize: [110, 36],
            iconAnchor: [55, 36],
            popupAnchor: [0, -38]
        });

        L.marker([unitLat, unitLng], { icon: pricePin }).addTo(map)
            .bindPopup(`
                <div class="p-3 text-left">
                    <span class="text-[10px] font-bold uppercase text-slate-400">{{ $unit->building?->code ?? 'UDH' }} &bull; {{ $unit->unit_number }}</span>
                    <h4 class="text-xs font-bold text-slate-900 mt-0.5">{{ $unit->title }}</h4>
                    <p class="text-[10px] text-slate-500 mt-1">{{ $unit->building?->address ?? 'KM 19 Ortigas Ave Ext, Pasig' }}</p>
                </div>
            `, { className: 'custom-map-popup' })
            .openPopup();
    });
</script>
@endpush
@endsection
