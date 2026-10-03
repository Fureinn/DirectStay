@extends('layouts.app')

@section('title', 'Manage Units & Photos - DirectStay Host')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('host.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                    &larr; Host Dashboard
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Managed Condominium Units
            </h1>
            <p class="text-xs text-slate-500">
                Upload interior photos, update unit rates, and manage staycation listings
            </p>
        </div>

        <a href="{{ route('units.index') }}" target="_blank"
           class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
            View Live Catalog &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach($units as $unit)
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                <!-- Cover Image -->
                <div class="relative h-56 bg-slate-100 overflow-hidden">
                    @if($unit->cover_image)
                        <img src="{{ asset($unit->cover_image) }}" alt="{{ $unit->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold bg-slate-800 text-white">
                            No Photo Uploaded
                        </div>
                    @endif
                    <div class="absolute top-4 left-4 flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/90 text-slate-900 backdrop-blur shadow-sm">
                            {{ $unit->building->code }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-600 text-white shadow-sm">
                            {{ $unit->unit_number }}
                        </span>
                    </div>

                    <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg bg-black/60 backdrop-blur text-white text-[11px] font-semibold">
                        📷 {{ count($unit->images ?? []) }} Photos
                    </div>
                </div>

                <!-- Info Body -->
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-1">{{ $unit->title }}</h3>
                        <p class="text-xs text-slate-500 mb-4">{{ $unit->building->name }} &bull; {{ $unit->building->address }}</p>

                        <div class="grid grid-cols-3 gap-2 p-3 bg-slate-50 rounded-2xl border border-slate-100 text-center text-xs mb-4">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Rate / Night</span>
                                <span class="font-bold text-slate-900">₱{{ number_format($unit->base_price_per_night, 2) }}</span>
                            </div>
                            <div class="border-x border-slate-200">
                                <span class="text-[10px] text-slate-400 block">Deposit</span>
                                <span class="font-bold text-slate-900">₱{{ number_format($unit->advance_deposit_required, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Capacity</span>
                                <span class="font-bold text-slate-900">{{ $unit->max_guests }} Guests</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Active Listing
                        </span>
                        <a href="{{ route('host.units.edit', $unit) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-sm transition-all">
                            <span>Manage Photos & Details</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
