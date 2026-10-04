@extends('layouts.app')

@section('title', 'DirectStay - Urban Deca Homes Ortigas Direct Bookings')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .leaflet-container {
        font-family: 'Instrument Sans', sans-serif !important;
        background: #f1f5f9;
        border-radius: 1.5rem;
    }
    .custom-map-popup .leaflet-popup-content-wrapper {
        padding: 0;
        overflow: hidden;
        border-radius: 1.25rem;
    }
    .custom-map-popup .leaflet-popup-content {
        margin: 0;
        line-height: 1;
    }
</style>
@endpush

@php
    $unitsMapData = ($allUnits ?? $units)->map(function ($u) use ($checkIn, $checkOut, $selectedGuests) {
        return [
            'id' => $u->id,
            'title' => $u->title,
            'unit_number' => $u->unit_number,
            'building_code' => $u->building?->code ?? '',
            'building_name' => $u->building?->name ?? 'Urban Deca Homes Ortigas',
            'address' => $u->building?->address ?? 'KM 19 Ortigas Ave Ext, Brgy. Rosario, Pasig City',
            'price_formatted' => '₱' . number_format($u->base_price_per_night, 0),
            'price' => (float) $u->base_price_per_night,
            'lat' => (float) $u->latitude,
            'lng' => (float) $u->longitude,
            'rating' => number_format($u->averageRating(), 1),
            'reviews_count' => $u->reviewsCount(),
            'max_guests' => $u->max_guests,
            'image' => $u->cover_image ? asset($u->cover_image) : null,
            'url' => route('units.show', array_filter(['unit' => $u, 'check_in' => $checkIn ?? null, 'check_out' => $checkOut ?? null, 'guests' => $selectedGuests ?? null])),
        ];
    });
