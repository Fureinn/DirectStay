@extends('layouts.app')

@section('title', 'Manage Units & Photos - DirectStay Host')

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('host.dashboard') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Host Dashboard</span>
                </a>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <span class="text-xs font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">Condo Portfolio</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Managed Condominium Units
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Urban Deca Homes Ortigas &bull; High-res gallery photo uploads, nightly pricing, and inventory specifications
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('host.units.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-xs font-black text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-500/25 transition-all hover:shadow-lg btn-press">
                <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add New Condo Unit</span>
            </a>

            <a href="{{ route('units.index') }}" target="_blank"
               class="px-4 py-2.5 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white/70 dark:bg-slate-800/70 hover:bg-white dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700 shadow-2xs backdrop-blur-md transition-all btn-press">
                Live Catalog &rarr;
            </a>
        </div>
    </div>

    <!-- Quick Stats Bento Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="glass-surface-dense rounded-3xl p-5 border border-white/60 dark:border-white/10 shadow-xs relative overflow-hidden">
            <div class="ambient-glow w-24 h-24 bg-blue-500/10 -top-6 -right-6 glow-animate"></div>
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">Portfolio Size</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tabular-nums">{{ $units->count() }}</div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">Condo units listed</span>
        </div>

        <div class="glass-surface-dense rounded-3xl p-5 border border-white/60 dark:border-white/10 shadow-xs relative overflow-hidden">
            <div class="ambient-glow w-24 h-24 bg-emerald-500/10 -top-6 -right-6 glow-animate"></div>
            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block mb-1">Listing Status</span>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums flex items-center gap-2">
                <span>{{ $units->count() }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 status-beacon"></span>
            </div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">100% active on portal</span>
        </div>

        <div class="glass-surface-dense rounded-3xl p-5 border border-white/60 dark:border-white/10 shadow-xs relative overflow-hidden">
            <div class="ambient-glow w-24 h-24 bg-indigo-500/10 -top-6 -right-6 glow-animate"></div>
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">Total Capacity</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tabular-nums">{{ $units->sum('max_guests') }}</div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">Max staycation guests</span>
        </div>

        <div class="glass-surface-dense rounded-3xl p-5 border border-white/60 dark:border-white/10 shadow-xs relative overflow-hidden">
            <div class="ambient-glow w-24 h-24 bg-purple-500/10 -top-6 -right-6 glow-animate"></div>
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">Avg Base Rate</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tabular-nums font-mono">
                ₱{{ number_format($units->avg('base_price_per_night'), 0) }}
            </div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400">Per night in Deca</span>
        </div>
    </div>

    <!-- Units Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 stagger-enter">
        @foreach($units as $unit)
            <div class="glass-surface-dense rounded-3xl border border-white/70 dark:border-white/10 overflow-hidden shadow-lg hover-lift flex flex-col justify-between group relative">
                <!-- Ambient glow on card hover -->
                <div class="ambient-glow w-36 h-36 bg-blue-500/10 -bottom-8 -right-8 glow-animate"></div>

                <!-- Cover Image & Media -->
                <div class="relative h-64 bg-slate-950 overflow-hidden select-none">
                    @if($unit->cover_image)
                        <img src="{{ asset($unit->cover_image) }}" alt="{{ $unit->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-all duration-500 ease-out">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-xs font-bold bg-slate-900">
                            <span class="text-3xl mb-1">📷</span>
                            <span>No Photo Uploaded</span>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/30 pointer-events-none"></div>

                    <!-- Top Floating Badges -->
                    <div class="absolute top-4 left-4 right-4 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-white/90 dark:bg-slate-900/90 text-slate-900 dark:text-white backdrop-blur-md shadow-xs">
                                Tower {{ $unit->building?->code ?? 'UDH' }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-600 text-white shadow-xs">
                                Unit {{ $unit->unit_number }}
                            </span>
                        </div>

                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-600/90 text-white backdrop-blur-md shadow-xs flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-200"></span>
                            Active Listing
                        </span>
                    </div>

                    <!-- Bottom Photo Counter & Title -->
                    <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between text-white pointer-events-none">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-300">Condo Listing</span>
                            <h3 class="text-lg font-black tracking-tight drop-shadow-sm">{{ $unit->title }}</h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-slate-950/70 backdrop-blur-md text-white text-[11px] font-bold border border-white/15">
                            📷 {{ count($unit->images ?? []) }} Photos
                        </span>
                    </div>
                </div>

                <!-- Info Body -->
                <div class="p-6 flex-1 flex flex-col justify-between relative z-10">
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ $unit->building?->name ?? 'Urban Deca Homes Ortigas' }} &bull; {{ $unit->building?->address ?? 'Pasig City' }}</span>
                        </p>

                        <!-- Specifications Grid -->
                        <div class="grid grid-cols-3 gap-2 p-3.5 bg-slate-50/80 dark:bg-slate-900/60 rounded-2xl border border-slate-200/60 dark:border-slate-800 text-center text-xs mb-5 shadow-2xs">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Nightly Rate</span>
                                <span class="font-black text-slate-900 dark:text-white tabular-nums font-mono text-sm">
                                    ₱{{ number_format($unit->base_price_per_night, 0) }}
                                </span>
                            </div>
                            <div class="border-x border-slate-200 dark:border-slate-800">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Sec Deposit</span>
                                <span class="font-black text-slate-900 dark:text-white tabular-nums font-mono text-sm">
                                    ₱{{ number_format($unit->advance_deposit_required, 0) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Capacity</span>
                                <span class="font-black text-slate-900 dark:text-white tabular-nums text-sm">
                                    {{ $unit->max_guests }} Guests
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Bar -->
                    <div class="pt-4 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between gap-3">
                        <a href="{{ route('units.show', $unit) }}" target="_blank"
                           class="text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 inline-flex items-center gap-1 transition-colors">
                            <span>Preview</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        <a href="{{ route('host.units.edit', $unit) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 shadow-sm shadow-blue-500/25 hover:shadow-md transition-all btn-press">
                            <span>Manage Photos &amp; Details</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
