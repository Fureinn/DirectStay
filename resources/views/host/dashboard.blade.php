@extends('layouts.app')

@section('title', 'Host Dashboard - DirectStay')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Host Welcome Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 mb-2">
                Certified Lessor Portal
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Host Operations Dashboard
            </h1>
            <p class="text-xs text-slate-500">
                Welcome back, <strong>{{ auth()->user()->name }}</strong> &bull; Managing Urban Deca Homes & DirectStay Properties
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('host.verifications.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Verifications Queue</span>
                @if($pendingVerificationsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-white text-blue-700 font-extrabold">
                        {{ $pendingVerificationsCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('units.index') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                Public Catalog &rarr;
            </a>
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        <!-- Card 1: Pending Verifications -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pending Triage</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-xs">
                    ⏳
                </span>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-900 mb-1">{{ $pendingVerificationsCount }}</div>
                <div class="text-xs text-amber-600 font-semibold">
                    {{ $pendingVerificationsCount > 0 ? 'Requires Host Gate Pass Action' : 'All Clear & Approved' }}
                </div>
            </div>
            <a href="{{ route('host.verifications.index') }}" class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center justify-between">
                <span>View Queue</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Card 2: Active / Upcoming Stays -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Bookings</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                    🏨
                </span>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-900 mb-1">{{ $activeBookingsCount }}</div>
                <div class="text-xs text-emerald-600 font-semibold">Confirmed & Cleared Stays</div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                {{ $completedBookingsCount }} completed checkouts
            </div>
        </div>

        <!-- Card 3: Security Deposits Held -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Security Deposits Held</span>
                <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                    🛡️
                </span>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-900 mb-1">₱{{ number_format($totalDepositsHeld, 2) }}</div>
                <div class="text-xs text-slate-500">₱1,000 per booking deposit escrow</div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                Subject to checkout inspection
            </div>
        </div>

        <!-- Card 4: Platform Service Fee Ledger -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">DirectStay 5% Platform Fees</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                    ⚡
                </span>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-900 mb-1">₱{{ number_format($totalPlatformFees, 2) }}</div>
                <div class="text-xs text-indigo-600 font-semibold">5% guest platform micro-fees</div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400">
                100% Host Earnings Kept (0% OTA Fee)
            </div>
        </div>

    </div>

    <!-- Managed Buildings & Units Overview -->
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm mb-10">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Managed Building Properties</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($buildings as $b)
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-white text-slate-800 border border-slate-200">
                                {{ $b->code }}
                            </span>
                            <span class="text-xs font-semibold text-blue-600">
                                Template: <code>{{ $b->gate_pass_template }}</code>
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-1">{{ $b->name }}</h3>
                        <p class="text-xs text-slate-500 mb-3">{{ $b->address }}</p>
                        <div class="text-xs text-slate-600">
                            Lobby Security Email: <strong>{{ $b->admin_email }}</strong>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-200 flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ $b->units_count }} active units</span>
                        <span class="font-bold text-emerald-600">Automated DomPDF Dispatch Active</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Recent DirectStay Reservations</h2>
                <p class="text-xs text-slate-500">Live booking activity across all buildings</p>
            </div>
            <a href="{{ route('host.verifications.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                View All &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[11px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Ref Code</th>
                        <th class="py-3.5 px-6">Building & Unit</th>
                        <th class="py-3.5 px-6">Lead Guest</th>
                        <th class="py-3.5 px-6">Stay Schedule</th>
                        <th class="py-3.5 px-6">Financials</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentBookings as $b)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                {{ $b->booking_code }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-800">{{ $b->unit->building->code }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $b->unit->unit_number }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-900">{{ $b->guest_name }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $b->guest_phone }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div>{{ $b->check_in_date->format('M d') }} - {{ $b->check_out_date->format('M d, Y') }}</div>
                                <span class="text-[11px] text-slate-400">{{ $b->nights_count }} Night(s)</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">₱{{ number_format($b->total_amount, 2) }}</div>
                                <span class="text-[10px] text-slate-400">Dep: ₱{{ number_format($b->advance_deposit_amount, 2) }}</span>
                            </td>
                            <td class="py-4 px-6">
                                @if($b->status === 'confirmed')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Confirmed ✓
                                    </span>
                                @elseif($b->status === 'checked_out')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Checked Out
                                    </span>
                                @elseif($b->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Pending Review
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('host.verifications.show', $b) }}"
                                   class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
                                    Verify
                                </a>
                                @if($b->status === 'confirmed' || $b->status === 'checked_in')
                                    <a href="{{ route('host.checkouts.show', $b) }}"
                                       class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                                        Checkout Inspect
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                No booking reservations logged yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
