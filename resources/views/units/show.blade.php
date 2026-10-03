@extends('layouts.app')

@section('title', $unit->title . ' - DirectStay')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-28 lg:pb-12">

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

    <div class="mb-8 bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-6 shadow-xs">
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
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
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
                <div class="grid grid-cols-3 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center mb-8">
                    <div>
                        <span class="text-[11px] font-medium text-slate-400 block mb-0.5">Rate / Night</span>
                        <span class="text-lg font-extrabold text-slate-900">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
                    </div>
                    <div class="border-x border-slate-200">
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
                                <div class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-slate-700 font-medium">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $item }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Building Security House Rules -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800">
                    <div class="flex items-center gap-2 font-bold text-sm text-slate-900 mb-2">
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Building Security & Gate Pass Policy (Urban Deca Homes Ortigas)
                    </div>
                    <div class="whitespace-pre-line text-slate-600 leading-relaxed font-sans">
                        {{ $unit->building ? $unit->building->house_rules : 'All guests must submit valid government-issued IDs for security gate pass clearance prior to building entry.' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Airbnb-Style Reservation Card -->
        <div class="lg:col-span-5" id="reservationWidget">
            <div class="sticky top-24 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-xl shadow-slate-200/60">

                <!-- Price Header -->
                <div class="flex items-baseline justify-between mb-5">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-black text-slate-900">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
                        <span class="text-sm font-normal text-slate-500">night</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-slate-600 font-medium">
                        <span class="text-amber-500">★</span>
                        <span>4.95</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="underline">Verified</span>
                    </div>
                </div>

                <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">

                    <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4">
                        <p class="text-xs font-extrabold text-emerald-900">How booking works</p>
                        <ol class="mt-2 grid grid-cols-3 gap-2 text-[11px] leading-snug text-emerald-800">
                            <li><span class="font-bold">1.</span> Choose dates and guests</li>
                            <li><span class="font-bold">2.</span> Start your reservation</li>
                            <li><span class="font-bold">3.</span> Upload deposit and ID for approval</li>
                        </ol>
                    </div>

                    <!-- Airbnb-style Grouped Date/Guest Picker Box -->
                    <div class="rounded-2xl border border-slate-300 overflow-hidden shadow-xs focus-within:border-slate-900 transition-colors">
                        <div class="grid grid-cols-2 divide-x divide-slate-300 border-b border-slate-300">
                            <!-- Check-in -->
                            <div class="p-3 bg-white hover:bg-slate-50 transition cursor-pointer">
                                <label for="checkInInput" class="block text-[9px] font-extrabold uppercase tracking-wider text-slate-800">Check-in</label>
                                <input type="date" name="check_in_date" id="checkInInput"
                                       min="{{ date('Y-m-d') }}"
                                       value="{{ old('check_in_date', date('Y-m-d')) }}"
                                       required
                                       class="w-full text-xs font-semibold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                            </div>
                            <!-- Checkout -->
                            <div class="p-3 bg-white hover:bg-slate-50 transition cursor-pointer">
                                <label for="checkOutInput" class="block text-[9px] font-extrabold uppercase tracking-wider text-slate-800">Checkout</label>
                                <input type="date" name="check_out_date" id="checkOutInput"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       value="{{ old('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                       required
                                       class="w-full text-xs font-semibold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                            </div>
                        </div>

                        <!-- Guests Selector -->
                        <div class="p-3 bg-white hover:bg-slate-50 transition">
                            <label for="guestCountSelect" class="block text-[9px] font-extrabold uppercase tracking-wider text-slate-800">Guests</label>
                            <select name="guest_count" id="guestCountSelect"
                                    class="w-full text-xs font-semibold text-slate-900 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                                @for($i = 1; $i <= $unit->max_guests; $i++)
                                    <option value="{{ $i }}" {{ old('guest_count') == $i ? 'selected' : '' }}>
                                        {{ $i }} guest{{ $i > 1 ? 's' : '' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Date Conflict Alert -->
                    <div id="dateAlert" class="hidden text-xs text-rose-600 font-semibold p-3 bg-rose-50 border border-rose-200 rounded-xl">
                        These dates are unavailable. Please pick different check-in/checkout dates.
                    </div>

                    <!-- Step 2: lead guest details (revealed after choosing dates) -->
                    <div id="confirmAndPaySection" class="{{ old('guest_name') ? '' : 'hidden' }} space-y-4 pt-4 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-900">Lead guest details</h3>
                            @auth
                                <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Pre-filled</span>
                            @else
                                <a href="{{ route('customer.login') }}" class="text-[11px] text-slate-500 hover:text-slate-900 underline">Log in</a>
                            @endauth
                        </div>

                        <!-- Lead Guest Info -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name</label>
                                <input type="text" name="guest_name" id="guestNameInput"
                                       value="{{ old('guest_name', auth()->check() ? auth()->user()->name : '') }}"
                                       placeholder="As shown on government ID" required
                                       class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                                    <input type="email" name="guest_email" id="guestEmailInput"
                                           value="{{ old('guest_email', auth()->check() ? auth()->user()->email : '') }}"
                                           placeholder="For gate pass" required
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone</label>
                                    <input type="text" name="guest_phone" id="guestPhoneInput"
                                           value="{{ old('guest_phone', auth()->check() ? (auth()->user()->phone ?? '') : '') }}"
                                           placeholder="e.g. 09171234567" required
                                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-900">
                                </div>
                            </div>
                        </div>

                        <!-- Optional Add-ons -->
                        @if($addOns->count() > 0)
                            <div class="pt-2">
                                <label class="block text-xs font-bold text-slate-800 mb-2">Optional Stay Extras</label>
                                <div class="space-y-2">
                                    @foreach($addOns as $addon)
                                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                                            <div>
                                                <span class="font-semibold text-slate-800">{{ $addon->name }}</span>
                                                <span class="text-slate-400 block text-[11px]">+₱{{ number_format($addon->price, 2) }} each</span>
                                            </div>
                                            <input type="number" name="add_ons[{{ $addon->id }}]" min="0" max="10" value="0"
                                                   data-price="{{ $addon->price }}"
                                                   class="addon-input w-16 text-center text-xs font-bold py-1 px-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-slate-900">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Primary Action Button (Airbnb Style Gradient / Solid) -->
                    <div class="pt-2">
                        <button type="button" id="reserveStepBtn" onclick="handleReserveClick()"
                                class="w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-rose-500 via-rose-600 to-pink-600 hover:from-rose-600 hover:to-pink-700 shadow-md shadow-rose-500/25 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span id="btnText">Reserve</span>
                        </button>

                        <button type="submit" id="confirmAndPayBtn"
                                class="hidden w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-rose-500 via-rose-600 to-pink-600 hover:from-rose-600 hover:to-pink-700 shadow-md shadow-rose-500/25 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span>Start reservation</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>

                        <p class="text-xs text-center text-slate-500 mt-2.5" id="notChargedNotice">
                            No online payment is taken here. The next page explains how to submit the security deposit and required ID.
                        </p>
                    </div>

                    <!-- Clean Price Summary & Collapsible Breakdown (Hidden by default, click to show) -->
                    <div class="pt-4 border-t border-slate-200 space-y-3">
                        <!-- Total Row (Always visible) -->
                        <div class="flex justify-between items-baseline">
                            <div>
                                <span class="text-base font-extrabold text-slate-900">Total</span>
                                <button type="button" id="toggleBreakdownBtn" onclick="togglePriceBreakdown()"
                                        class="block text-xs font-semibold text-slate-600 hover:text-slate-900 underline mt-0.5 cursor-pointer">
                                    Show price breakdown
                                </button>
                            </div>
                            <span class="text-2xl font-black text-slate-900" id="calcGrandTotal">₱0.00</span>
                        </div>

                        <!-- Collapsible Itemized Breakdown (Hidden by default) -->
                        <div id="priceBreakdownDetails" class="hidden pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                            <div class="flex justify-between">
                                <span class="underline">₱{{ number_format($unit->base_price_per_night, 0) }} x <span id="calcNights">1</span> night(s)</span>
                                <span class="font-medium text-slate-900" id="calcRoomTotal">₱0.00</span>
                            </div>

                            <div id="addonsRow" class="hidden flex justify-between">
                                <span class="underline">Add-ons Subtotal</span>
                                <span class="font-medium text-slate-900" id="calcAddonsTotal">₱0.00</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="underline">DirectStay service fee</span>
                                <span class="font-medium text-slate-900" id="calcPlatformFee">₱0.00</span>
                            </div>

                            <div class="flex justify-between border-t border-slate-100 pt-2">
                                <span class="underline">Refundable security deposit</span>
                                <span class="font-medium text-slate-900">₱{{ number_format($unit->advance_deposit_required, 2) }}</span>
                            </div>
                            <p class="text-[10px] text-slate-400">
                                * After starting the reservation, submit the ₱{{ number_format($unit->advance_deposit_required, 0) }} security deposit and ID through the secure portal. The deposit is refundable after checkout inspection, less any itemized deductions.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Mobile Airbnb-Style Fixed Bottom Floating Bar -->
<div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 px-4 py-3 shadow-lg flex items-center justify-between">
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

        document.getElementById('calcNights').innerText = nights;
        document.getElementById('calcRoomTotal').innerText = '₱' + roomTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calcPlatformFee').innerText = '₱' + platformFee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calcAddonsTotal').innerText = '₱' + addonsTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calcGrandTotal').innerText = '₱' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

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
    }

    function togglePriceBreakdown() {
        const details = document.getElementById('priceBreakdownDetails');
        const btn = document.getElementById('toggleBreakdownBtn');
        if (details.classList.contains('hidden')) {
            details.classList.remove('hidden');
            btn.innerText = 'Hide price breakdown';
        } else {
            details.classList.add('hidden');
            btn.innerText = 'Show price breakdown';
        }
    }

    function handleReserveClick() {
        // Reveal confirm and pay section
        confirmAndPaySection.classList.remove('hidden');
        reserveStepBtn.classList.add('hidden');
        confirmAndPayBtn.classList.remove('hidden');
        if (notChargedNotice) {
            notChargedNotice.innerText = 'Next: start the reservation, then submit your security deposit and ID for host approval.';
        }

        // Focus on name input if empty
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
</script>
@endpush
@endsection
