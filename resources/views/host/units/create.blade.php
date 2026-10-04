@extends('layouts.app')

@section('title', 'Add New Unit - DirectStay Host')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
        selectedBuildingId: '{{ old('building_id', $buildings->first()->id ?? '') }}',
        title: '{{ old('title', '') }}',
        unitNumber: '{{ old('unit_number', '') }}',
        basePrice: parseFloat('{{ old('base_price_per_night', '1500') }}') || 1500,
        deposit: parseFloat('{{ old('advance_deposit_required', '1000') }}') || 1000,
        maxGuests: parseInt('{{ old('max_guests', '4') }}') || 4,
        description: `{{ old('description', '') }}`,
        
        // Amenity chip state
        popularAmenities: [
            { id: 'ac', label: 'Inverter Split-Type Aircon', icon: '❄️' },
            { id: 'wifi', label: 'High-Speed Fiber WiFi 100Mbps', icon: '📶' },
            { id: 'tv', label: '55-inch Smart TV with Netflix', icon: '📺' },
            { id: 'induction', label: 'Induction Cooker & Range Hood', icon: '🍳' },
            { id: 'fridge', label: '2-Door Refrigerator', icon: '🧊' },
            { id: 'microwave', label: 'Microwave Oven', icon: '♨️' },
            { id: 'shower', label: 'Heated Shower', icon: '🚿' },
            { id: 'pool', label: 'Deca Swimming Pool Access', icon: '🏊' },
            { id: 'washer', label: 'In-Unit Washing Machine', icon: '🧺' },
            { id: 'bed', label: 'Queen Bed & Double Loft Setup', icon: '🛏️' },
            { id: 'kettle', label: 'Electric Kettle & Coffee Nook', icon: '☕' },
            { id: 'balcony', label: 'Scenic Balcony View', icon: '🪟' },
            { id: 'smartlock', label: 'Digital Smartlock Keyless Entry', icon: '🔑' },
            { id: 'linens', label: 'Fresh Hotel-Grade Linens & Towels', icon: '🧴' },
            { id: 'cookware', label: 'Complete Cookware & Dining Utensils', icon: '🍽️' },
            { id: 'iron', label: 'Steam Iron & Hair Dryer', icon: '💨' }
        ],
        selectedAmenities: [
            'Inverter Split-Type Aircon',
            'High-Speed Fiber WiFi 100Mbps',
            '55-inch Smart TV with Netflix',
            'Induction Cooker & Range Hood',
            '2-Door Refrigerator',
            'Microwave Oven',
            'Heated Shower',
            'Deca Swimming Pool Access',
            'Fresh Hotel-Grade Linens & Towels'
        ],
        customAmenityInput: '',

        toggleAmenity(label) {
            const index = this.selectedAmenities.indexOf(label);
            if (index > -1) {
                this.selectedAmenities.splice(index, 1);
            } else {
                this.selectedAmenities.push(label);
            }
        },

        addCustomAmenity() {
            const item = this.customAmenityInput.trim();
            if (item && !this.selectedAmenities.includes(item)) {
                this.selectedAmenities.push(item);
                this.customAmenityInput = '';
            }
        },

        removeAmenity(label) {
            const index = this.selectedAmenities.indexOf(label);
            if (index > -1) {
                this.selectedAmenities.splice(index, 1);
            }
        },

        get inventoryItemsString() {
            return this.selectedAmenities.join(', ');
        },

        // Title suggestions
        applyTitleSuggestion(sug) {
            this.title = sug;
        },

        // Live Photo Previews
        coverPreviewUrl: null,
        galleryPreviews: [],

        handleCoverChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.coverPreviewUrl = URL.createObjectURL(file);
            }
        },

        handleGalleryChange(event) {
            const files = Array.from(event.target.files);
            this.galleryPreviews = files.map(file => ({
                name: file.name,
                url: URL.createObjectURL(file),
                size: (file.size / (1024 * 1024)).toFixed(2)
            }));
        },

        // Financial calculations
        get guestPlatformFee() {
            return Math.round(this.basePrice * 0.05 * 100) / 100;
        },
        get guestTotalPerNight() {
            return Math.round((this.basePrice + this.guestPlatformFee) * 100) / 100;
        },
        get airbnbComparativeGuestPrice() {
            return Math.round((this.basePrice * 1.15) * 100) / 100;
        },
        get monthlyProjectedEarnings() {
            return Math.round(this.basePrice * 20); // 20 nights occupancy estimate
        }
     }">

    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <a href="{{ route('host.units.index') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Host Inventory</span>
            </a>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Urban Deca Homes Ortigas &bull; New Listing Creator</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    Add New Condominium Unit
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Configure your condo specifications, select tower amenities, calculate payouts, and upload high-resolution gallery photos.
                </p>
            </div>

            <!-- Host Advantage Callout -->
            <div class="bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 rounded-2xl p-3 px-4 text-xs text-blue-900 dark:text-blue-200 shrink-0">
                <div class="font-extrabold flex items-center gap-1.5">
                    <span>⚡ 0% Host Commission</span>
                </div>
                <p class="text-[11px] text-blue-700 dark:text-blue-300 mt-0.5">
                    You keep 100% of your nightly base rate.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Add Unit Form -->
    <form action="{{ route('host.units.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        <!-- Synced Hidden Input for Inventory Items -->
        <input type="hidden" name="inventory_items" :value="inventoryItemsString">

        <!-- =================================================================== -->
        <!-- BENTO CARD 1: TOWER SELECTION & IDENTITY                            -->
        <!-- =================================================================== -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shadow-md shadow-blue-500/25">1</span>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Tower &amp; Unit Identity</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Select which building tower your condominium belongs to</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Step 1 of 4</span>
            </div>

            <!-- Visual Tower Cards Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                    Building / Complex Tower <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    @foreach($buildings as $building)
                        <label class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-blue-400 dark:hover:border-blue-600"
                               :class="selectedBuildingId == '{{ $building->id }}' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/40 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50'">
                            <input type="radio" name="building_id" value="{{ $building->id }}"
                                   x-model="selectedBuildingId" class="sr-only" required>
                            
                            <div class="flex items-start gap-3 w-full">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                                    {{ $building->code }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xs font-black text-slate-900 dark:text-white truncate">
                                            {{ $building->name }}
                                        </h3>
                                        <span x-show="selectedBuildingId == '{{ $building->id }}'" class="text-blue-600 dark:text-blue-400 text-xs font-extrabold">✓ Active</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                                        {{ $building->address }}
                                    </p>
                                    <span class="inline-block mt-2 text-[10px] font-bold px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        Official PMO Gate Pass Zone
                                    </span>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('building_id')
                    <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Unit Number -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Unit Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="unit_number" x-model="unitNumber"
                           placeholder="e.g. Unit N412 or Unit P718" required
                           class="w-full text-xs px-3.5 py-2.5 rounded-2xl border @error('unit_number') border-rose-300 bg-rose-50/40 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono font-bold">
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Printed on gate pass clearance.</p>
                    @error('unit_number')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit Title with Suggestions -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Listing Display Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" x-model="title"
                           placeholder="e.g. A-F Modern 2BR Staycation Pad &bull; High-Speed WiFi &amp; Balcony" required
                           class="w-full text-xs px-3.5 py-2.5 rounded-2xl border @error('title') border-rose-300 bg-rose-50/40 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                    
                    <!-- Quick Title Suggestions -->
                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                        <span class="text-[10px] text-slate-400">Quick ideas:</span>
                        <button type="button" @click="applyTitleSuggestion('Modern 2BR Minimalist Suite • High-Speed WiFi & Netflix')"
                                class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            + Minimalist 2BR
                        </button>
                        <button type="button" @click="applyTitleSuggestion('Cozy Family Loft • Deca Pool Access & Full Kitchenette')"
                                class="text-[10px] font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            + Family Loft & Pool
                        </button>
                    </div>
                    @error('title')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Unit Description &amp; Highlights
                </label>
                <textarea name="description" rows="3" x-model="description"
                          placeholder="Describe the unit layout, sleeping arrangement (queen/loft bed), fast WiFi speed for workations, and amenities guests love..."
                          class="w-full text-xs px-3.5 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-600"></textarea>
                @error('description')
                    <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 2: FINANCIAL PRICING & EARNINGS CALCULATOR               -->
        <!-- =================================================================== -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-md shadow-emerald-500/25">2</span>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Pricing, Escrow Deposit &amp; Payouts</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Set your nightly rates with transparent fee visibility</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Step 2 of 4</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Base Nightly Rate -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Base Rate / Night (₱) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">₱</span>
                        <input type="number" step="1" min="100" max="100000" name="base_price_per_night" x-model.number="basePrice" required
                               class="w-full text-xs pl-8 pr-3.5 py-2.5 rounded-2xl border @error('base_price_per_night') border-rose-300 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 tabular-nums font-mono font-black text-sm">
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Host keeps 100% of this amount.</p>
                    @error('base_price_per_night')
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Advance Security Deposit -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Escrow Deposit (₱) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">₱</span>
                        <input type="number" step="1" min="0" max="50000" name="advance_deposit_required" x-model.number="deposit" required
                               class="w-full text-xs pl-8 pr-3.5 py-2.5 rounded-2xl border @error('advance_deposit_required') border-rose-300 @else border-slate-200 dark:border-slate-700 @enderror bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 tabular-nums font-mono font-bold">
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Held safely in escrow for damages.</p>
                </div>

                <!-- Max Guests -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Max Capacity (Guests) <span class="text-rose-500">*</span>
                    </label>
                    <select name="max_guests" x-model.number="maxGuests" required
                            class="w-full text-xs px-3.5 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                        @for($g = 1; $g <= 10; $g++)
                            <option value="{{ $g }}">{{ $g }} Guest{{ $g > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Lobby PMO security limit.</p>
                </div>
            </div>

            <!-- Interactive Host Earnings & Comparative Pricing Card -->
            <div class="rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-emerald-50/80 via-teal-50/60 to-slate-50 dark:from-slate-850 dark:via-slate-850 dark:to-slate-800 border border-emerald-200/60 dark:border-slate-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                        <span>💰 DirectStay Host Earnings Breakdown</span>
                    </span>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">1 Night Example</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-800/80 border border-slate-200/50 dark:border-slate-700">
                        <span class="text-[10px] text-slate-400 block font-semibold">Host Take-Home</span>
                        <span class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                            ₱<span x-text="basePrice.toLocaleString()"></span>
                        </span>
                        <span class="text-[10px] text-emerald-700 dark:text-emerald-300 block font-bold">100% Payout</span>
                    </div>

                    <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-800/80 border border-slate-200/50 dark:border-slate-700">
                        <span class="text-[10px] text-slate-400 block font-semibold">Guest Platform Fee</span>
                        <span class="text-base sm:text-lg font-black text-blue-600 dark:text-blue-400 font-mono tabular-nums">
                            ₱<span x-text="guestPlatformFee.toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                        </span>
                        <span class="text-[10px] text-blue-700 dark:text-blue-300 block font-bold">5% Lean Fee</span>
                    </div>

                    <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-800/80 border border-slate-200/50 dark:border-slate-700">
                        <span class="text-[10px] text-slate-400 block font-semibold">Guest Checkout Rate</span>
                        <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white font-mono tabular-nums">
                            ₱<span x-text="guestTotalPerNight.toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                        </span>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Total per night</span>
                    </div>

                    <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-800/80 border border-slate-200/50 dark:border-slate-700">
                        <span class="text-[10px] text-slate-400 block font-semibold">Projected Monthly</span>
                        <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white font-mono tabular-nums">
                            ₱<span x-text="monthlyProjectedEarnings.toLocaleString()"></span>
                        </span>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block">@ 20 nights occupancy</span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-3 flex items-center gap-1.5">
                    <span class="text-emerald-500 font-bold">✓</span>
                    <span>On Airbnb, guests would pay approximately <strong>₱<span x-text="airbnbComparativeGuestPrice.toLocaleString()"></span></strong> for the same night due to heavy 15% middleman markups.</span>
                </p>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 3: INTERACTIVE AMENITY CHIP SELECTOR                     -->
        <!-- =================================================================== -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-md shadow-indigo-500/25">3</span>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Included Furnishings &amp; Amenities</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Click chips to toggle amenities included in your unit inventory</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Step 3 of 4</span>
            </div>

            <!-- Popular Amenity Chips Grid -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2.5">
                    Click to Toggle Popular Staycation Amenities (<span x-text="selectedAmenities.length"></span> active):
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <template x-for="item in popularAmenities" :key="item.id">
                        <button type="button" @click="toggleAmenity(item.label)"
                                class="p-3 rounded-2xl border text-left transition-all flex items-center gap-2 cursor-pointer btn-press text-xs font-bold"
                                :class="selectedAmenities.includes(item.label) ?
                                    'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-900 dark:text-indigo-200 ring-2 ring-indigo-500/20 shadow-2xs' :
                                    'border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-600'">
                            <span x-text="item.icon" class="text-base"></span>
                            <span x-text="item.label" class="line-clamp-2 leading-snug"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Custom Amenity Tag Adder -->
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Add Custom Amenity or Item
                </label>
                <div class="flex gap-2">
                    <input type="text" x-model="customAmenityInput" @keydown.enter.prevent="addCustomAmenity()"
                           placeholder="e.g. PS5 Console, Rice Cooker, Board Games..."
                           class="flex-1 text-xs px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="button" @click="addCustomAmenity()"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors cursor-pointer">
                        + Add Item
                    </button>
                </div>
            </div>

            <!-- Active Selected Amenities Pills Bar -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                <span class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                    Active Unit Inventory Roster:
                </span>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="amenity in selectedAmenities" :key="amenity">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                            <span x-text="amenity"></span>
                            <button type="button" @click="removeAmenity(amenity)" class="text-slate-400 hover:text-rose-500 cursor-pointer font-bold">&times;</button>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 4: PHOTO UPLOADS WITH LIVE PREVIEWS                      -->
        <!-- =================================================================== -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-teal-600 text-white font-black text-xs flex items-center justify-center shadow-md shadow-teal-500/25">4</span>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Gallery &amp; Cover Imagery</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">High-quality photos significantly boost reservation conversion rates</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Step 4 of 4</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Primary Cover Photo Drop Area with Live Preview -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Primary Cover Image (Hero Display)
                    </label>
                    
                    <div class="relative border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-3xl p-5 text-center hover:border-blue-500 dark:hover:border-blue-500 transition-colors bg-slate-50/50 dark:bg-slate-800/50">
                        <template x-if="coverPreviewUrl">
                            <div class="relative rounded-2xl overflow-hidden h-44 mb-3 border border-slate-200 dark:border-slate-700">
                                <img :src="coverPreviewUrl" alt="Cover Preview" class="w-full h-full object-cover">
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-600 text-white shadow-md">
                                    Cover Photo
                                </span>
                            </div>
                        </template>

                        <div x-show="!coverPreviewUrl" class="py-6">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-3">
                                📸
                            </div>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Upload primary cover image</p>
                            <p class="text-[11px] text-slate-400 mt-1">JPEG, PNG, or WebP up to 10MB</p>
                        </div>

                        <input type="file" name="cover_photo" accept="image/*" @change="handleCoverChange($event)"
                               class="w-full text-xs file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                    </div>
                </div>

                <!-- Gallery Photos Upload Area with Live Previews -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Additional Gallery Photos (Multiple Allowed)
                    </label>

                    <div class="relative border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-3xl p-5 text-center hover:border-indigo-500 dark:hover:border-indigo-500 transition-colors bg-slate-50/50 dark:bg-slate-800/50">
                        <div class="py-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-2">
                                🖼️
                            </div>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Upload multiple room photos</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Bedroom, bathroom, kitchen, balcony</p>
                        </div>

                        <input type="file" name="photos[]" multiple accept="image/*" @change="handleGalleryChange($event)"
                               class="w-full text-xs file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">

                        <!-- Gallery Live Thumbnails Grid -->
                        <div x-show="galleryPreviews.length > 0" class="mt-4 grid grid-cols-3 gap-2 text-left">
                            <template x-for="(img, idx) in galleryPreviews" :key="idx">
                                <div class="relative rounded-xl overflow-hidden h-16 border border-slate-200 dark:border-slate-700 group">
                                    <img :src="img.url" class="w-full h-full object-cover">
                                    <span class="absolute bottom-1 right-1 text-[9px] font-bold text-white bg-black/60 px-1 rounded" x-text="img.size + 'MB'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Form Action Footer -->
        <div class="flex items-center justify-between p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-md">
            <a href="{{ route('host.units.index') }}"
               class="px-5 py-2.5 rounded-2xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-7 py-3 rounded-2xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 transition-all hover:-translate-y-0.5 cursor-pointer">
                Create &amp; Publish Unit Listing &rarr;
            </button>
        </div>
    </form>
</div>
@endsection
