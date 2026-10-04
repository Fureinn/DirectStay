@extends('layouts.app')

@section('title', $unit->title . ' - DirectStay Ortigas')

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
    $photos = $unit->galleryImages();
    $featuredAmenities = $unit->featuredAmenities();
    $inventoryItems = $unit->inventory_items ?? [];
    $totalPhotos = count($photos);
@endphp

<!-- Main Container with Alpine.js state for Lightbox, Amenities Modal, and Reservation state -->
<div x-data="{
    galleryOpen: false,
    activePhotoIndex: 0,
    amenitiesModalOpen: false,
    showCalendar: false,
    breakdownOpen: true,
    copiedShare: false,
    openGallery(index = 0) {
        this.activePhotoIndex = index;
        this.galleryOpen = true;
    },
    nextPhoto() {
        this.activePhotoIndex = (this.activePhotoIndex + 1) % {{ max($totalPhotos, 1) }};
    },
    prevPhoto() {
        this.activePhotoIndex = (this.activePhotoIndex - 1 + {{ max($totalPhotos, 1) }}) % {{ max($totalPhotos, 1) }};
    },
    shareListing() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
            this.copiedShare = true;
            setTimeout(() => { this.copiedShare = false; }, 2500);
        }
    }
}"
@keydown.window.escape="galleryOpen = false; amenitiesModalOpen = false"
@keydown.window.arrow-left="if (galleryOpen) prevPhoto()"
@keydown.window.arrow-right="if (galleryOpen) nextPhoto()"
class="min-h-screen bg-slate-50/60 pb-20">

    <!-- ======================================================================= -->
    <!-- 1. TOP HEADER & BREADCRUMBS BAR                                         -->
    <!-- ======================================================================= -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-4">
        <!-- Top Breadcrumbs & Utility Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="{{ route('units.index') }}" class="hover:text-blue-600 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>All Staycations</span>
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-700">{{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }}</span>
                <span class="text-slate-300">/</span>
                <span class="text-blue-600 font-bold">Tower {{ $unit->building ? $unit->building->code : 'UDH' }} &bull; Unit {{ $unit->unit_number }}</span>
            </nav>

            <div class="flex items-center gap-2">
                <!-- Direct Booking Advantage Pill -->
                <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Zero Airbnb 15% Markup</span>
                </span>

                <!-- Share Button -->
                <button type="button" @click="shareListing()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    <span x-text="copiedShare ? 'Copied Link!' : 'Share'"></span>
                </button>
            </div>
        </div>

        <!-- Listing Main Title & Subtitle Meta -->
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                {{ $unit->title }}
            </h1>
            <div class="flex flex-wrap items-center gap-y-2 gap-x-4 mt-2 text-xs text-slate-600">
                <!-- Star Rating -->
                <a href="#reviewsSection" class="flex items-center gap-1.5 font-bold text-slate-900 hover:text-blue-600 transition-colors">
                    <span class="text-amber-500 text-sm">★</span>
                    <span class="tabular-nums">{{ number_format($unit->averageRating(), 1) }}</span>
                    <span class="text-slate-400 font-normal">({{ $unit->reviewsCount() }} reviews)</span>
                </a>

                <span class="text-slate-300">&bull;</span>

                <!-- Location link -->
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }} &bull; Tower {{ $unit->building ? $unit->building->code : 'UDH' }} &bull; Pasig City</span>
                </span>

                <span class="text-slate-300">&bull;</span>

                <!-- Instant Pass Badge -->
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                    Deca PMO Gate Pass Ready
                </span>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 2. AIRBNB-STYLE 5-PHOTO MOSAIC GRID                                     -->
    <!-- ======================================================================= -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="relative rounded-3xl overflow-hidden bg-slate-900 border border-slate-200/80 shadow-md">
            @if($totalPhotos >= 5)
                <!-- 5-Photo Mosaic Grid (1 large on left, 4 in 2x2 grid on right) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 h-[340px] sm:h-[420px] lg:h-[480px]">
                    <!-- Main Feature Photo (Takes 2 cols) -->
                    <div class="md:col-span-2 relative group overflow-hidden cursor-pointer h-full" @click="openGallery(0)">
                        <img src="{{ asset($photos[0]) }}" alt="{{ $unit->title }} - Main View"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                    </div>
                    <!-- 2nd Photo -->
                    <div class="hidden md:block relative group overflow-hidden cursor-pointer h-full" @click="openGallery(1)">
                        <img src="{{ asset($photos[1]) }}" alt="{{ $unit->title }} - Photo 2"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                    </div>
                    <!-- 3rd Photo -->
                    <div class="hidden md:block relative group overflow-hidden cursor-pointer h-full" @click="openGallery(2)">
                        <img src="{{ asset($photos[2]) }}" alt="{{ $unit->title }} - Photo 3"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                    </div>
                    <!-- 4th Photo -->
                    <div class="hidden md:block relative group overflow-hidden cursor-pointer h-full" @click="openGallery(3)">
                        <img src="{{ asset($photos[3]) }}" alt="{{ $unit->title }} - Photo 4"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                    </div>
                    <!-- 5th Photo -->
                    <div class="hidden md:block relative group overflow-hidden cursor-pointer h-full" @click="openGallery(4)">
                        <img src="{{ asset($photos[4]) }}" alt="{{ $unit->title }} - Photo 5"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                    </div>
                </div>
            @elseif($totalPhotos >= 2)
                <!-- 2 to 4 Photos Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 h-[320px] sm:h-[400px] lg:h-[460px]">
                    <div class="relative group overflow-hidden cursor-pointer h-full" @click="openGallery(0)">
                        <img src="{{ asset($photos[0]) }}" alt="{{ $unit->title }} - Main"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                    </div>
                    <div class="hidden md:grid grid-cols-1 {{ $totalPhotos >= 3 ? 'grid-rows-2' : '' }} gap-2 h-full">
                        <div class="relative group overflow-hidden cursor-pointer h-full" @click="openGallery(1)">
                            <img src="{{ asset($photos[1]) }}" alt="{{ $unit->title }} - Photo 2"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                        </div>
                        @if($totalPhotos >= 3)
                            <div class="relative group overflow-hidden cursor-pointer h-full" @click="openGallery(2)">
                                <img src="{{ asset($photos[2]) }}" alt="{{ $unit->title }} - Photo 3"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Single Panoramic Photo -->
                <div class="relative group overflow-hidden cursor-pointer h-[320px] sm:h-[420px] lg:h-[480px]" @click="openGallery(0)">
                    <img src="{{ asset($photos[0]) }}" alt="{{ $unit->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors"></div>
                </div>
            @endif

            <!-- Floating "Show all photos" Button -->
            <button type="button" @click="openGallery(0)"
                    class="absolute bottom-4 right-4 sm:bottom-5 sm:right-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/90 hover:bg-white text-slate-900 text-xs font-black tracking-tight shadow-lg backdrop-blur-md border border-white/60 transition-all transform hover:scale-102 cursor-pointer z-10">
                <svg class="w-4 h-4 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Show all {{ $totalPhotos }} photo{{ $totalPhotos === 1 ? '' : 's' }}</span>
            </button>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 3. MAIN CONTENT: 2-COLUMN SPLIT (LEFT DETAILS, RIGHT STICKY WIDGET)     -->
    <!-- ======================================================================= -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

            <!-- =================================================================== -->
            <!-- LEFT COLUMN (7 COLS): UNIT INTRO, PROCESS, AMENITIES, REVIEWS, MAP  -->
            <!-- =================================================================== -->
            <div class="lg:col-span-7 space-y-8">

                <!-- Unit Host & Core Specs Bento Card -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="flex items-center justify-between pb-6 border-b border-slate-100">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-wider text-blue-600 block mb-1">
                                Verified DirectStay Property
                            </span>
                            <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                                Hosted by {{ $unit->host ? $unit->host->name : 'DirectStay Superhost Team' }}
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Urban Deca Homes Ortigas PMO-Accredited Host &bull; Fast response in under 15 mins
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-blue-700 text-white font-black text-base flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                            {{ strtoupper(substr($unit->host ? $unit->host->name : 'DS', 0, 2)) }}
                        </div>
                    </div>

                    <!-- 4 Key Specs Strip -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 block mb-1 text-base">👥</span>
                            <span class="text-xs font-black text-slate-900 block">{{ $unit->max_guests }} Guests</span>
                            <span class="text-[10px] text-slate-400">Max capacity</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 block mb-1 text-base">🛏️</span>
                            <span class="text-xs font-black text-slate-900 block">Loft &amp; Queen</span>
                            <span class="text-[10px] text-slate-400">Sleeping setup</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 block mb-1 text-base">🚿</span>
                            <span class="text-xs font-black text-slate-900 block">1 Bath</span>
                            <span class="text-[10px] text-slate-400">Private hot shower</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-slate-400 block mb-1 text-base">🔑</span>
                            <span class="text-xs font-black text-slate-900 block">Self Check-in</span>
                            <span class="text-[10px] text-slate-400">Smartlock &amp; Pass</span>
                        </div>
                    </div>
                </div>

                <!-- =============================================================== -->
                <!-- DIRECTSTAY 3-STEP SEAMLESS GATE PASS & BOOKING JOURNEY          -->
                <!-- =============================================================== -->
                <div class="bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-7 text-white shadow-xl relative overflow-hidden">
                    <!-- Subtle background glow -->
                    <div class="absolute -top-12 -right-12 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                                Hassle-Free Arrival
                            </span>
                            <span class="text-xs text-blue-200/80 font-medium">Urban Deca Homes Ortigas PMO Process</span>
                        </div>
                        <h3 class="text-lg sm:text-xl font-black text-white tracking-tight mb-2">
                            How Your DirectStay Reservation Works
                        </h3>
                        <p class="text-xs text-slate-300 mb-6 max-w-xl leading-relaxed">
                            No confusing paperwork or security hold-ups at the gate. We automate the Pasig PMO gate pass clearance so your stay is 100% compliant and ready on arrival.
                        </p>

                        <!-- 3-Step Illustrated Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <!-- Step 1 -->
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 flex flex-col justify-between">
                                <div>
                                    <div class="w-8 h-8 rounded-xl bg-blue-500 text-white font-black text-xs flex items-center justify-center mb-3 shadow-md">
                                        1
                                    </div>
                                    <h4 class="text-xs font-black text-white mb-1">Instant Direct Booking</h4>
                                    <p class="text-[11px] text-slate-300 leading-normal">
                                        Select your dates and lock your staycation with transparent pricing and escrow deposit protection.
                                    </p>
                                </div>
                                <span class="text-[10px] text-emerald-300 font-bold mt-3 block">✓ Zero OTA Markups</span>
                            </div>

                            <!-- Step 2 -->
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 flex flex-col justify-between">
                                <div>
                                    <div class="w-8 h-8 rounded-xl bg-indigo-500 text-white font-black text-xs flex items-center justify-center mb-3 shadow-md">
                                        2
                                    </div>
                                    <h4 class="text-xs font-black text-white mb-1">ID Upload &amp; Clearance</h4>
                                    <p class="text-[11px] text-slate-300 leading-normal">
                                        Submit government IDs securely online. DirectStay generates your official Deca Gate Pass PDF.
                                    </p>
                                </div>
                                <span class="text-[10px] text-blue-300 font-bold mt-3 block">✓ PMO Security Approved</span>
                            </div>

                            <!-- Step 3 -->
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 flex flex-col justify-between">
                                <div>
                                    <div class="w-8 h-8 rounded-xl bg-teal-500 text-white font-black text-xs flex items-center justify-center mb-3 shadow-md">
                                        3
                                    </div>
                                    <h4 class="text-xs font-black text-white mb-1">Express Check-in</h4>
                                    <p class="text-[11px] text-slate-300 leading-normal">
                                        Present your QR pass at the Ortigas Ave Ext gate and enter your unit with digital smartlock PIN.
                                    </p>
                                </div>
                                <span class="text-[10px] text-teal-300 font-bold mt-3 block">✓ Keyless &amp; Seamless</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unit Description Card -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="text-base font-black text-slate-900 tracking-tight">About this space</h3>
                    <div class="text-sm text-slate-600 leading-relaxed space-y-3">
                        <p>{{ $unit->description }}</p>
                    </div>
                </div>

                <!-- =============================================================== -->
                <!-- ICON-BACKED FEATURED AMENITIES                                 -->
                <!-- =============================================================== -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Featured Amenities</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Everything you need for a comfortable staycation or workation</p>
                        </div>
                        @if(count($inventoryItems) > 6)
                            <button type="button" @click="amenitiesModalOpen = true"
                                    class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors cursor-pointer">
                                View all ({{ count($inventoryItems) }})
                            </button>
                        @endif
                    </div>

                    <!-- 6 Highlighted Amenity Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach($featuredAmenities as $amenity)
                            <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 hover:bg-slate-50 transition-all">
                                <div class="w-9 h-9 rounded-xl bg-blue-100/70 text-blue-700 flex items-center justify-center shrink-0">
                                    @if($amenity['icon'] === 'snowflake')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m0-18l-3 3m3-3l3 3m-3 15l-3-3m3 3l3-3m-9-6h18m-18 0l3-3m-3 3l3 3m15-3l-3-3m3 3l3 3"/>
                                        </svg>
                                    @elseif($amenity['icon'] === 'wifi')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                        </svg>
                                    @elseif($amenity['icon'] === 'tv')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    @elseif($amenity['icon'] === 'kitchen')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    @elseif($amenity['icon'] === 'fridge')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4a2 2 0 012-2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4zm0 6h14M8 6v2m0 6v3"/>
                                        </svg>
                                    @elseif($amenity['icon'] === 'washer')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16a2 2 0 002 2h12a2 2 0 002-2V4a2 2 0 00-2-2H6a2 2 0 00-2 2zm8 14a5 5 0 100-10 5 5 0 000 10zm0-3a2 2 0 100-4 2 2 0 000 4z"/>
                                        </svg>
                                    @elseif($amenity['icon'] === 'bed')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4v-4m14 4v-4M4 6a2 2 0 012-2h12a2 2 0 012 2v4H4V6z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-900">{{ $amenity['label'] }}</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $amenity['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($inventoryItems))
                        <div class="pt-3">
                            <button type="button" @click="amenitiesModalOpen = true"
                                    class="w-full py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200/80 text-xs font-black text-slate-800 transition-all cursor-pointer flex items-center justify-center gap-2">
                                <span>Show all {{ count($inventoryItems) }} amenities &amp; unit inventory</span>
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Building Security House Rules Policy -->
                <div class="p-6 rounded-3xl bg-amber-50/70 border border-amber-200/80 text-xs text-slate-800 relative">
                    <div class="flex items-center gap-2 font-black text-sm text-amber-950 mb-2">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Urban Deca Homes Ortigas PMO Gate Pass &amp; House Rules
                    </div>
                    <p class="text-slate-700 leading-relaxed font-sans mb-3">
                        {{ $unit->building ? $unit->building->house_rules : 'All guests must submit valid government-issued IDs for security gate pass clearance prior to building entry.' }}
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center pt-2 border-t border-amber-200/60 font-semibold text-[11px] text-amber-900">
                        <div>Check-in: <strong>2:00 PM</strong></div>
                        <div>Check-out: <strong>12:00 PM</strong></div>
                        <div>Quiet: <strong>10:00 PM</strong></div>
                        <div>Strictly: <strong>No Smoking</strong></div>
                    </div>
                </div>

                <!-- Guest Ratings & Verified Reviews Section -->
                <div id="reviewsSection" class="rounded-3xl bg-white border border-slate-200/80 shadow-xs p-6 sm:p-7 relative space-y-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2.5 mb-1">
                                <span class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-1.5 tabular-nums">
                                    <span class="text-amber-500 text-2xl">★</span>
                                    <span>{{ number_format($unit->averageRating(), 1) }}</span>
                                </span>
                                <span class="text-xs font-black uppercase tracking-wider px-3 py-1 rounded-full bg-amber-50 text-amber-900 border border-amber-200">
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
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-amber-950 bg-amber-300 hover:bg-amber-400 shadow-xs transition-all cursor-pointer">
                                    <span>★ Rate Your Stay</span>
                                </button>
                            @endif
                        @endauth
                    </div>

                    <!-- Rating Criteria Breakdown Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
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
                <div class="rounded-3xl bg-white border border-slate-200/80 shadow-xs p-6 sm:p-7 relative overflow-hidden space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Location &amp; Pasig Neighborhood Guide
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas' }} &bull; Tower {{ $unit->building ? $unit->building->code : 'UDH' }} &bull; Unit {{ $unit->unit_number }}
                            </p>
                        </div>

                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(($unit->building ? $unit->building->name : 'Urban Deca Homes Ortigas') . ' ' . ($unit->building->address ?? 'KM 19 Ortigas Avenue Extension Pasig City')) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-blue-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <span>Google Maps</span>
                        </a>
                    </div>

                    <!-- Unit Location Leaflet Map Container -->
                    <div id="unitLocationMap" class="w-full h-72 sm:h-80 rounded-2xl border border-slate-200 shadow-inner relative z-10"></div>

                    <!-- Neighborhood Transit & Distances Guide -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs pt-1">
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 block font-semibold">Deca Main Gate</span>
                            <strong class="text-slate-900 block truncate">Ortigas Ave Extension</strong>
                            <span class="text-[10px] text-blue-600 font-bold">Pass Verification Point</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 block font-semibold">Commercial Hub</span>
                            <strong class="text-slate-900 block truncate">SM City East Ortigas</strong>
                            <span class="text-[10px] text-emerald-600 font-bold">1.2 km &bull; 5 mins</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-[10px] text-slate-400 block font-semibold">Lifestyle Estate</span>
                            <strong class="text-slate-900 block truncate">Bridgetowne / Victor</strong>
                            <span class="text-[10px] text-emerald-600 font-bold">2.5 km &bull; 8 mins</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- =================================================================== -->
            <!-- RIGHT COLUMN (5 COLS): STICKY RESERVATION & INSTANT PRICING WIDGET  -->
            <!-- =================================================================== -->
            <div class="lg:col-span-5" id="reservationWidget">
                <div class="sticky top-24 bg-white/95 backdrop-blur-xl rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-7 relative overflow-hidden">
                    <!-- Soft decorative gradient corner -->
                    <div class="absolute -top-16 -right-16 w-44 h-44 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    <!-- Price Header -->
                    <div class="flex items-baseline justify-between mb-5">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-3xl font-black text-slate-900 tabular-nums tracking-tight font-mono">
                                ₱{{ number_format($unit->base_price_per_night, 0) }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">/ night</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-700 font-semibold bg-slate-50 border border-slate-200 px-3 py-1 rounded-full shadow-2xs">
                            <span class="text-amber-500 font-bold">★</span>
                            <span class="tabular-nums font-bold">{{ number_format($unit->averageRating(), 1) }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <a href="#reviewsSection" class="text-slate-600 hover:text-slate-900 underline">{{ $unit->reviewsCount() }} reviews</a>
                        </div>
                    </div>

                    <!-- Booking Form -->
                    <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm" class="space-y-4">
                        @csrf
                        <input type="hidden" name="unit_id" value="{{ $unit->id }}">

                        <!-- DirectStay Guarantee Banner -->
                        <div class="rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 p-3.5 text-xs">
                            <div class="flex items-center gap-2 font-black text-blue-950">
                                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                                <span>Pasig PMO Gate Pass Included</span>
                            </div>
                            <p class="text-[11px] text-blue-800/80 mt-1 leading-normal">
                                Automated digital clearance issued upon ID confirmation. Escrow holds deposit safely.
                            </p>
                        </div>

                        <!-- Date Picker Toggle & Header -->
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-[11px] font-black uppercase tracking-wider text-slate-500">Stay Dates</span>
                            <button type="button" @click="showCalendar = !showCalendar"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="showCalendar ? 'Close Calendar' : 'View Calendar'"></span>
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
                             class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs relative shadow-xs">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-bold text-slate-900" id="calMonthTitle">{{ date('F Y') }}</span>
                                <div class="flex items-center gap-2.5 text-[10px] font-semibold text-slate-500">
                                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> Open</span>
                                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-slate-300"></span> Booked</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-center font-bold text-[10px] text-slate-400 mb-1">
                                <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                            </div>
                            <div id="interactiveCalendarDays" class="grid grid-cols-7 gap-1 text-center text-xs">
                                <!-- Populated dynamically by JS -->
                            </div>
                        </div>

                        <!-- Grouped Date/Guest Picker Box -->
                        <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-2xs focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-500/20 transition-all bg-white">
                            <div class="grid grid-cols-2 divide-x divide-slate-200 border-b border-slate-200">
                                <!-- Check-in -->
                                <div class="p-3 hover:bg-slate-50/80 transition cursor-pointer">
                                    <label for="checkInInput" class="block text-[9px] font-black uppercase tracking-wider text-slate-500">Check-in</label>
                                    <input type="date" name="check_in_date" id="checkInInput"
                                           min="{{ date('Y-m-d') }}"
                                           value="{{ old('check_in_date', $defaultCheckIn ?? date('Y-m-d')) }}"
                                           required
                                           class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer tabular-nums tracking-tight font-mono">
                                </div>
                                <!-- Checkout -->
                                <div class="p-3 hover:bg-slate-50/80 transition cursor-pointer">
                                    <label for="checkOutInput" class="block text-[9px] font-black uppercase tracking-wider text-slate-500">Checkout</label>
                                    <input type="date" name="check_out_date" id="checkOutInput"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           value="{{ old('check_out_date', $defaultCheckOut ?? date('Y-m-d', strtotime('+1 day'))) }}"
                                           required
                                           class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer tabular-nums tracking-tight font-mono">
                                </div>
                            </div>

                            <!-- Guests Selector -->
                            <div class="p-3 hover:bg-slate-50/80 transition">
                                <label for="guestCountSelect" class="block text-[9px] font-black uppercase tracking-wider text-slate-500">Guests</label>
                                <select name="guest_count" id="guestCountSelect"
                                        class="w-full text-xs font-bold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                    @for($i = 1; $i <= $unit->max_guests; $i++)
                                        <option value="{{ $i }}" {{ old('guest_count', $defaultGuests ?? 1) == $i ? 'selected' : '' }}>
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
                            <span>Selected dates are booked. Please pick other dates.</span>
                        </div>

                        <!-- Step 2: Lead guest details (revealed after clicking Reserve or if validation errors exist) -->
                        <div id="confirmAndPaySection" class="{{ ($errors->any() || old('guest_name')) ? '' : 'hidden' }} space-y-3.5 pt-3 border-t border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Lead Guest Details</h3>
                                @auth
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Logged in</span>
                                @else
                                    <a href="{{ route('customer.login') }}" class="text-[10px] text-blue-600 hover:text-blue-800 font-bold underline">Quick Login</a>
                                @endauth
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Full Legal Name</label>
                                    <input type="text" name="guest_name" id="guestNameInput"
                                           value="{{ old('guest_name', auth()->check() ? auth()->user()->name : '') }}"
                                           placeholder="Must match government ID for Deca Gate Pass" required
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
                                               placeholder="For PDF pass" required
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

                            <!-- Optional Stay Add-ons -->
                            @if($addOns->count() > 0)
                                <div class="pt-2">
                                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-800 mb-2">Optional Stay Enhancements</label>
                                    <div class="space-y-2">
                                        @foreach($addOns as $addon)
                                            <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-xs">
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

                        <!-- Action Buttons -->
                        <div class="pt-2">
                            <button type="button" id="reserveStepBtn" onclick="handleReserveClick()"
                                    class="{{ ($errors->any() || old('guest_name')) ? 'hidden' : '' }} w-full py-3.5 px-4 rounded-2xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span id="btnText">Reserve Unit</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>

                            <button type="submit" id="confirmAndPayBtn"
                                    class="{{ ($errors->any() || old('guest_name')) ? '' : 'hidden' }} w-full py-3.5 px-4 rounded-2xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/25 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span>Proceed to Security Clearance</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            <p class="text-[11px] text-center text-slate-400 mt-2 font-medium" id="notChargedNotice">
                                No instant card charge. Clear ID verification on the next step.
                            </p>
                        </div>

                        <!-- Dynamic Price Summary & Itemized Breakdown -->
                        <div class="pt-4 border-t border-slate-200 space-y-3">
                            <div class="flex justify-between items-baseline">
                                <div>
                                    <span class="text-sm font-black text-slate-900">Total Amount</span>
                                    <button type="button" @click="breakdownOpen = !breakdownOpen"
                                            class="block text-[11px] font-bold text-blue-600 hover:text-blue-800 transition-colors mt-0.5 cursor-pointer">
                                        <span x-text="breakdownOpen ? 'Hide breakdown ▲' : 'Show breakdown ▼'"></span>
                                    </button>
                                </div>
                                <span class="text-2xl font-black text-slate-900 tabular-nums tracking-tight font-mono" id="calcGrandTotal">₱0.00</span>
                            </div>

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
                                        <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Save 10% vs OTA</span>
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

    <!-- ======================================================================= -->
    <!-- 4. FULL-SCREEN LIGHTBOX GALLERY MODAL (ALPINE.JS)                       -->
    <!-- ======================================================================= -->
    <div x-show="galleryOpen" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-xl flex flex-col justify-between p-4 sm:p-6 text-white">

        <!-- Top Header Bar -->
        <div class="flex items-center justify-between gap-4 max-w-7xl w-full mx-auto pb-4">
            <div class="flex items-center gap-3">
                <span class="text-xs font-black uppercase tracking-wider text-blue-400 bg-blue-950/80 px-3 py-1 rounded-full border border-blue-800/60">
                    {{ $unit->building ? $unit->building->code : 'UDH' }} &bull; Unit {{ $unit->unit_number }}
                </span>
                <span class="text-xs font-semibold text-slate-300 truncate max-w-xs sm:max-w-md">
                    {{ $unit->title }}
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-mono font-bold text-slate-400 bg-white/10 px-3 py-1 rounded-full">
                    <span x-text="activePhotoIndex + 1"></span> of {{ $totalPhotos }}
                </span>
                <button type="button" @click="galleryOpen = false"
                        class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Center Image Display with Prev/Next Controls -->
        <div class="relative flex-1 flex items-center justify-center max-w-6xl w-full mx-auto my-auto overflow-hidden">
            <!-- Prev Button -->
            <button type="button" @click="prevPhoto()"
                    class="absolute left-2 sm:left-4 z-20 w-11 h-11 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center backdrop-blur-md border border-white/20 transition-all cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <!-- Active Photo Display -->
            <div class="max-h-[72vh] flex items-center justify-center">
                @foreach($photos as $idx => $photo)
                    <img x-show="activePhotoIndex === {{ $idx }}"
                         src="{{ asset($photo) }}"
                         alt="{{ $unit->title }} - Photo {{ $idx + 1 }}"
                         class="max-h-[72vh] max-w-full object-contain rounded-2xl shadow-2xl transition-opacity duration-300"
                         loading="lazy">
                @endforeach
            </div>

            <!-- Next Button -->
            <button type="button" @click="nextPhoto()"
                    class="absolute right-2 sm:right-4 z-20 w-11 h-11 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center backdrop-blur-md border border-white/20 transition-all cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Bottom Thumbnails Strip -->
        <div class="max-w-4xl w-full mx-auto pt-4 overflow-x-auto flex items-center justify-center gap-2">
            @foreach($photos as $idx => $photo)
                <button type="button" @click="activePhotoIndex = {{ $idx }}"
                        class="h-14 w-20 rounded-xl overflow-hidden border-2 transition-all shrink-0 cursor-pointer"
                        :class="activePhotoIndex === {{ $idx }} ? 'border-blue-500 ring-2 ring-blue-500/50 scale-105' : 'border-white/20 opacity-50 hover:opacity-100'">
                    <img src="{{ asset($photo) }}" alt="Thumb {{ $idx + 1 }}" class="w-full h-full object-cover">
                </button>
            @endforeach
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 5. ALL AMENITIES & INVENTORY MODAL (ALPINE.JS)                          -->
    <!-- ======================================================================= -->
    <div x-show="amenitiesModalOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-4">
        
        <div class="bg-white rounded-3xl max-w-xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-slate-100 overflow-hidden"
             @click.away="amenitiesModalOpen = false">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-900">What this place offers</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Complete inventory and furnishings in Unit {{ $unit->unit_number }}</p>
                </div>
                <button type="button" @click="amenitiesModalOpen = false"
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-3">
                @if(!empty($inventoryItems))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($inventoryItems as $item)
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100 text-xs font-semibold text-slate-800">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 text-center py-4">No specific inventory items detailed.</p>
                @endif
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="button" @click="amenitiesModalOpen = false"
                        class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Mobile Airbnb-Style Fixed Bottom Floating Bar -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-3 flex items-center justify-between shadow-lg">
    <div>
        <div class="flex items-baseline gap-1">
            <span class="text-lg font-black text-slate-900 tabular-nums tracking-tight font-mono">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
            <span class="text-xs text-slate-500">/ night</span>
        </div>
        <div class="text-[11px] font-semibold text-blue-600 underline" id="mobileDateSummary">
            Select dates
        </div>
    </div>
    <button type="button" onclick="scrollAndOpenReserve()"
            class="px-6 py-2.5 rounded-2xl font-bold text-sm text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25">
        Reserve
    </button>
</div>

<!-- ======================================================================= -->
<!-- REVIEW MODAL & JAVASCRIPT LOGIC                                         -->
<!-- ======================================================================= -->
<div id="reviewModal" class="hidden opacity-0 fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-4 transition-all duration-300">
    <div id="reviewModalBox" class="bg-white rounded-3xl w-full max-w-lg overflow-hidden transition-all duration-300 transform scale-95 relative shadow-2xl border border-slate-100">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">Rate Your Stay</span>
                <h3 class="text-base font-bold text-slate-900 mt-1" id="reviewModalUnitTitle">Review Stay</h3>
                <p class="text-xs text-slate-400 font-mono" id="reviewModalBookingCode"></p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-all cursor-pointer">
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
                <button type="button" onclick="closeReviewModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer">
                    Publish Review
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const basePrice = {{ $unit->base_price_per_night }};
    const advanceDeposit = {{ $unit->advance_deposit_required }};
    const platformFeeRate = 0.05; // 5% Guest Platform Fee
    const blockedDates = @json($blockedDates);

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
