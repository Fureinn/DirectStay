@extends('layouts.app')

@section('title', 'My Bookings - DirectStay')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
                Guest Dashboard
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                My DirectStay Reservations
            </h1>
            <p class="text-xs text-slate-500">
                Logged in as <strong>{{ $user->name }}</strong> ({{ $user->email }}) &bull; Mobile: {{ $user->phone ?? 'Not set' }}
            </p>
        </div>

        <a href="{{ route('units.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Book Another Unit
        </a>
    </div>

    <section class="mb-8 rounded-3xl border border-emerald-100 bg-emerald-50 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-emerald-950">How to book a stay</h2>
                <p class="mt-1 text-xs leading-relaxed text-emerald-800">Choose a unit, select available dates and guests, then start the reservation. You will be guided to upload the security-deposit receipt and your ID. Add every person staying in the unit so the building can issue the required gate pass.</p>
            </div>
            <a href="{{ route('units.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700">Explore units</a>
        </div>
    </section>

    @if($bookings->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-sm max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                🏨
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">No bookings yet</h3>
            <p class="text-xs text-slate-500 mb-6">
                You haven't made any staycation reservations yet. Explore our verified units in Urban Deca Homes Ortigas with zero OTA markups.
            </p>
            <a href="{{ route('units.index') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition-all">
                Browse Units in Ortigas Pasig &rarr;
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($bookings as $b)
                <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-6 sm:p-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">

                        <!-- Left Info -->
                        <div class="flex items-start gap-5">
                            <!-- Thumbnail -->
                            <div class="w-24 h-24 rounded-2xl overflow-hidden shrink-0 bg-slate-100 border border-slate-200">
                                @if($b->unit->cover_image)
                                    <img src="{{ asset($b->unit->cover_image) }}" alt="Unit" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-bold">DS</div>
                                @endif
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                                        {{ $b->booking_code }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-semibold">
                                        {{ $b->unit->building->name }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md">
                                        {{ $b->unit->unit_number }}
                                    </span>
                                </div>

                                <h3 class="text-base font-bold text-slate-900 mb-1">
                                    {{ $b->unit->title }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        📅 {{ $b->check_in_date->format('M d, Y') }} &rarr; {{ $b->check_out_date->format('M d, Y') }} ({{ $b->nights_count }} Nights)
                                    </span>
                                    <span>&bull;</span>
                                    <span>👥 {{ $b->guest_count }} Guests</span>
                                    <span>&bull;</span>
                                    <span class="font-bold text-slate-900">Total: ₱{{ number_format($b->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Status & CTAs -->
                        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-start lg:justify-end border-t lg:border-t-0 pt-4 lg:pt-0 border-slate-100">
                            <div>
                                @if($b->status === 'confirmed' || $b->status === 'checked_in')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Gate Pass Ready ✓
                                    </span>
                                @elseif($b->status === 'checked_out')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Completed Stay
                                    </span>
                                @elseif($b->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        Action / Verification Required
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('compliance.portal', $b->booking_code) }}"
                               class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                                Complete booking &bull; Details
                            </a>

                            @if($b->status === 'confirmed' || $b->status === 'checked_in')
                                <a href="{{ route('compliance.downloadGatePass', $b->booking_code) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all"
                                   title="Official Building Gate Pass (Deca Guest Form)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Gate Pass
                                </a>
                                <a href="{{ route('compliance.downloadRentalAgreement', $b->booking_code) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 shadow-xs transition-all"
                                   title="Official Lease Contract (Staycation Rental Agreement)">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    Rental Contract
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
