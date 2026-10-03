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
    $unitsMapData = ($allUnits ?? $units)->map(function ($u) {
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
            'url' => route('units.show', $u),
        ];
    });
@endphp

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10"
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

    <!-- Hero Section with Ambient Glassmorphism -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900/90 via-slate-850/90 to-blue-950/90 backdrop-blur-2xl text-white p-6 sm:p-10 lg:p-12 mb-10 shadow-2xl border border-white/20">
        <div class="ambient-glow w-96 h-96 bg-blue-500/20 -right-20 -top-20 glow-animate blob-animate"></div>
        <div class="ambient-glow w-96 h-96 bg-emerald-500/15 -left-20 -bottom-20 glow-animate"></div>
        
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-xs font-semibold uppercase tracking-wider mb-4 backdrop-blur-md shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 status-beacon"></span>
                Official Condominium Direct-Booking Engine
            </div>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white mb-4 leading-tight">
                Urban Deca Homes Ortigas Direct Bookings.
            </h1>
            <p class="text-slate-300 text-sm sm:text-base lg:text-lg mb-6 leading-relaxed">
                Stay at <strong>Urban Deca Homes Ortigas (Pasig City)</strong> directly with verified unit owners. Enjoy 0% host commission, lean <strong class="text-blue-300">5% guest platform fee</strong> (saving an average of ₱276 per stay vs Airbnb), secure GCash processing, and automated building gate pass clearance.
            </p>

            <div class="flex flex-wrap items-center gap-3 text-xs font-medium text-slate-300">
                <div class="flex items-center gap-2 bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-md border border-white/15 shadow-xs">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>KM 19 Ortigas Ave Ext, Brgy. Rosario, Pasig</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-md border border-white/15 shadow-xs">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>Deca Gate Pass Certified (Bldgs N & P)</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-md border border-white/15 shadow-xs">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>₱1,000 Refundable Security Deposit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & View Switcher Bar -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black tracking-tight text-slate-900">Featured Units in Ortigas, Pasig</h2>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Explore available direct units across Building N and Building P.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            <!-- Building Filter Navigation (Glass Capsule) -->
            <div class="inline-flex p-1.5 rounded-2xl bg-white/50 backdrop-blur-xl border border-white/60 shadow-[0_4px_20px_rgb(0,0,0,0.04)]">
                <a href="{{ route('units.index') }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all btn-press {{ empty($selectedBuilding) ? 'bg-white text-blue-700 shadow-sm border border-slate-200/50' : 'text-slate-600 hover:text-slate-900' }}">
                    All ({{ $totalUnitsCount ?? $units->count() }})
                </a>
                @foreach($buildings as $b)
                    <a href="{{ route('units.index', ['building' => $b->code]) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all btn-press {{ $selectedBuilding === $b->code ? 'bg-white text-blue-700 shadow-sm border border-slate-200/50' : 'text-slate-600 hover:text-slate-900' }}">
                        {{ $b->code }} ({{ $b->units_count }})
                    </a>
                @endforeach
            </div>

            <!-- View Switcher (Cards / Map & Prices / Split) -->
            <div class="inline-flex p-1.5 rounded-2xl bg-slate-200/60 backdrop-blur-xl border border-white/60 shadow-inner">
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

    <!-- Interactive Map Section (Shown in 'map' or 'split' view) -->
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

    <!-- Units Grid (Visible in 'grid' or 'split' view) -->
    <div x-show="viewMode === 'grid' || viewMode === 'split'"
         class="stagger-enter grid grid-cols-1 lg:grid-cols-2 gap-8">
        @forelse($units as $unit)
            <div x-data="{ saved: false, showAllAmenities: false }"
                 id="unitCard-{{ $unit->id }}"
                 :class="selectedUnitId === {{ $unit->id }} ? 'ring-2 ring-blue-500 shadow-xl' : ''"
                 class="glass-surface hover-lift flex flex-col rounded-3xl border border-white/60 overflow-hidden group shadow-[0_8px_30px_rgb(0,0,0,0.06)] relative transition-all duration-300">
                
                <!-- Ambient Subtle Glow for each card -->
                <div class="ambient-glow w-48 h-48 bg-blue-500/10 -top-12 -right-12 glow-animate"></div>

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
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-900/70 backdrop-blur-xl text-white border border-white/20 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            {{ $unit->building ? $unit->building->name : 'Urban Deca Homes' }}
                        </span>

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
                                    class="w-8 h-8 rounded-full bg-slate-900/60 hover:bg-slate-900/80 backdrop-blur-xl flex items-center justify-center text-white border border-white/20 transition-all btn-press">
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
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-600/90 backdrop-blur-md text-white border border-blue-400/30 shadow-xs">
                                Unit {{ $unit->unit_number }}
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

                <!-- Unit Details Body with Translucent Styling -->
                <div class="p-6 flex-1 flex flex-col justify-between relative z-10">
                    <div>
                        <p class="text-xs sm:text-sm text-slate-600 mb-5 leading-relaxed line-clamp-3">
                            {{ $unit->description }}
                        </p>

                        <!-- Key Specs Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-5 text-xs">
                            <div class="p-3.5 bg-white/50 backdrop-blur-md rounded-2xl border border-white/60 flex items-center gap-3 shadow-xs">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 border border-blue-400/20 flex items-center justify-center text-blue-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px] font-medium">Capacity</span>
                                    <span class="font-bold text-slate-900">Up to {{ $unit->max_guests }} Guests</span>
                                </div>
                            </div>

                            <div class="p-3.5 bg-white/50 backdrop-blur-md rounded-2xl border border-white/60 flex items-center gap-3 shadow-xs">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center text-emerald-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px] font-medium">Security Deposit</span>
                                    <span class="font-bold text-slate-900 tabular-nums font-mono">₱{{ number_format($unit->advance_deposit_required, 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Amenities Checklist with Interactive Toggle -->
                        @if(!empty($unit->inventory_items))
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Included Amenities</span>
                                    @if(count($unit->inventory_items) > 4)
                                        <button type="button" @click="showAllAmenities = !showAllAmenities"
                                                class="text-[11px] font-bold text-blue-600 hover:text-blue-700 transition-colors cursor-pointer">
                                            <span x-text="showAllAmenities ? 'Show less' : '+{{ count($unit->inventory_items) - 4 }} more'"></span>
                                        </button>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(array_slice($unit->inventory_items, 0, 4) as $item)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white/60 text-slate-700 border border-white/60 backdrop-blur-sm">
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
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-blue-500/10 text-blue-700 border border-blue-400/30 backdrop-blur-sm">
                                                    <span class="text-blue-500 font-bold">✓</span> {{ $extraItem }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Pricing & Primary CTA -->
                    <div class="pt-5 border-t border-white/40 flex items-center justify-between gap-4">
                        <div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl sm:text-3xl font-black text-slate-900 tabular-nums font-mono">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
                                <span class="text-xs text-slate-500 font-medium">night</span>
                            </div>
                            <span class="text-[11px] text-emerald-600 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                0% Host Markup &bull; Direct Booking
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="focusUnit({{ $unit->id }}); toggleView('map');"
                                    class="hidden sm:inline-flex items-center gap-1 px-3 py-3 rounded-xl font-bold text-xs text-slate-700 bg-white/70 hover:bg-white border border-white/80 shadow-xs transition-all cursor-pointer"
                                    title="View location on map">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Locate</span>
                            </button>

                            <a href="{{ route('units.show', $unit) }}"
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
            <div class="col-span-2 text-center py-16 glass-surface rounded-3xl border border-white/60">
                <p class="text-slate-500 text-sm">No units found matching this building filter.</p>
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
