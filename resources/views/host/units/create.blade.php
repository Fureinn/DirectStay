@extends('layouts.app')

@section('title', 'Add New Unit - DirectStay Host')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumb & Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-2">
            <a href="{{ route('host.units.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-1">
                &larr; <span>Back to Units</span>
            </a>
        </div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 mb-2">
            <span>Urban Deca Homes Ortigas &bull; Property Addition</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Add New Condominium Unit
        </h1>
        <p class="text-xs text-slate-500 mt-1">
            Register a unit to your host inventory, set nightly pricing, security deposit rules, and upload gallery imagery.
        </p>
    </div>

    <!-- Add Unit Form (Bento SaaS Card) -->
    <form action="{{ route('host.units.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Bento Card 1: Core Property Details -->
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5">
            <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">1</span>
                <span>Unit Identity &amp; Building Association</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Building Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Building / Complex Tower <span class="text-rose-500">*</span>
                    </label>
                    <select name="building_id" required
                            class="w-full text-xs px-3.5 py-2.5 rounded-2xl border @error('building_id') border-rose-300 bg-rose-50/40 @else border-slate-200 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                        <option value="">Select Building Tower</option>
                        @foreach($buildings as $building)
                            <option value="{{ $building->id }}" {{ old('building_id') == $building->id ? 'selected' : '' }}>
                                {{ $building->name }} ({{ $building->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('building_id')
                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit Number -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Unit Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="unit_number" value="{{ old('unit_number') }}"
                           placeholder="e.g. N-412 or P-815" required
                           class="w-full text-xs px-3.5 py-2.5 rounded-2xl border @error('unit_number') border-rose-300 bg-rose-50/40 @else border-slate-200 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono">
                    @error('unit_number')
                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Unit Listing Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Listing Display Title <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}"
                       placeholder="e.g. Modern Minimalist 2BR Suite &bull; High-Speed WiFi &amp; Balcony" required
                       class="w-full text-xs px-3.5 py-2.5 rounded-2xl border @error('title') border-rose-300 bg-rose-50/40 @else border-slate-200 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600">
                @error('title')
                    <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Unit Description &amp; House Amenities
                </label>
                <textarea name="description" rows="3"
                          placeholder="Describe the unit layout, view, bedding, and what makes this staycation special..."
                          class="w-full text-xs px-3.5 py-2.5 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Bento Card 2: Pricing & Occupancy -->
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5">
            <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">2</span>
                <span>Financial Ledger &amp; Capacity</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Base Nightly Rate -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Base Rate / Night (₱) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">₱</span>
                        <input type="number" step="0.01" min="0" name="base_price_per_night" value="{{ old('base_price_per_night', '1500.00') }}" required
                               class="w-full text-xs pl-8 pr-3.5 py-2.5 rounded-2xl border @error('base_price_per_night') border-rose-300 @else border-slate-200 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600 tabular-nums font-mono font-bold">
                    </div>
                    @error('base_price_per_night')
                        <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Advance Security Deposit -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Escrow Deposit (₱) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs text-slate-400 font-bold">₱</span>
                        <input type="number" step="0.01" min="0" name="advance_deposit_required" value="{{ old('advance_deposit_required', '1000.00') }}" required
                               class="w-full text-xs pl-8 pr-3.5 py-2.5 rounded-2xl border @error('advance_deposit_required') border-rose-300 @else border-slate-200 @enderror focus:outline-none focus:ring-2 focus:ring-blue-600 tabular-nums font-mono font-bold">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Held in escrow during guest stay</p>
                </div>

                <!-- Max Guests -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Max Capacity (Guests) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" min="1" max="20" name="max_guests" value="{{ old('max_guests', 4) }}" required
                           class="w-full text-xs px-3.5 py-2.5 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 tabular-nums font-bold">
                    <p class="text-[10px] text-slate-400 mt-1">Official gate pass roster limit</p>
                </div>
            </div>

            <!-- Included Amenities -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Included Amenities &amp; Items (Comma-separated)
                </label>
                <input type="text" name="inventory_items" value="{{ old('inventory_items', 'Inverter Aircon, 55-inch Smart TV with Netflix, High-Speed Fiber WiFi 100Mbps, Induction Cooker & Range Hood, Refrigerator & Microwave, Heated Shower, Electric Kettle & Cookware, Fresh Towels & Linens') }}"
                       placeholder="Inverter Aircon, High-Speed WiFi, Smart TV, Heated Shower, Refrigerator"
                       class="w-full text-xs px-3.5 py-2.5 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <p class="text-[10px] text-slate-400 mt-1">Separate amenities with commas.</p>
            </div>
        </div>

        <!-- Bento Card 3: Photos & Imagery -->
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6 sm:p-8 space-y-5">
            <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">3</span>
                <span>Unit Imagery &amp; Gallery Photos</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Primary Cover Photo -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Primary Cover Image (JPEG, PNG, WebP)
                    </label>
                    <input type="file" name="cover_photo" accept="image/*"
                           class="w-full text-xs px-3 py-2 rounded-2xl border border-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Appears on listing cards and hero headers.</p>
                </div>

                <!-- Gallery Photos -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Additional Gallery Photos (Multiple allowed)
                    </label>
                    <input type="file" name="photos[]" multiple accept="image/*"
                           class="w-full text-xs px-3 py-2 rounded-2xl border border-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Select bedroom, bathroom, kitchen, and living area photos.</p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('host.units.index') }}"
               class="px-5 py-2.5 rounded-2xl text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-2xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-sm transition-all hover:-translate-y-0.5 cursor-pointer">
                Create &amp; Publish Unit Listing
            </button>
        </div>
    </form>

</div>
@endsection
