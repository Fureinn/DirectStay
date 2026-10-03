@extends('layouts.app')

@section('title', $unit->title . ' - DirectStay')

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
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-28 lg:pb-12">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6">
        <a href="{{ route('units.index') }}" class="hover:text-slate-900 transition-colors font-medium">All Units</a>
        <span>&rsaquo;</span>
        <span>{{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }}</span>
        <span>&rsaquo;</span>
        <span class="text-slate-900 font-semibold">{{ $unit->unit_number }}</span>
    </nav>

    <!-- Photo Gallery Showcase -->
    @php
        $photos = $unit->images ?? [];
        if (empty($photos) && $unit->cover_image) {
            $photos = [$unit->cover_image];
        }
    @endphp

    <div class="glass-surface mb-8 rounded-3xl p-4 sm:p-6 relative overflow-hidden">
        <div class="ambient-glow w-48 h-48 bg-blue-400/10 -top-16 -left-16 glow-animate blob-animate"></div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- Main Featured Image Viewport -->
            <div class="lg:col-span-8 relative h-72 sm:h-96 lg:h-[480px] rounded-2xl overflow-hidden bg-slate-900 shadow-inner group">
                <img id="mainGalleryImage"
                     src="{{ !empty($photos) ? asset($photos[0]) : asset('images/units/unit_n412.jpg') }}"
                     alt="{{ $unit->title }}"
                     class="w-full h-full object-cover transition-opacity duration-300">

                <div class="absolute top-4 left-4 flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-slate-900/80 backdrop-blur-md text-white border border-white/20">
                        {{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-slate-900/90 text-white shadow-xs">
                        Unit {{ $unit->unit_number }}
                    </span>
                </div>

                <div class="absolute bottom-4 right-4">
                    <span id="photoCounter" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-900/80 backdrop-blur-md text-white border border-white/20">
                        Photo 1 of {{ max(count($photos), 1) }}
                    </span>
                </div>
            </div>

            <!-- Thumbnail Side Gallery -->
            <div class="lg:col-span-4 flex lg:flex-col gap-3 overflow-x-auto lg:overflow-y-auto max-h-[480px] pr-1 pb-2 lg:pb-0">
                @if(!empty($photos))
                    @foreach($photos as $index => $photo)
                        <button type="button"
                                onclick="switchMainPhoto('{{ asset($photo) }}', {{ $index + 1 }})"
                                class="thumb-btn flex-shrink-0 relative h-20 w-28 lg:w-full lg:h-28 rounded-xl overflow-hidden border-2 transition-all cursor-pointer {{ $index === 0 ? 'border-slate-900 ring-2 ring-slate-900/20' : 'border-slate-200 hover:border-slate-400 opacity-75 hover:opacity-100' }}"
                                data-index="{{ $index + 1 }}">
                            <img src="{{ asset($photo) }}" alt="Photo {{ $index + 1 }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                @else
                    <div class="h-full flex flex-col items-center justify-center p-6 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-xs text-slate-500">More gallery photos being uploaded</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content & Reservation Engine Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Column: Unit Information & Building Rules -->
        <div class="lg:col-span-7 space-y-6">
            <div class="glass-surface rounded-3xl p-6 sm:p-8 relative overflow-hidden">
                <div class="ambient-glow w-40 h-40 bg-indigo-400/8 -top-12 -right-12 glow-animate"></div>
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800">
                        {{ $unit->building ? $unit->building->name : 'Building ' . $unit->unit_number }}
                    </span>
                    <span class="text-xs font-extrabold text-slate-700 bg-slate-50 border border-slate-200 px-3 py-1 rounded-lg">
                        {{ $unit->unit_number }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-2">
                    {{ $unit->title }}
                </h1>
                <p class="text-xs text-slate-500 mb-6 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ $unit->building ? $unit->building->address : 'KM 19 Ortigas Avenue Extension, Brgy. Rosario, Pasig City' }}</span>
                </p>

                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                    {{ $unit->description }}
                </p>

                <!-- Key Specifications -->
                <div class="grid grid-cols-3 gap-3 p-4 bg-white/35 rounded-2xl border border-white/40 backdrop-blur-sm text-center mb-8 relative">
                    <div>
                        <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Rate / Night</span>
                        <span class="text-lg font-extrabold text-slate-900">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
                    </div>
                    <div class="border-x border-white/40">
                        <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Max Occupants</span>
                        <span class="text-lg font-extrabold text-slate-900">{{ $unit->max_guests }} Guests</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Security Deposit</span>
                        <span class="text-lg font-extrabold text-slate-900">₱{{ number_format($unit->advance_deposit_required, 0) }}</span>
                    </div>
                </div>

                <!-- Unit Inventory Checklist -->
                @if(!empty($unit->inventory_items))
                    <div class="mb-8">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Included Amenities</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            @foreach($unit->inventory_items as $item)
                                <div class="flex items-center gap-2 p-2.5 bg-white/30 rounded-xl border border-white/40 backdrop-blur-sm text-slate-700 font-medium hover:bg-white/45 transition-all">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $item }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Building Security House Rules -->
                <div class="p-6 rounded-2xl bg-amber-50/40 border border-amber-200/40 backdrop-blur-sm text-xs text-slate-800 relative">
                    <div class="flex items-center gap-2 font-bold text-sm text-slate-900 mb-2">
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Building Security & Gate Pass Policy (Urban Deca Homes Ortigas)
                    </div>
                    <div class="whitespace-pre-line text-slate-600 leading-relaxed font-sans">
                        {{ $unit->building ? $unit->building->house_rules : 'All guests must submit valid government-issued IDs for security gate pass clearance prior to building entry.' }}
                    </div>
                </div>

                <!-- Guest Ratings & Verified Reviews Section -->
                <div id="reviewsSection" class="mt-8 pt-8 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <div class="flex items-center gap-2.5 mb-1">
                                <span class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-1.5">
                                    <span class="text-amber-500 text-2xl">★</span>
                                    <span>{{ number_format($unit->averageRating(), 1) }}</span>
                                </span>
                                <span class="text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full score-badge-gold">
                                    Guest Favorite
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">
                                Overall score from {{ $unit->reviewsCount() }} verified guest review{{ $unit->reviewsCount() === 1 ? '' : 's' }}
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
                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-amber-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-400 hover:from-amber-400 hover:to-yellow-500 shadow-md shadow-amber-500/25 hover:scale-105 active:scale-95 transition-all cursor-pointer">
                                    <span>★ Rate Your Stay</span>
                                </button>
                            @endif
                        @endauth
                    </div>

                    <!-- Rating Criteria Breakdown Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 p-4 bg-white/30 rounded-2xl border border-white/40 backdrop-blur-sm text-xs relative">
                        <div>
                            <div class="flex justify-between font-semibold text-slate-700 mb-1">
                                <span>Cleanliness</span>
                                <span class="font-bold text-slate-900">5.0</span>
                            </div>
                            <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full" style="width: 100%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between font-semibold text-slate-700 mb-1">
                                <span>Host Communication</span>
                                <span class="font-bold text-slate-900">5.0</span>
                            </div>
                            <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full" style="width: 100%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between font-semibold text-slate-700 mb-1">
                                <span>Amenities & WiFi Speed</span>
                                <span class="font-bold text-slate-900">4.9</span>
                            </div>
                            <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full" style="width: 98%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between font-semibold text-slate-700 mb-1">
                                <span>Value for Money</span>
                                <span class="font-bold text-slate-900">5.0</span>
                            </div>
                            <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Review Cards -->
                    <div class="space-y-4">
                        @forelse($unit->reviews as $review)
                            <div class="p-5 rounded-2xl bg-white/40 border border-white/50 backdrop-blur-sm shadow-[0_8px_30px_rgb(0,0,0,0.06)] hover:bg-white/55 hover:border-white/60 transition-all duration-300">
                                <div class="flex items-start justify-between gap-4 mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-sm">
                                            {{ strtoupper(substr($review->guest_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-slate-900">{{ $review->guest_name }}</span>
                                                <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-bold border border-emerald-200">Verified Stay</span>
                                            </div>
                                            <span class="text-[11px] text-slate-400">{{ $review->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-0.5 text-amber-500 text-xs">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span>{{ $s <= $review->rating ? '★' : '☆' }}</span>
                                        @endfor
                                    </div>
                                </div>

                                @if($review->comment)
                                    <p class="text-xs text-slate-600 leading-relaxed pl-12">
                                        &ldquo;{{ $review->comment }}&rdquo;
                                    </p>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400 text-xs">
                                No guest reviews yet. Book this unit to be the first to share your experience!
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Location & Neighborhood Map Section -->
                <div class="glass-surface rounded-3xl p-6 sm:p-7 relative overflow-hidden mt-8 shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-white/60">
                    <div class="ambient-glow w-48 h-48 bg-blue-500/10 -top-16 -right-16 glow-animate"></div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Where You'll Be
                            </h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                {{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }} &bull; {{ $unit->unit_number }}
                            </p>
                        </div>

                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(($unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas') . ' ' . ($unit->building->address ?? 'KM 19 Ortigas Avenue Extension Pasig City')) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/70 hover:bg-white text-xs font-bold text-blue-600 border border-white/80 shadow-2xs transition-all btn-press">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Get Directions</span>
                        </a>
                    </div>

                    <!-- Unit Location Leaflet Map Container -->
                    <div id="unitLocationMap" class="w-full h-72 sm:h-80 rounded-2xl border border-white/80 shadow-inner relative z-10 mb-4"></div>

                    <!-- Neighborhood Transit & Distances Guide -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                        <div class="p-3 rounded-2xl bg-white/50 border border-white/60 shadow-2xs">
                            <span class="text-[10px] text-slate-400 block font-semibold">Deca Main Entrance</span>
                            <strong class="text-slate-900 block truncate">Ortigas Ave Ext Gate</strong>
                            <span class="text-[10px] text-blue-600 font-bold">Pass Clearance Point</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-white/50 border border-white/60 shadow-2xs">
                            <span class="text-[10px] text-slate-400 block font-semibold">Commercial Hub</span>
                            <strong class="text-slate-900 block truncate">SM City East Ortigas</strong>
                            <span class="text-[10px] text-emerald-600 font-bold">1.2 km &bull; 5 mins</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-white/50 border border-white/60 shadow-2xs">
                            <span class="text-[10px] text-slate-400 block font-semibold">Destination Estate</span>
                            <strong class="text-slate-900 block truncate">Bridgetowne / Victor</strong>
                            <span class="text-[10px] text-emerald-600 font-bold">2.5 km &bull; 8 mins</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Airbnb-Style Reservation Card -->
        <div class="lg:col-span-5" id="reservationWidget">
            <div class="glass-reservation-panel sticky top-24 rounded-3xl p-6 sm:p-7 relative overflow-hidden">
                <!-- Ambient Glow behind reservation panel -->
                <div class="ambient-glow w-48 h-48 bg-blue-500/10 -top-16 -right-16 glow-animate blob-animate"></div>
                <div class="ambient-glow w-32 h-32 bg-indigo-400/8 bottom-0 -left-8 glow-animate" style="animation-delay: 3s;"></div>

                <!-- Price Header -->
                <div class="flex items-baseline justify-between mb-5">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-black text-slate-900">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
                        <span class="text-sm font-normal text-slate-500">night</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-700 font-semibold bg-amber-50 border border-amber-200/80 px-2.5 py-1 rounded-full">
                        <span class="text-amber-500 font-bold">★</span>
                        <span>{{ number_format($unit->averageRating(), 1) }}</span>
                        <span class="text-slate-400 font-normal">&bull;</span>
                        <a href="#reviewsSection" class="text-slate-600 hover:text-slate-900 underline">{{ $unit->reviewsCount() }} reviews</a>
                    </div>
                </div>

                <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm" class="space-y-4"
                      x-data="{ showCalendar: false, breakdownOpen: false }">
                    @csrf
                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">

                    <div class="rounded-2xl bg-blue-50/40 border border-blue-200/40 backdrop-blur-sm p-4 relative">
                        <p class="text-xs font-black text-blue-950 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600 status-beacon"></span>
                            Direct Staycation Protection
                        </p>
                        <ol class="mt-2 grid grid-cols-3 gap-2 text-[11px] leading-snug text-blue-900 font-medium">
                            <li><span class="font-bold text-blue-700">1.</span> Select dates &amp; guests</li>
                            <li><span class="font-bold text-blue-700">2.</span> Reserve unit</li>
                            <li><span class="font-bold text-blue-700">3.</span> Gate pass clearance</li>
                        </ol>
                    </div>

                    <!-- Visual Date Picker Header & Toggle -->
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Trip Dates</span>
                        <button type="button" @click="showCalendar = !showCalendar"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span x-text="showCalendar ? 'Hide Visual Calendar' : 'View Availability Calendar'"></span>
                        </button>
                    </div>

                    <!-- Interactive Calendar Viewport (Collapsible) -->
                    <div x-show="showCalendar" x-cloak
                         x-transition:enter="transition ease-out duration-250"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="p-4 bg-white/35 rounded-2xl border border-white/40 backdrop-blur-sm text-xs relative">
                        
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-extrabold text-slate-900" id="calMonthTitle">{{ date('F Y') }}</span>
                            <div class="flex items-center gap-3 text-[10px] font-semibold text-slate-500">
                                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> Available</span>
                                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-slate-300"></span> Reserved</span>
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
                    <div class="rounded-2xl border border-white/40 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.06)] focus-within:border-blue-600/60 focus-within:ring-2 focus-within:ring-blue-500/20 transition-all bg-white/30 backdrop-blur-sm">
                        <div class="grid grid-cols-2 divide-x divide-white/30 border-b border-white/30">
                            <!-- Check-in -->
                            <div class="p-3 bg-white/40 hover:bg-white/55 transition cursor-pointer">
                                <label for="checkInInput" class="block text-[9px] font-black uppercase tracking-wider text-slate-600">Check-in</label>
                                <input type="date" name="check_in_date" id="checkInInput"
                                       min="{{ date('Y-m-d') }}"
                                       value="{{ old('check_in_date', date('Y-m-d')) }}"
                                       required
                                       class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer tabular-nums font-mono">
                            </div>
                            <!-- Checkout -->
                            <div class="p-3 bg-white/40 hover:bg-white/55 transition cursor-pointer">
                                <label for="checkOutInput" class="block text-[9px] font-black uppercase tracking-wider text-slate-600">Checkout</label>
                                <input type="date" name="check_out_date" id="checkOutInput"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       value="{{ old('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                       required
                                       class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer tabular-nums font-mono">
                            </div>
                        </div>

                        <!-- Guests Selector -->
                        <div class="p-3 bg-white/40 hover:bg-white/55 transition">
                            <label for="guestCountSelect" class="block text-[9px] font-black uppercase tracking-wider text-slate-600">Guests</label>
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
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Selected dates overlap with an existing reservation. Please select different dates.</span>
                    </div>

                    <!-- Step 2: lead guest details (revealed after choosing dates) -->
                    <div id="confirmAndPaySection" class="{{ ($errors->any() || old('guest_name')) ? '' : 'hidden' }} space-y-4 pt-4 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-900">Lead guest details</h3>
                            @auth
                                <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Pre-filled</span>
                            @else
                                <a href="{{ route('customer.login') }}" class="text-[11px] text-blue-600 hover:text-blue-800 font-bold underline">Log in</a>
                            @endauth
                        </div>

                        <!-- Lead Guest Info -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name</label>
                                <input type="text" name="guest_name" id="guestNameInput"
                                       value="{{ old('guest_name', auth()->check() ? auth()->user()->name : '') }}"
                                       placeholder="As shown on government ID" required
                                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('guest_name') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600">
                                @error('guest_name')
                                    <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                                    <input type="email" name="guest_email" id="guestEmailInput"
                                           value="{{ old('guest_email', auth()->check() ? auth()->user()->email : '') }}"
                                           placeholder="For gate pass" required
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('guest_email') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600">
                                    @error('guest_email')
                                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone</label>
                                    <input type="text" name="guest_phone" id="guestPhoneInput"
                                           value="{{ old('guest_phone', auth()->check() ? (auth()->user()->phone ?? '') : '') }}"
                                           placeholder="e.g. 09171234567" required
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border @error('guest_phone') border-rose-400 bg-rose-50/30 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600">
                                    @error('guest_phone')
                                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Optional Add-ons -->
                        @if($addOns->count() > 0)
                            <div class="pt-2">
                                <label class="block text-xs font-bold text-slate-800 mb-2">Optional Stay Extras</label>
                                <div class="space-y-2">
                                    @foreach($addOns as $addon)
                                        <div class="flex items-center justify-between p-2.5 bg-white/30 rounded-xl border border-white/35 backdrop-blur-sm text-xs hover:bg-white/45 transition-all">
                                            <div>
                                                <span class="font-bold text-slate-800">{{ $addon->name }}</span>
                                                <span class="text-slate-400 block text-[11px] font-mono tabular-nums">+₱{{ number_format($addon->price, 2) }} each</span>
                                            </div>
                                            <input type="number" name="add_ons[{{ $addon->id }}]" min="0" max="10" value="0"
                                                   data-price="{{ $addon->price }}"
                                                   class="addon-input w-16 text-center text-xs font-bold py-1 px-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-600 tabular-nums">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Primary Action Button -->
                    <div class="pt-2">
                        <button type="button" id="reserveStepBtn" onclick="handleReserveClick()"
                                class="{{ ($errors->any() || old('guest_name')) ? 'hidden' : '' }} w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 hover:shadow-lg transition-all btn-press flex items-center justify-center gap-2 cursor-pointer">
                            <span id="btnText">Reserve Unit</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>

                        <button type="submit" id="confirmAndPayBtn"
                                class="{{ ($errors->any() || old('guest_name')) ? '' : 'hidden' }} w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-600/25 hover:shadow-lg transition-all btn-press flex items-center justify-center gap-2 cursor-pointer">
                            <span>Proceed to Security Clearance</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>

                        <p class="text-xs text-center text-slate-500 mt-2.5 font-medium" id="notChargedNotice">
                            No upfront card charge. Deposit details and ID gate pass vault on the next step.
                        </p>
                    </div>

                    <!-- Clean Dynamic Price Summary & Collapsible Breakdown -->
                    <div class="pt-4 border-t border-slate-200 space-y-3">
                        <!-- Total Row -->
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-base font-extrabold text-slate-900">Total Due</span>
                                <button type="button" @click="breakdownOpen = !breakdownOpen"
                                        class="block text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors mt-0.5 cursor-pointer">
                                    <span x-text="breakdownOpen ? 'Hide price breakdown ▲' : 'Show price breakdown ▼'"></span>
                                </button>
                            </div>
                            <span class="text-2xl font-black text-slate-900 tabular-nums font-mono tracking-tight" id="calcGrandTotal">₱0.00</span>
                        </div>

                        <!-- Itemized Breakdown with Alpine transition -->
                        <div x-show="breakdownOpen" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span>₱{{ number_format($unit->base_price_per_night, 0) }} x <span id="calcNights">1</span> night(s)</span>
                                <span class="font-bold text-slate-900 tabular-nums font-mono" id="calcRoomTotal">₱0.00</span>
                            </div>

                            <div id="addonsRow" class="hidden flex justify-between">
                                <span>Add-ons Subtotal</span>
                                <span class="font-bold text-slate-900 tabular-nums font-mono" id="calcAddonsTotal">₱0.00</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="flex items-center gap-1">
                                    DirectStay platform fee (5%)
                                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1 rounded">Save vs Airbnb</span>
                                </span>
                                <span class="font-bold text-blue-600 tabular-nums font-mono" id="calcPlatformFee">₱0.00</span>
                            </div>

                            <div class="flex justify-between border-t border-slate-100 pt-2">
                                <span class="font-medium text-slate-700">Refundable security deposit</span>
                                <span class="font-bold text-slate-900 tabular-nums font-mono">₱{{ number_format($unit->advance_deposit_required, 2) }}</span>
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
            <span class="text-lg font-black text-slate-900">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
            <span class="text-xs text-slate-500">night</span>
        </div>
        <div class="text-[11px] font-semibold text-slate-700 underline" id="mobileDateSummary">
            Select dates
        </div>
    </div>
    <button type="button" onclick="scrollAndOpenReserve()"
            class="px-6 py-2.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-rose-500 to-pink-600 shadow-md shadow-rose-500/25">
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
                btn.classList.add('border-slate-900', 'ring-2', 'ring-slate-900/20');
                btn.classList.remove('border-slate-200', 'opacity-75');
            } else {
                btn.classList.remove('border-slate-900', 'ring-2', 'ring-slate-900/20');
                btn.classList.add('border-slate-200', 'opacity-75');
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

        // Smooth text update with tabular-nums highlight
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

        // Empty padding cells for first week offset
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
                html += `<div class="${cellClass}" title="Reserved Date">${d}</div>`;
            } else if (isCheckIn || isCheckOut) {
                cellClass += 'bg-blue-600 text-white shadow-xs cursor-pointer hover:scale-105';
                html += `<button type="button" onclick="selectCalendarDate('${dateStr}')" class="${cellClass}">${d}</button>`;
            } else if (isInRange) {
                cellClass += 'bg-blue-100 text-blue-800 cursor-pointer hover:bg-blue-200';
                html += `<button type="button" onclick="selectCalendarDate('${dateStr}')" class="${cellClass}">${d}</button>`;
            } else {
                cellClass += 'text-slate-700 hover:bg-blue-50 hover:text-blue-600 cursor-pointer';
                html += `<button type="button" onclick="selectCalendarDate('${dateStr}')" class="${cellClass}">${d}</button>`;
            }
        }

        calContainer.innerHTML = html;
    }

    let calClickStep = 0;
    function selectCalendarDate(dateStr) {
        if (calClickStep === 0 || dateStr <= checkInInput.value) {
            checkInInput.value = dateStr;
            const nextDay = new Date(dateStr);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.min = dateStr;
            checkOutInput.value = nextDay.toISOString().split('T')[0];
            calClickStep = 1;
        } else {
            checkOutInput.value = dateStr;
            calClickStep = 0;
        }
        calculate();
    }

    function handleReserveClick() {
        confirmAndPaySection.classList.remove('hidden');
        reserveStepBtn.classList.add('hidden');
        confirmAndPayBtn.classList.remove('hidden');
        if (notChargedNotice) {
            notChargedNotice.innerText = 'Next: start the reservation, then submit your security deposit and ID for host approval.';
        }

        const nameInput = document.getElementById('guestNameInput');
        if (nameInput && !nameInput.value) {
            nameInput.focus();
        }
    }

    function scrollAndOpenReserve() {
        const widget = document.getElementById('reservationWidget');
        if (widget) {
            widget.scrollIntoView({ behavior: 'smooth' });
            handleReserveClick();
        }
    }

    checkInInput.addEventListener('change', () => {
        checkOutInput.min = checkInInput.value;
        if (checkOutInput.value <= checkInInput.value) {
            const nextDay = new Date(checkInInput.value);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.value = nextDay.toISOString().split('T')[0];
        }
        calculate();
    });

    checkOutInput.addEventListener('change', calculate);
    addonInputs.forEach(input => input.addEventListener('input', calculate));

    calculate();

    // Review Modal Interactive Functions with Smooth Animations
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
                btn.classList.add('star-active');
                btn.classList.remove('star-inactive');
            } else {
                btn.classList.remove('star-active');
                btn.classList.add('star-inactive');
            }
        });
    }
</script>

<!-- Interactive Rating & Review Modal -->
<div id="reviewModal" class="hidden opacity-0 fixed inset-0 z-50 overflow-y-auto glass-modal-overlay flex items-center justify-center p-4 transition-all duration-300">
    <div id="reviewModalBox" class="glass-modal-box rounded-3xl w-full max-w-lg overflow-hidden transition-all duration-300 transform scale-95 relative">
        <div class="ambient-glow w-40 h-40 bg-amber-400/10 -top-12 -right-12 glow-animate"></div>
        <div class="p-6 border-b border-white/30 flex items-center justify-between relative">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">Rate Your Stay</span>
                <h3 class="text-lg font-bold text-slate-900 mt-1" id="reviewModalUnitTitle">Review Stay</h3>
                <p class="text-xs text-slate-400 font-mono" id="reviewModalBookingCode"></p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-full bg-white/50 hover:bg-white/70 backdrop-blur-sm text-slate-500 flex items-center justify-center transition-all btn-press border border-white/40">
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
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-white/30 rounded-2xl border border-white/35 backdrop-blur-sm text-xs relative">
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
                <textarea name="comment" rows="3" maxlength="1000" placeholder="Share your experience (e.g. aircon coolness, WiFi speed, amenities, building pool, host assistance)..."
                          class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors btn-press">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 shadow-md shadow-amber-500/25 btn-press cursor-pointer">
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

        // Standard OpenStreetMap tiles (100% Free, NO API key required)
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
