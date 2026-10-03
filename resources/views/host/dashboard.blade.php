@extends('layouts.app')

@section('title', 'Host Dashboard - DirectStay')

@section('content')
<div class="page-enter max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Top Host Welcome Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-700 border border-blue-200/60 mb-2 backdrop-blur-sm">
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
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-blue-600/25 transition-all hover:shadow-xl hover:shadow-blue-600/30 hover:-translate-y-0.5 btn-press">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Verifications Queue</span>
                @if($pendingVerificationsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/90 text-blue-700 font-extrabold">
                        {{ $pendingVerificationsCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('units.index') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 glass-surface hover:bg-white/60 transition-all btn-press">
                Public Catalog &rarr;
            </a>
        </div>
    </div>

    <!-- Metrics Cards Grid — Glassmorphic Financial Ledger -->
    <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 stagger-enter">

        <!-- Ambient Glow Blobs -->
        <div class="ambient-glow w-72 h-72 bg-amber-400/15 -top-20 -left-16 glow-animate blob-animate"></div>
        <div class="ambient-glow w-64 h-64 bg-blue-500/12 -top-10 right-1/4 glow-animate blob-animate" style="animation-delay: 2s;"></div>
        <div class="ambient-glow w-56 h-56 bg-emerald-400/10 bottom-0 right-0 glow-animate blob-animate" style="animation-delay: 4s;"></div>

        <!-- Card 1: Pending Verifications -->
        <div class="glass-surface-dense rounded-3xl p-6 flex flex-col justify-between hover-lift relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-3xl"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pending Triage</span>
                    <span class="w-9 h-9 rounded-xl bg-amber-100/80 text-amber-600 flex items-center justify-center font-bold text-sm shadow-sm shadow-amber-200/50">
                        ⏳
                    </span>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-slate-900 mb-1 tabular-nums">{{ $pendingVerificationsCount }}</div>
                    <div class="text-xs text-amber-600 font-semibold">
                        {{ $pendingVerificationsCount > 0 ? 'Requires Host Gate Pass Action' : 'All Clear & Approved' }}
                    </div>
                </div>
            </div>
            <a href="{{ route('host.verifications.index') }}" class="relative mt-4 pt-3 border-t border-white/40 text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center justify-between transition-colors">
                <span>View Queue</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Card 2: Active / Upcoming Stays -->
        <div class="glass-surface-dense rounded-3xl p-6 flex flex-col justify-between hover-lift relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-3xl"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Bookings</span>
                    <span class="w-9 h-9 rounded-xl bg-emerald-100/80 text-emerald-600 flex items-center justify-center font-bold text-sm shadow-sm shadow-emerald-200/50">
                        🏨
                    </span>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-slate-900 mb-1 tabular-nums">{{ $activeBookingsCount }}</div>
                    <div class="text-xs text-emerald-600 font-semibold">Confirmed & Cleared Stays</div>
                </div>
            </div>
            <div class="relative mt-4 pt-3 border-t border-white/40 text-[11px] text-slate-400">
                {{ $completedBookingsCount }} completed checkouts
            </div>
        </div>

        <!-- Card 3: Security Deposits Held -->
        <div class="glass-surface-dense rounded-3xl p-6 flex flex-col justify-between hover-lift relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-3xl"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Security Deposits Held</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-100/80 text-blue-600 flex items-center justify-center font-bold text-sm shadow-sm shadow-blue-200/50">
                        🛡️
                    </span>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-slate-900 mb-1 tabular-nums font-mono">₱{{ number_format($totalDepositsHeld, 2) }}</div>
                    <div class="text-xs text-slate-500">₱1,000 per booking deposit escrow</div>
                </div>
            </div>
            <div class="relative mt-4 pt-3 border-t border-white/40 text-[11px] text-slate-400">
                Subject to checkout inspection
            </div>
        </div>

        <!-- Card 4: Platform Service Fee Ledger -->
        <div class="glass-surface-dense rounded-3xl p-6 flex flex-col justify-between hover-lift relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-violet-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-3xl"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">DirectStay 5% Platform Fees</span>
                    <span class="w-9 h-9 rounded-xl bg-indigo-100/80 text-indigo-600 flex items-center justify-center font-bold text-sm shadow-sm shadow-indigo-200/50">
                        ⚡
                    </span>
                </div>
                <div>
                    <div class="text-3xl font-extrabold text-slate-900 mb-1 tabular-nums font-mono">₱{{ number_format($totalPlatformFees, 2) }}</div>
                    <div class="text-xs text-indigo-600 font-semibold">5% guest platform micro-fees</div>
                </div>
            </div>
            <div class="relative mt-4 pt-3 border-t border-white/40 text-[11px] text-slate-400">
                100% Host Earnings Kept (0% OTA Fee)
            </div>
        </div>

    </div>

    <!-- Managed Buildings & Units Overview -->
    <div class="glass-surface rounded-3xl p-8 mb-10 relative overflow-hidden">
        <div class="ambient-glow w-48 h-48 bg-indigo-400/10 -top-12 -right-8 glow-animate"></div>
        <h2 class="text-lg font-bold text-slate-900 mb-4 relative">Managed Building Properties</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative">
            @foreach($buildings as $b)
                <div class="p-6 rounded-2xl bg-white/40 border border-white/50 backdrop-blur-sm flex flex-col justify-between hover:bg-white/55 transition-all duration-300 hover:shadow-lg hover:shadow-slate-200/40 group">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-white/70 text-slate-800 border border-white/50 backdrop-blur-sm">
                                {{ $b->code }}
                            </span>
                            <span class="text-xs font-semibold text-blue-600">
                                Template: <code>{{ $b->gate_pass_template }}</code>
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-1 group-hover:text-blue-700 transition-colors">{{ $b->name }}</h3>
                        <p class="text-xs text-slate-500 mb-3">{{ $b->address }}</p>
                        <div class="text-xs text-slate-600">
                            Lobby Security Email: <strong>{{ $b->admin_email }}</strong>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/40 flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ $b->units_count }} active units</span>
                        <span class="font-bold text-emerald-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 status-beacon"></span>
                            Automated DomPDF Dispatch Active
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Bookings Table — Frosted Glass -->
    <div class="glass-surface rounded-3xl overflow-hidden relative">
        <div class="ambient-glow w-40 h-40 bg-blue-400/8 top-0 right-20 glow-animate"></div>

        <div class="p-6 border-b border-white/30 flex items-center justify-between relative">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Recent DirectStay Reservations</h2>
                <p class="text-xs text-slate-500">Live booking activity across all buildings</p>
            </div>
            <a href="{{ route('host.verifications.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                View All &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="glass-table-header text-slate-700 uppercase font-bold text-[11px] tracking-wider border-b border-white/30">
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
                <tbody class="divide-y divide-white/20 font-medium">
                    @forelse($recentBookings as $b)
                        <tr class="glass-table-row transition-colors">
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
                                <div class="font-bold text-slate-900 tabular-nums">₱{{ number_format($b->total_amount, 2) }}</div>
                                <span class="text-[10px] text-slate-400">Dep: ₱{{ number_format($b->advance_deposit_amount, 2) }}</span>
                            </td>
                            <td class="py-4 px-6">
                                @if($b->status === 'confirmed')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-200/60 backdrop-blur-sm">
                                        Confirmed ✓
                                    </span>
                                @elseif($b->status === 'checked_out')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100/80 text-slate-700 border border-slate-200/60 backdrop-blur-sm">
                                        Checked Out
                                    </span>
                                @elseif($b->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100/80 text-rose-700 border border-rose-200/60 backdrop-blur-sm">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100/80 text-amber-800 border border-amber-200/60 backdrop-blur-sm">
                                        Pending Review
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('host.verifications.show', $b) }}"
                                   class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50/70 hover:bg-blue-100/80 backdrop-blur-sm border border-blue-100/50 transition-colors">
                                    Verify
                                </a>
                                @if($b->status === 'confirmed' || $b->status === 'checked_in')
                                    <a href="{{ route('host.checkouts.show', $b) }}"
                                       class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white/50 hover:bg-white/70 backdrop-blur-sm border border-white/40 transition-colors">
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
