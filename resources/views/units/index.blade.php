@extends('layouts.app')

@section('title', 'DirectStay - Urban Deca Homes Ortigas Direct Bookings')

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 text-white p-6 sm:p-10 lg:p-12 mb-10 shadow-xl border border-emerald-900/40">
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold uppercase tracking-wider mb-4 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Official Condominium Direct-Booking Engine
            </div>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white mb-4 leading-tight">
                Urban Deca Homes Ortigas Direct Bookings.
            </h1>
            <p class="text-slate-300 text-sm sm:text-base lg:text-lg mb-6 leading-relaxed">
                Stay at <strong>Urban Deca Homes Ortigas (Pasig City)</strong> directly with verified unit owners. Enjoy 0% host commission, lean <strong>5% guest platform fee</strong> (saving an average of ₱276 per stay vs Airbnb), secure GCash processing, and automated building gate pass clearance.
            </p>

            <div class="flex flex-wrap items-center gap-3 text-xs font-medium text-slate-300">
                <div class="flex items-center gap-2 bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-xs border border-white/10">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>KM 19 Ortigas Ave Ext, Brgy. Rosario, Pasig</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-xs border border-white/10">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>Deca Gate Pass Building Templates (N & P)</span>
                </div>
                <div class="flex items-center gap-2 bg-white/10 px-3.5 py-2 rounded-xl backdrop-blur-xs border border-white/10">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>₱1,000 Refundable Security Deposit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Building Filter Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Featured Units in Ortigas, Pasig</h2>
            <p class="text-sm text-slate-500 mt-0.5">Explore available units across Building N and Building P.</p>
        </div>

        <div class="inline-flex p-1 rounded-2xl bg-slate-100 border border-slate-200/80 shadow-xs">
            <a href="{{ route('units.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ empty($selectedBuilding) ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                All Units ({{ $units->count() }})
            </a>
            @foreach($buildings as $b)
                <a href="{{ route('units.index', ['building' => $b->code]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $selectedBuilding === $b->code ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    {{ $b->code }} ({{ $b->units_count }})
                </a>
            @endforeach
        </div>
    </div>

    <!-- Units Grid -->
    <div class="stagger-enter grid grid-cols-1 lg:grid-cols-2 gap-8">
        @forelse($units as $unit)
            <div class="surface-card hover-lift flex flex-col bg-white/90 rounded-3xl border overflow-hidden group">
                <!-- Unit Card Media Container -->
                <div class="relative h-64 sm:h-72 w-full overflow-hidden bg-slate-100">
                    @if ($unit->cover_image)
                        <img src="{{ asset($unit->cover_image) }}" alt="{{ $unit->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900 p-6 flex flex-col justify-between text-white">
                            <span class="text-xs uppercase tracking-wider text-emerald-400 font-bold">Urban Deca Homes Ortigas</span>
                            <div>
                                <h4 class="text-2xl font-bold">{{ $unit->title }}</h4>
                                <p class="text-sm text-slate-400">{{ $unit->unit_number }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-transparent to-black/30 pointer-events-none"></div>

                    <!-- Badges on image -->
                    <div class="absolute top-4 left-4 right-4 flex items-center justify-between pointer-events-none">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-slate-900/80 backdrop-blur-md text-white border border-white/20 shadow-xs">
                            {{ $unit->building ? $unit->building->name : 'Urban Deca Homes' }}
                        </span>
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold score-badge-gold shadow-sm backdrop-blur-md">
                            <svg class="w-3.5 h-3.5 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span>{{ number_format($unit->averageRating(), 1) }}</span>
                            <span class="text-amber-900/60 font-semibold text-[11px]">({{ $unit->reviewsCount() }})</span>
                        </div>
                    </div>

                    <div class="absolute bottom-4 left-4 right-4 text-white pointer-events-none">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight drop-shadow-sm">{{ $unit->title }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-500 text-white shadow-xs">
                                Unit {{ $unit->unit_number }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-200 flex items-center gap-1.5 mt-1">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ $unit->building ? $unit->building->address : 'KM 19 Ortigas Ave Ext, Brgy. Rosario, Pasig City' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Unit Details Body -->
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <p class="text-sm text-slate-600 mb-5 leading-relaxed line-clamp-3">
                            {{ $unit->description }}
                        </p>

                        <!-- Key Specs Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-5 text-xs">
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px] font-medium">Capacity</span>
                                    <span class="font-bold text-slate-900">Up to {{ $unit->max_guests }} Guests</span>
                                </div>
                            </div>

                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px] font-medium">Security Deposit</span>
                                    <span class="font-bold text-slate-900">₱{{ number_format($unit->advance_deposit_required, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Amenities Checklist -->
                        @if(!empty($unit->inventory_items))
                            <div class="mb-6">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Included Amenities:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(array_slice($unit->inventory_items, 0, 4) as $item)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/60">
                                            ✓ {{ $item }}
                                        </span>
                                    @endforeach
                                    @if(count($unit->inventory_items) > 4)
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-semibold text-slate-500 bg-slate-50">
                                            +{{ count($unit->inventory_items) - 4 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Pricing & CTA Button -->
                    <div class="pt-5 border-t border-slate-100 flex items-center justify-between gap-4">
                        <div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl sm:text-3xl font-black text-slate-900">₱{{ number_format($unit->base_price_per_night, 0) }}</span>
                                <span class="text-xs text-slate-500 font-medium">night</span>
                            </div>
                            <span class="text-[11px] text-emerald-600 font-semibold block">0% Host Markup &bull; Direct Stay</span>
                        </div>

                        <a href="{{ route('units.show', $unit) }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-rose-500 via-rose-600 to-pink-600 hover:from-rose-600 hover:to-pink-700 shadow-md shadow-rose-500/25 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer">
                            <span>View Unit & Book</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-16 bg-white rounded-3xl border border-slate-200">
                <p class="text-slate-500 text-sm">No units found matching this building filter.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