@endphp

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8"
     x-data="{
        viewMode: 'grid', // 'grid', 'split', 'map'
        selectedUnitId: null,
        toggleView(mode) {
            this.viewMode = mode;
            $nextTick(() => {
                if (window.directStayMap) {
                    setTimeout(() => window.directStayMap.invalidateSize(), 200);
                }
            });
        },
        focusUnit(unitId) {
            this.selectedUnitId = unitId;
            if (window.focusMapUnit) {
                window.focusMapUnit(unitId);
            }
        }
     }">

    <!-- ======================================================================= -->
    <!-- 1. EDITORIAL STAYCATION HERO & SEARCH CAPSULE -->
    <!-- ======================================================================= -->
    <div class="relative overflow-hidden rounded-3xl sm:rounded-4xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 text-white p-6 sm:p-10 lg:p-12 mb-8 shadow-2xl border border-white/15">
        <!-- Ambient atmospheric glow effects -->
        <div class="ambient-glow w-96 h-96 bg-blue-500/20 -right-16 -top-16 glow-animate blob-animate"></div>
        <div class="ambient-glow w-96 h-96 bg-emerald-500/15 -left-16 -bottom-16 glow-animate"></div>
        <div class="ambient-glow w-64 h-64 bg-indigo-500/10 top-1/2 left-1/3 glow-animate"></div>

        <div class="relative z-10 max-w-4xl mx-auto text-center flex flex-col items-center">
            <!-- Trust Badge Pill -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 hover:bg-white/15 text-blue-200 border border-white/20 text-xs font-bold uppercase tracking-wider mb-5 backdrop-blur-xl shadow-xs transition-all">
                <span class="w-2 h-2 rounded-full bg-emerald-400 status-beacon"></span>
                <span>Urban Deca Homes Ortigas &bull; Official Direct Stays</span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white mb-4 leading-[1.12]">
                Direct Condo Staycations.<br class="hidden sm:inline" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-emerald-300 to-teal-200">
                    Zero Middleman. Instant Gate Pass.
                </span>
            </h1>

            <p class="text-slate-300 text-sm sm:text-base lg:text-lg max-w-2xl mb-8 leading-relaxed font-normal">
                Skip commercial OTA markups. Book verified condo suites directly from unit owners across <strong>Buildings N &amp; P</strong> in Pasig City with lean <strong>5% service fee</strong> (save ~₱276 per night vs Airbnb) and guaranteed lobby clearance.
            </p>

            <!-- Floating Staycation Search Capsule (Airbnb / Booking Style) -->
            <div class="w-full max-w-4xl bg-white/95 backdrop-blur-2xl rounded-2xl sm:rounded-3xl p-3 sm:p-4 text-slate-900 shadow-2xl border border-white/80">
                <form method="GET" action="{{ route('units.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 sm:gap-3 items-center">
                    <!-- Tower / Building Dropdown -->
                    <div class="lg:col-span-3 text-left px-3.5 py-2.5 rounded-xl bg-slate-50/80 hover:bg-slate-100/80 border border-slate-200/60 transition-colors">
                        <label for="building" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            Tower / Building
                        </label>
                        <select name="building" id="building" class="w-full bg-transparent font-bold text-xs sm:text-sm text-slate-800 focus:outline-none cursor-pointer mt-0.5">
                            <option value="">All Towers (Deca Ortigas)</option>
                            @foreach($buildings as $b)
                                <option value="{{ $b->code }}" {{ $selectedBuilding === $b->code ? 'selected' : '' }}>
                                    Tower {{ $b->code }} ({{ $b->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Check-in Date -->
                    <div class="lg:col-span-3 text-left px-3.5 py-2.5 rounded-xl bg-slate-50/80 hover:bg-slate-100/80 border border-slate-200/60 transition-colors">
                        <label for="check_in" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            Check-in Date
                        </label>
                        <input type="date" name="check_in" id="check_in"
                               min="{{ now()->toDateString() }}"
                               value="{{ $checkIn ?? '' }}"
                               class="w-full bg-transparent font-bold text-xs sm:text-sm text-slate-800 focus:outline-none cursor-pointer mt-0.5">
                    </div>

                    <!-- Check-out Date -->
                    <div class="lg:col-span-3 text-left px-3.5 py-2.5 rounded-xl bg-slate-50/80 hover:bg-slate-100/80 border border-slate-200/60 transition-colors">
                        <label for="check_out" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            Check-out Date
                        </label>
                        <input type="date" name="check_out" id="check_out"
                               min="{{ now()->addDay()->toDateString() }}"
                               value="{{ $checkOut ?? '' }}"
                               class="w-full bg-transparent font-bold text-xs sm:text-sm text-slate-800 focus:outline-none cursor-pointer mt-0.5">
                    </div>

                    <!-- Guests -->
                    <div class="lg:col-span-2 text-left px-3.5 py-2.5 rounded-xl bg-slate-50/80 hover:bg-slate-100/80 border border-slate-200/60 transition-colors">
                        <label for="guests" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            Guests
                        </label>
                        <select name="guests" id="guests" class="w-full bg-transparent font-bold text-xs sm:text-sm text-slate-800 focus:outline-none cursor-pointer mt-0.5">
                            <option value="">Any guests</option>
                            <option value="1" {{ (string)$selectedGuests === '1' ? 'selected' : '' }}>1 Guest</option>
                            <option value="2" {{ (string)$selectedGuests === '2' ? 'selected' : '' }}>2 Guests</option>
                            <option value="3" {{ (string)$selectedGuests === '3' ? 'selected' : '' }}>3 Guests</option>
                            <option value="4" {{ (string)$selectedGuests === '4' ? 'selected' : '' }}>4+ Guests</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="lg:col-span-1 flex items-center h-full">
                        <button type="submit"
                                class="w-full h-full min-h-[48px] rounded-xl font-bold text-xs sm:text-sm text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 flex items-center justify-center gap-1.5 transition-all btn-press cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="lg:hidden font-extrabold">Find Stays</span>
                        </button>
                    </div>
                </form>

                @if($selectedBuilding || $checkIn || $checkOut || $selectedGuests)
                    <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex flex-wrap items-center gap-1.5 text-slate-600">
                            <span class="font-bold text-slate-400">Active filters:</span>
                            @if($selectedBuilding)
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold border border-blue-200">Tower {{ $selectedBuilding }}</span>
                            @endif
                            @if($checkIn && $checkOut)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">{{ \Carbon\Carbon::parse($checkIn)->format('M d') }} – {{ \Carbon\Carbon::parse($checkOut)->format('M d') }}</span>
                            @endif
                            @if($selectedGuests)
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold border border-slate-200">{{ $selectedGuests }} {{ Str::plural('Guest', (int)$selectedGuests) }}</span>
                            @endif
                        </div>
                        <a href="{{ route('units.index') }}" class="font-bold text-rose-600 hover:text-rose-700 text-xs flex items-center gap-1">
                            <span>✕ Reset all filters</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Quick Trust Badges Strip -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3 sm:gap-4 text-xs font-medium text-slate-300">
                <div class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl backdrop-blur-md border border-white/10">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>KM 19 Ortigas Ave Ext, Rosario, Pasig</span>
                </div>
                <div class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl backdrop-blur-md border border-white/10">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>Building N &amp; P Gate Pass Certified</span>
                </div>
                <div class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl backdrop-blur-md border border-white/10">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>₱1,000 Refundable Security Deposit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 2. WHY DIRECTSTAY: 3-COLUMN VALUE PROPOSITION STRIP -->
    <!-- ======================================================================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        <!-- Value 1: Price Advantage -->
        <div class="p-5 sm:p-6 rounded-3xl bg-white/90 backdrop-blur-xl border border-white/80 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl shadow-xs">
                    💸
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                    Save ~₱276 / night
                </span>
            </div>
            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mb-1">
                Zero OTA Middleman Fees
            </h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Mainstream travel portals deduct 14%–16% guest fees plus 3%+ host charges. DirectStay applies an ultra-lean 5% platform fee with 0% host markup.
            </p>
            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">DirectStay Fee:</span>
                <span class="font-extrabold text-emerald-600">Only 5.0% (vs ~15% Airbnb)</span>
            </div>
        </div>

        <!-- Value 2: Digital Gate Pass Clearance -->
        <div class="p-5 sm:p-6 rounded-3xl bg-white/90 backdrop-blur-xl border border-white/80 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-xl shadow-xs">
                    🛡️
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-800">
                    Lobby Clearance
                </span>
            </div>
            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mb-1">
                Automated Lobby Gate Pass
            </h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Avoid security delays at Urban Deca Homes Ortigas. Official Building N &amp; P gate pass PDFs are automatically generated and emailed to you.
            </p>
            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Compliance:</span>
                <span class="font-extrabold text-blue-600">RA 10173 Encrypted Data Privacy</span>
            </div>
        </div>

        <!-- Value 3: Verified Direct Hosts -->
        <div class="p-5 sm:p-6 rounded-3xl bg-white/90 backdrop-blur-xl border border-white/80 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center text-xl shadow-xs">
                    ✨
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                    Verified Condos
                </span>
            </div>
            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mb-1">
                100% Verified Condo Hosts
            </h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Connect directly with genuine unit owners. Full kitchenette, split-type aircon, high-speed WiFi, smart TV with Netflix, and pool access included.
            </p>
            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Security Deposit:</span>
                <span class="font-extrabold text-indigo-600">₱1,000 Refundable Policy</span>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 3. FILTER & VIEW SWITCHER BAR -->
    <!-- ======================================================================= -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900">Featured Units in Deca Ortigas</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-800">
                    {{ $units->count() }} {{ Str::plural('unit', $units->count()) }} available
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Verified direct-booking units across Building N &amp; Building P in Pasig City.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            <!-- Building Filter Navigation (Glass Capsule) -->
            <div class="inline-flex p-1 rounded-2xl bg-white/70 backdrop-blur-xl border border-slate-200/70 shadow-xs">
                <a href="{{ route('units.index', array_filter(['check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $selectedGuests])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all btn-press {{ empty($selectedBuilding) ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    All Towers ({{ $totalUnitsCount ?? $units->count() }})
                </a>
                @foreach($buildings as $b)
                    <a href="{{ route('units.index', array_filter(['building' => $b->code, 'check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $selectedGuests])) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all btn-press {{ $selectedBuilding === $b->code ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Tower {{ $b->code }} ({{ $b->units_count }})
                    </a>
                @endforeach
            </div>

            <!-- View Switcher (Cards / Map & Prices / Split) -->
            <div class="inline-flex p-1 rounded-2xl bg-slate-200/70 backdrop-blur-xl border border-white/60 shadow-inner">
                <button type="button" @click="toggleView('grid')"
                        :class="viewMode === 'grid' ? 'bg-white text-blue-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Cards</span>
                </button>

                <button type="button" @click="toggleView('split')"
                        :class="viewMode === 'split' ? 'bg-white text-blue-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="hidden lg:flex px-3 py-1.5 rounded-xl text-xs transition-all items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    <span>Split View</span>
                </button>

                <button type="button" @click="toggleView('map')"
                        :class="viewMode === 'map' ? 'bg-white text-blue-600 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Map &amp; Prices</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 4. INTERACTIVE MAP SECTION (Shown in 'map' or 'split' view) -->
    <!-- ======================================================================= -->
    <div x-show="viewMode === 'map' || viewMode === 'split'"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-10">

        <div class="glass-surface p-4 sm:p-5 rounded-3xl border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.06)] relative overflow-hidden">
            <!-- Top Map Bar & Quick Focus Chips -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 status-beacon"></span>
                    <span class="text-xs font-bold text-slate-900">Interactive Deca Ortigas Map:</span>
                    <span class="text-xs text-slate-500">Click any price pin to view unit details</span>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                    <span class="text-slate-400 font-medium">Quick jump:</span>
                    <button type="button" @click="focusUnit('all')"
                            class="px-2.5 py-1 rounded-lg bg-white/70 hover:bg-white text-slate-700 font-bold border border-slate-200/60 shadow-2xs transition-all">
                        Complex Overview ({{ $totalUnitsCount ?? 2 }})
                    </button>
                    @foreach(($allUnits ?? $units) as $unit)
                        <button type="button" @click="focusUnit({{ $unit->id }})"
                                class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold border border-blue-200/60 shadow-2xs transition-all">
                            {{ $unit->unit_number }} (₱{{ number_format($unit->base_price_per_night, 0) }})
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div id="interactiveMap" class="w-full h-[420px] sm:h-[480px] rounded-2xl border border-white/80 shadow-inner relative z-10"></div>

            <!-- Vicinity Landmarks & Commute Guide -->
            <div class="mt-4 pt-4 border-t border-slate-200/50">
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Prime Ortigas Location &amp; Vicinity Distances:
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-white/50 border border-white/60 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-semibold">Deca Complex</span>
                        <strong class="text-slate-900 block truncate">KM 19 Ortigas Ave</strong>
                        <span class="text-[10px] text-blue-600 font-bold">Main Gate &bull; Pasig</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/50 border border-white/60 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-semibold">Shopping Mall</span>
                        <strong class="text-slate-900 block truncate">SM City East Ortigas</strong>
                        <span class="text-[10px] text-emerald-600 font-bold">1.2 km &bull; 5 mins</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/50 border border-white/60 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-semibold">Lifestyle Estate</span>
                        <strong class="text-slate-900 block truncate">Bridgetowne &amp; Victor</strong>
                        <span class="text-[10px] text-emerald-600 font-bold">2.5 km &bull; 8 mins</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/50 border border-white/60 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-semibold">Commercial Hub</span>
                        <strong class="text-slate-900 block truncate">Eastwood City Libis</strong>
                        <span class="text-[10px] text-emerald-600 font-bold">3.8 km &bull; 12 mins</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/50 border border-white/60 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-semibold">Hospital</span>
                        <strong class="text-slate-900 block truncate">The Medical City</strong>
                        <span class="text-[10px] text-blue-600 font-bold">4.2 km &bull; 15 mins</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/50 border border-white/60 shadow-2xs">
                        <span class="text-[10px] text-slate-400 block font-semibold">Business District</span>
                        <strong class="text-slate-900 block truncate">Ortigas Center CBD</strong>
                        <span class="text-[10px] text-blue-600 font-bold">5.5 km &bull; 20 mins</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 5. REFINED UNIT CARDS GRID -->
    <!-- ======================================================================= -->
    <div x-show="viewMode === 'grid' || viewMode === 'split'"
         class="stagger-enter grid grid-cols-1 lg:grid-cols-2 gap-8">
        @forelse($units as $unit)
            @php
                $otaEstimatedPrice = round($unit->base_price_per_night * 1.142);
                $estimatedSavings = round(($unit->base_price_per_night * 1.142) - ($unit->base_price_per_night * 1.05));
                $unitBookingUrl = route('units.show', array_filter([
                    'unit' => $unit,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'guests' => $selectedGuests
                ]));
            @endphp
            <div x-data="{ saved: false, showAllAmenities: false }"
                 id="unitCard-{{ $unit->id }}"
                 :class="selectedUnitId === {{ $unit->id }} ? 'ring-2 ring-blue-500 shadow-2xl scale-[1.01]' : ''"
                 class="bg-white/95 backdrop-blur-xl hover-lift flex flex-col rounded-3xl border border-slate-200/80 overflow-hidden group shadow-[0_10px_35px_rgb(15,23,42,0.06)] relative transition-all duration-300">
                
                <!-- Unit Card Media Container -->
                <div class="relative h-64 sm:h-72 w-full overflow-hidden bg-slate-900 z-10">
                    @if ($unit->cover_image)
                        <img src="{{ asset($unit->cover_image) }}" alt="{{ $unit->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900 p-6 flex flex-col justify-between text-white">
                            <span class="text-xs uppercase tracking-wider text-blue-400 font-bold">Urban Deca Homes Ortigas</span>
                            <div>
                                <h4 class="text-2xl font-bold">{{ $unit->title }}</h4>
                                <p class="text-sm text-slate-400">{{ $unit->unit_number }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-transparent to-black/30 pointer-events-none"></div>

                    <!-- Top Floating Badges on Image -->
                    <div class="absolute top-4 left-4 right-4 flex items-center justify-between pointer-events-auto">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-900/80 backdrop-blur-xl text-white border border-white/20 shadow-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Tower {{ $unit->building ? $unit->building->code : 'UDH' }} &bull; Unit {{ $unit->unit_number }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Star Rating Pill -->
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold score-badge-gold shadow-sm backdrop-blur-md">
                                <svg class="w-3.5 h-3.5 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="tabular-nums">{{ number_format($unit->averageRating(), 1) }}</span>
                                <span class="text-amber-900/60 font-semibold text-[11px]">({{ $unit->reviewsCount() }})</span>
                            </div>

                            <!-- Heart Wishlist Toggle Button -->
                            <button type="button" @click="saved = !saved"
                                    class="w-8 h-8 rounded-full bg-slate-900/60 hover:bg-slate-900/80 backdrop-blur-xl flex items-center justify-center text-white border border-white/20 transition-all btn-press cursor-pointer">
                                <svg class="w-4 h-4 transition-colors" :class="saved ? 'text-rose-500 fill-rose-500' : 'text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Bottom Titles on Image -->
                    <div class="absolute bottom-4 left-4 right-4 text-white pointer-events-none">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl sm:text-2xl font-black tracking-tight drop-shadow-sm">{{ $unit->title }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-600/90 backdrop-blur-md text-white border border-emerald-400/40 shadow-xs">
                                ⚡ Instant Gate Pass
                            </span>
                        </div>
                        <p class="text-xs text-slate-200 flex items-center gap-1.5 mt-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ $unit->building ? $unit->building->address : 'KM 19 Ortigas Ave Ext, Brgy. Rosario, Pasig City' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Unit Details Body -->
                <div class="p-6 flex-1 flex flex-col justify-between relative z-10">
                    <div>
                        <p class="text-xs sm:text-sm text-slate-600 mb-5 leading-relaxed line-clamp-2">
                            {{ $unit->description }}
                        </p>

                        <!-- Key Specs Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-5 text-xs">
                            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/60 flex items-center gap-3 shadow-2xs">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 border border-blue-400/20 flex items-center justify-center text-blue-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Capacity</span>
                                    <span class="font-extrabold text-slate-900">Up to {{ $unit->max_guests }} Guests</span>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/60 flex items-center gap-3 shadow-2xs">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center text-emerald-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">Security Deposit</span>
                                    <span class="font-extrabold text-slate-900 tabular-nums font-mono">₱{{ number_format($unit->advance_deposit_required, 0) }} (Refundable)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Amenities Checklist with Interactive Toggle -->
                        @if(!empty($unit->inventory_items))
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Included In-Unit Amenities</span>
                                    @if(count($unit->inventory_items) > 4)
                                        <button type="button" @click="showAllAmenities = !showAllAmenities"
                                                class="text-[11px] font-bold text-blue-600 hover:text-blue-700 transition-colors cursor-pointer">
                                            <span x-text="showAllAmenities ? 'Show less' : '+{{ count($unit->inventory_items) - 4 }} more'"></span>
                                        </button>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(array_slice($unit->inventory_items, 0, 4) as $item)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100/80 text-slate-700 border border-slate-200/50">
                                            <span class="text-emerald-500 font-bold">✓</span> {{ $item }}
                                        </span>
                                    @endforeach
                                    
                                    @if(count($unit->inventory_items) > 4)
                                        <div x-show="showAllAmenities" x-cloak
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 -translate-y-1"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             class="contents">
                                            @foreach(array_slice($unit->inventory_items, 4) as $extraItem)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                    <span class="text-blue-500 font-bold">✓</span> {{ $extraItem }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Pricing, Comparison & Primary CTA -->
                    <div class="pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums font-mono">
                                    ₱{{ number_format($unit->base_price_per_night, 0) }}
                                </span>
                                <span class="text-xs text-slate-500 font-medium">/ night</span>
                                <span class="text-xs text-slate-400 line-through">
                                    ₱{{ number_format($otaEstimatedPrice, 0) }} on Airbnb
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-extrabold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Save ~₱{{ number_format($estimatedSavings, 0) }} (5% lean fee)
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="focusUnit({{ $unit->id }}); toggleView('map');"
                                    class="hidden sm:inline-flex items-center gap-1 px-3 py-3 rounded-xl font-bold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all cursor-pointer"
                                    title="View location on map">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Locate</span>
                            </button>

                            <a href="{{ $unitBookingUrl }}"
                               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-xs sm:text-sm text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 hover:shadow-lg transition-all btn-press cursor-pointer">
                                <span>Check Availability</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-16 px-4 bg-white/90 backdrop-blur-xl rounded-3xl border border-slate-200 shadow-sm max-w-xl mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-2xl mx-auto mb-4">
                    🔍
                </div>
                <h3 class="text-lg font-black text-slate-900 mb-2">No units match your search filters</h3>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                    We couldn't find any available condominium units matching your selected dates, tower, or guest count. Try adjusting your dates or clearing filters.
                </p>
                <a href="{{ route('units.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/25 transition-all">
                    <span>Reset All Filters &amp; View All Units</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('interactiveMap');
    if (!mapElement) return;

    const units = @json($unitsMapData);

    // Center directly on Urban Deca Homes Ortigas between Buildings N & P
    const defaultCenter = [14.59239, 121.10229];
    const map = L.map('interactiveMap', {
        center: defaultCenter,
        zoom: 17,
        scrollWheelZoom: false,
    });

    window.directStayMap = map;

    // Standard OpenStreetMap tiles (100% Free, NO API key required)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Marker storage
    const markers = {};

    units.forEach(unit => {
        // Custom Airbnb-style Price Bubble Pin
        const priceIcon = L.divIcon({
            className: 'price-pin-wrapper',
            html: `<div class="map-price-marker" id="marker-${unit.id}">
                        <span>${unit.price_formatted}</span>
                   </div>`,
            iconSize: [80, 36],
            iconAnchor: [40, 36],
            popupAnchor: [0, -38]
        });

        const marker = L.marker([unit.lat, unit.lng], { icon: priceIcon }).addTo(map);
        markers[unit.id] = marker;

        // Custom Glassmorphic Popup
        const popupContent = `
            <div class="custom-map-popup w-64 text-left">
                ${unit.image ? `
                    <div class="relative h-32 w-full overflow-hidden">
                        <img src="${unit.image}" alt="${unit.title}" class="w-full h-full object-cover">
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-900/80 backdrop-blur-md text-white">
                            ${unit.building_code} &bull; ${unit.unit_number}
                        </span>
                    </div>
                ` : ''}
                <div class="p-3.5 space-y-2">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">${unit.building_name}</span>
                            <span class="text-xs font-bold text-amber-500">★ ${unit.rating}</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900 truncate mt-0.5">${unit.title}</h4>
                        <p class="text-[10px] text-slate-500 truncate">${unit.address}</p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-black text-slate-900 font-mono">${unit.price_formatted}</span>
                            <span class="text-[10px] text-slate-400">/night</span>
                        </div>
                        <a href="${unit.url}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-xs">
                            View Unit &rarr;
                        </a>
                    </div>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent, {
            maxWidth: 280,
            className: 'custom-map-popup'
        });

        // Hover effect: highlight marker and card
        marker.on('mouseover', function () {
            const el = document.getElementById(`marker-${unit.id}`);
            if (el) el.classList.add('is-selected');
        });

        marker.on('mouseout', function () {
            const el = document.getElementById(`marker-${unit.id}`);
            if (el) el.classList.remove('is-selected');
        });
    });

    // Global helper to focus a specific unit or zoom to fit all
    window.focusMapUnit = function (unitId) {
        if (unitId === 'all') {
            map.flyTo(defaultCenter, 16, { duration: 1 });
            return;
        }

        const marker = markers[unitId];
        if (marker) {
            map.flyTo(marker.getLatLng(), 18, { duration: 1.2 });
            marker.openPopup();

            // Scroll smoothly to unit card if visible
            const card = document.getElementById(`unitCard-${unitId}`);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    };
});
</script>
@endpush
