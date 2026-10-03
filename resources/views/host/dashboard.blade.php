@extends('layouts.app')

@section('title', 'Host Dashboard - DirectStay Bento SaaS')

@section('content')
<script>
window.hostDashboard = function() {
    return {
        calendarTab: 'calendar',
        blockModalOpen: false,
        selectedUnitId: 'all',
        blockFormUnitId: 'all',

        currentYear: {{ now()->year }},
        currentMonth: {{ now()->month - 1 }}, // 0-indexed (0 = Jan)

        startDate: null,
        endDate: null,
        hoverDate: null,
        isSelecting: false,
        activeBlockDetails: null,

        blockedDates: @json($blockedDatesData),
        bookings: @json($calendarBookingsData),
        units: @json($units),

        get monthName() {
            const dt = new Date(this.currentYear, this.currentMonth, 1);
            return dt.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        },
        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },
        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },
        goToToday() {
            this.currentYear = {{ now()->year }};
            this.currentMonth = {{ now()->month - 1 }};
        },
        get daysGrid() {
            const year = this.currentYear;
            const month = this.currentMonth;
            const firstDay = new Date(year, month, 1).getDay(); // 0 = Sun
            const totalDays = new Date(year, month + 1, 0).getDate();
            const todayStr = '{{ now()->toDateString() }}';

            const cells = [];
            for (let i = 0; i < firstDay; i++) {
                cells.push({ isBlank: true, key: 'blank-' + i });
            }

            for (let d = 1; d <= totalDays; d++) {
                const monthStr = String(month + 1).padStart(2, '0');
                const dayStr = String(d).padStart(2, '0');
                const dateStr = `${year}-${monthStr}-${dayStr}`;
                const isToday = (dateStr === todayStr);

                // Filter blocks for selected unit
                const matchingBlocks = this.blockedDates.filter(b => {
                    if (this.selectedUnitId !== 'all' && b.unit_id != this.selectedUnitId) return false;
                    return dateStr >= b.start_date && dateStr <= b.end_date;
                });

                // Filter bookings for selected unit
                const matchingBookings = this.bookings.filter(b => {
                    if (this.selectedUnitId !== 'all' && b.unit_id != this.selectedUnitId) return false;
                    return dateStr >= b.check_in_date && dateStr <= b.check_out_date;
                });

                cells.push({
                    isBlank: false,
                    key: dateStr,
                    day: d,
                    dateStr: dateStr,
                    isToday: isToday,
                    isPast: dateStr < todayStr,
                    blocks: matchingBlocks,
                    bookings: matchingBookings,
                    isBlocked: matchingBlocks.length > 0,
                    hasBooking: matchingBookings.length > 0,
                });
            }

            return cells;
        },
        handleDayClick(cell) {
            if (cell.isBlank) return;

            // If day is already blocked and we're not currently selecting an end date, show block details
            if (cell.isBlocked && !this.isSelecting) {
                this.activeBlockDetails = cell.blocks[0];
                return;
            }

            this.activeBlockDetails = null;

            if (!this.isSelecting) {
                // First click sets startDate
                this.startDate = cell.dateStr;
                this.endDate = null;
                this.hoverDate = null;
                this.isSelecting = true;
                if (this.selectedUnitId !== 'all') {
                    this.blockFormUnitId = this.selectedUnitId;
                }
            } else {
                // Second click sets endDate
                if (cell.dateStr < this.startDate) {
                    // Clicked an earlier date, make it the new start date
                    this.startDate = cell.dateStr;
                    this.endDate = null;
                } else {
                    this.endDate = cell.dateStr;
                    this.isSelecting = false;
                    this.hoverDate = null;
                }
            }
        },
        handleDayHover(cell) {
            if (this.isSelecting && !cell.isBlank) {
                this.hoverDate = cell.dateStr;
            }
        },
        clearSelection() {
            this.startDate = null;
            this.endDate = null;
            this.hoverDate = null;
            this.isSelecting = false;
            this.activeBlockDetails = null;
        },
        isCellSelected(dateStr) {
            if (!this.startDate) return false;
            return dateStr === this.startDate || dateStr === this.endDate;
        },
        isCellInRange(dateStr) {
            if (!this.startDate) return false;
            const end = this.endDate || (this.isSelecting ? this.hoverDate : null);
            if (!end) return false;
            const min = this.startDate < end ? this.startDate : end;
            const max = this.startDate < end ? end : this.startDate;
            return dateStr >= min && dateStr <= max;
        },
        formatDisplayDate(dStr) {
            if (!dStr) return '';
            const [y, m, d] = dStr.split('-');
            const dt = new Date(y, m - 1, d);
            return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
        nightsCount() {
            if (!this.startDate) return 0;
            const end = this.endDate || this.startDate;
            const [y1, m1, d1] = this.startDate.split('-');
            const [y2, m2, d2] = end.split('-');
            const dt1 = new Date(y1, m1 - 1, d1);
            const dt2 = new Date(y2, m2 - 1, d2);
            const diff = Math.round((dt2 - dt1) / (1000 * 60 * 60 * 24));
            return diff <= 0 ? 1 : diff;
        },
        activeBlocksCountForUnit() {
            return this.blockedDates.filter(b => {
                if (this.selectedUnitId !== 'all' && b.unit_id != this.selectedUnitId) return false;
                return b.end_date >= '{{ now()->toDateString() }}';
            }).length;
        }
    };
};
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="hostDashboard()">

    <!-- Top Bento Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                <span>Host Control Center &bull; Urban Deca Homes Ortigas</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Operations Dashboard
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Real-time booking dispatch, deposit escrow, and security gate pass management.
            </p>
        </div>

        <!-- Quick Top Nav Actions -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('host.units.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Unit</span>
            </a>

            <button type="button" @click="blockModalOpen = true"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200/80 hover:bg-rose-100/70 transition-all hover:-translate-y-0.5 cursor-pointer">
                <span>🚫 Block Dates</span>
            </button>

            <a href="{{ route('host.verifications.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-2xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-sm transition-all hover:-translate-y-0.5">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Gate Pass Triage</span>
                @if($pendingVerificationsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-slate-950">
                        {{ $pendingVerificationsCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('units.index') }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl text-xs font-semibold text-slate-700 bg-white border border-slate-200/80 hover:bg-slate-50 shadow-xs transition-all">
                <span>Catalog</span>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- MODERN SAAS BENTO GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- =================================================================== -->
        <!-- BENTO CARD 1: INTERACTIVE BOOKING & BLOCK CALENDAR (Col-Span-2, Row-Span-2) -->
        <!-- =================================================================== -->
        <div class="lg:col-span-2 lg:row-span-2 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 p-5 sm:p-6 flex flex-col justify-between relative overflow-hidden group">
            <!-- Subtle SaaS accent gradient glow -->
            <div class="absolute -top-24 -right-24 w-60 h-60 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <div>
                <!-- Calendar Card Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100/60 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm font-bold text-slate-900 tracking-tight">Interactive Calendar</h2>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                    Click dates to block
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400">Urban Deca Homes &bull; Click 1st date, then 2nd date to block</p>
                        </div>
                    </div>

                    <!-- View Selector Pill -->
                    <div class="flex items-center p-1 rounded-xl bg-slate-100/80 text-[11px] font-semibold text-slate-600 self-start sm:self-auto">
                        <button type="button"
                                @click="calendarTab = 'calendar'"
                                :class="calendarTab === 'calendar' ? 'bg-white text-slate-900 shadow-xs' : 'hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                            Month
                        </button>
                        <button type="button"
                                @click="calendarTab = 'schedule'"
                                :class="calendarTab === 'schedule' ? 'bg-white text-slate-900 shadow-xs' : 'hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                            Timeline
                        </button>
                    </div>
                </div>

                <!-- Secondary Control Toolbar: Unit Filter & Month Navigation -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 mb-3 border-b border-slate-100 text-xs">
                    <!-- Unit Filter Dropdown -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Unit:</span>
                        <select x-model="selectedUnitId"
                                class="text-xs font-semibold py-1 px-2.5 rounded-xl border border-slate-200/90 bg-slate-50/70 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all">⚡ All Units (Overview)</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->building->code ?? 'UDH' }} &bull; Unit {{ $unit->unit_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month Navigation -->
                    <div class="flex items-center gap-1">
                        <button type="button" @click="prevMonth()" title="Previous Month"
                                class="p-1.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <span class="text-xs font-bold text-slate-800 min-w-[110px] text-center" x-text="monthName"></span>
                        <button type="button" @click="nextMonth()" title="Next Month"
                                class="p-1.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <button type="button" @click="goToToday()"
                                class="ml-1 px-2 py-0.5 rounded-lg text-[10px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors cursor-pointer">
                            Today
                        </button>
                    </div>
                </div>

                <!-- Calendar View Tab -->
                <div x-show="calendarTab === 'calendar'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <!-- Legend Indicators -->
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3 px-1 text-[10px] text-slate-500">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <span class="w-2.5 h-2.5 rounded-md bg-slate-100 border border-slate-300"></span>
                                <span>Available (Click to Block)</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <span class="w-2.5 h-2.5 rounded-md bg-rose-500"></span>
                                <span>Blocked</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <span class="w-2.5 h-2.5 rounded-md bg-blue-600"></span>
                                <span>Guest Stay</span>
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400" x-show="isSelecting">
                            📍 Select end date to finish range
                        </span>
                    </div>

                    <!-- Active Block Inspector (When host clicks an existing blocked date) -->
                    <div x-show="activeBlockDetails" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="mb-3.5 p-4 rounded-3xl backdrop-blur-xl bg-white/85 border border-rose-200/80 shadow-lg shadow-rose-950/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs relative overflow-hidden">
                        <!-- Glow accent -->
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-rose-400/10 rounded-full blur-xl pointer-events-none"></div>

                        <div class="flex items-start gap-3 relative">
                            <div class="w-9 h-9 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200/80 flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                                🔒
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-slate-900" x-text="activeBlockDetails ? (activeBlockDetails.building_code + ' • Unit ' + activeBlockDetails.unit_number) : ''"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-200/80">Blocked Range</span>
                                </div>
                                <div class="text-xs text-rose-900 font-semibold mt-0.5" x-text="activeBlockDetails ? activeBlockDetails.reason : ''"></div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5"
                                     x-text="activeBlockDetails ? (formatDisplayDate(activeBlockDetails.start_date) + ' → ' + formatDisplayDate(activeBlockDetails.end_date)) : ''"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 relative">
                            <button type="button" @click="activeBlockDetails = null"
                                    class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 border border-slate-200/60 transition-colors cursor-pointer">
                                Dismiss
                            </button>
                            <form :action="activeBlockDetails ? activeBlockDetails.delete_url : '#'" method="POST"
                                  onsubmit="return confirm('Are you sure you want to unblock these dates?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 shadow-sm shadow-rose-600/20 transition-all cursor-pointer">
                                    Unblock Dates
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Calendar Column Headers -->
                    <div class="grid grid-cols-7 gap-1 text-center mb-1">
                        @foreach(['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $dayName)
                            <div class="py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                {{ $dayName }}
                            </div>
                        @endforeach
                    </div>

                    <!-- Dynamic Calendar Grid (Alpine-driven) -->
                    <div class="grid grid-cols-7 gap-1 text-center">
                        <template x-for="cell in daysGrid" :key="cell.key">
                            <div>
                                <!-- Blank leading cell -->
                                <template x-if="cell.isBlank">
                                    <div class="h-10 sm:h-11 rounded-xl bg-slate-50/40"></div>
                                </template>

                                <!-- Calendar Day Cell -->
                                <template x-if="!cell.isBlank">
                                    <button type="button"
                                            @click="handleDayClick(cell)"
                                            @mouseenter="handleDayHover(cell)"
                                            :title="cell.isBlocked ? (cell.blocks[0].reason + ' (Click to unblock)') : (cell.hasBooking ? ('Booked: ' + cell.bookings[0].guest_name) : 'Click to block dates')"
                                            class="w-full h-10 sm:h-11 rounded-xl p-1 flex flex-col items-center justify-between text-xs transition-all relative cursor-pointer select-none group/cell backdrop-blur-xs"
                                            :class="{
                                                'bg-gradient-to-tr from-rose-600 to-pink-600 text-white font-black shadow-md shadow-rose-600/25 ring-2 ring-rose-400/50 z-10': isCellSelected(cell.dateStr),
                                                'bg-rose-50/90 text-rose-950 font-bold border-y border-rose-200/80': isCellInRange(cell.dateStr) && !isCellSelected(cell.dateStr),
                                                'bg-rose-50/70 text-rose-700 border border-rose-200/80 font-bold hover:bg-rose-100/80': cell.isBlocked && !isCellInRange(cell.dateStr) && !isCellSelected(cell.dateStr),
                                                'bg-blue-50/70 text-blue-900 border border-blue-200/80 font-bold hover:bg-blue-100/80': cell.hasBooking && !cell.isBlocked && !isCellInRange(cell.dateStr) && !isCellSelected(cell.dateStr),
                                                'bg-white/60 hover:bg-white/95 text-slate-700 border border-slate-100 hover:border-slate-300/80 hover:shadow-xs': !cell.isBlocked && !cell.hasBooking && !isCellInRange(cell.dateStr) && !isCellSelected(cell.dateStr),
                                                'opacity-40': cell.isPast && !cell.isBlocked && !cell.hasBooking && !isCellSelected(cell.dateStr)
                                            }">
                                        
                                        <div class="w-full flex items-center justify-between px-0.5 leading-none">
                                            <span class="text-[11px] font-mono" x-text="cell.day"></span>
                                            <template x-if="cell.dateStr === startDate">
                                                <span class="text-[8px] font-black uppercase tracking-tight px-1 py-0.2 rounded bg-white text-rose-700 shadow-2xs">In</span>
                                            </template>
                                            <template x-if="cell.dateStr === endDate && endDate !== startDate">
                                                <span class="text-[8px] font-black uppercase tracking-tight px-1 py-0.2 rounded bg-white text-rose-700 shadow-2xs">Out</span>
                                            </template>
                                        </div>

                                        <!-- Bottom Badges / Indicators -->
                                        <div class="flex items-center gap-0.5">
                                            <template x-if="cell.isBlocked">
                                                <span class="text-[9px] leading-none" :class="isCellSelected(cell.dateStr) ? 'text-white' : 'text-rose-600'">🔒</span>
                                            </template>
                                            <template x-if="cell.hasBooking && !cell.isBlocked">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                            </template>
                                            <template x-if="cell.isToday && !cell.isBlocked && !cell.hasBooking">
                                                <span class="w-1 h-1 rounded-full bg-amber-500"></span>
                                            </template>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- ================================================================= -->
                    <!-- INTERACTIVE BLOCK ACTION BAR (Appears when dates are selected) -->
                    <!-- ================================================================= -->
                    <div x-show="startDate" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="mt-4 p-4 sm:p-5 rounded-3xl backdrop-blur-xl bg-white/80 border border-rose-200/70 shadow-xl shadow-rose-950/5 relative overflow-hidden">
                        <!-- Subtle decorative gradient blur behind glass -->
                        <div class="absolute -top-12 -right-12 w-40 h-40 bg-gradient-to-br from-rose-400/10 to-pink-500/10 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3.5 relative">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-100/70 text-rose-600 flex items-center justify-center font-bold text-sm border border-rose-200/60 shadow-2xs shrink-0">
                                    🗓️
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 flex flex-wrap items-center gap-2">
                                        <span class="tracking-tight">Block Dates Range:</span>
                                        <span class="font-mono text-rose-700 bg-rose-50/90 px-2.5 py-0.5 rounded-xl border border-rose-200/80 font-extrabold text-[11px]"
                                              x-text="formatDisplayDate(startDate) + (endDate && endDate !== startDate ? ' → ' + formatDisplayDate(endDate) : ' (1 Day)')"></span>
                                        <span class="text-[10px] text-slate-500 font-semibold px-2 py-0.5 rounded-full bg-slate-100/80 border border-slate-200/60"
                                              x-text="nightsCount() + ' ' + (nightsCount() === 1 ? 'day' : 'days')"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-0.5" x-show="!endDate">
                                        💡 Click another date on the calendar to set the end date, or confirm below to block this single day.
                                    </p>
                                </div>
                            </div>

                            <button type="button" @click="clearSelection()"
                                    class="self-start sm:self-auto text-xs font-semibold text-slate-500 hover:text-slate-800 px-3 py-1.5 rounded-xl hover:bg-slate-100/80 border border-slate-200/60 transition-colors cursor-pointer">
                                ✕ Clear
                            </button>
                        </div>

                        <!-- Block Submission Form -->
                        <form action="{{ route('host.blocked-dates.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-200/70 relative">
                            @csrf
                            <input type="hidden" name="start_date" :value="startDate">
                            <input type="hidden" name="end_date" :value="endDate || startDate">

                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">Target Unit</label>
                                <select name="unit_id" x-model="blockFormUnitId"
                                        class="w-full text-xs px-3.5 py-2.5 rounded-2xl bg-white/90 border border-slate-200/90 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500 shadow-2xs cursor-pointer backdrop-blur-md">
                                    <option value="all">⚡ All Units (Fleet-wide Block)</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">
                                            {{ $unit->building->code ?? 'UDH' }} &bull; Unit {{ $unit->unit_number }} ({{ $unit->title }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">Block Reason</label>
                                <select name="reason"
                                        class="w-full text-xs px-3.5 py-2.5 rounded-2xl bg-white/90 border border-slate-200/90 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500 shadow-2xs cursor-pointer backdrop-blur-md">
                                    <option value="Host Personal Staycation">Host Personal Stay</option>
                                    <option value="Unit Maintenance & Deep Cleaning">Deep Cleaning / Maintenance</option>
                                    <option value="Aircon / Appliance Servicing">Aircon / Appliance Servicing</option>
                                    <option value="Direct / Offline Guest Reservation">Direct / Offline Reservation</option>
                                    <option value="Building Elevator / Power Maintenance">Building Maintenance</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button type="submit"
                                        class="w-full py-2.5 px-4 rounded-2xl bg-gradient-to-r from-rose-600 via-rose-500 to-rose-600 hover:from-rose-700 hover:to-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/25 transition-all flex items-center justify-center gap-1.5 cursor-pointer hover:scale-[1.01] active:scale-[0.98]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>Confirm &amp; Block Dates</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Schedule Timeline Tab -->
                <div x-show="calendarTab === 'schedule'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-2.5">
                    {{-- Active Blocked Dates List --}}
                    @if($blockedDates->count() > 0)
                        <div class="mb-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 block mb-1.5">Active Host Date Blocks</span>
                            <div class="space-y-1.5 max-h-44 overflow-y-auto pr-1">
                                @foreach($blockedDates as $block)
                                    <div class="p-2.5 rounded-xl bg-rose-50/70 border border-rose-200/80 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs">🔒</span>
                                            <div>
                                                <div class="font-bold text-slate-900 font-mono text-[11px]">
                                                    {{ $block->unit?->building?->code ?? 'UDH' }} &bull; Unit {{ $block->unit?->unit_number ?? 'N/A' }}
                                                    <span class="text-slate-500 font-sans font-normal ml-1">({{ $block->start_date->format('M d') }} &rarr; {{ $block->end_date->format('M d') }})</span>
                                                </div>
                                                <span class="text-[10px] text-rose-700 block truncate max-w-[220px]">{{ $block->reason }}</span>
                                            </div>
                                        </div>
                                        <form action="{{ route('host.blocked-dates.destroy', $block) }}" method="POST" onsubmit="return confirm('Unblock these dates?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[10px] font-bold text-rose-700 hover:bg-rose-100 transition-colors cursor-pointer">
                                                Unblock
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Guest Bookings List --}}
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block mb-1.5">Upcoming Guest Stays</span>
                        <div class="space-y-1.5">
                            @forelse($recentBookings->take(4) as $booking)
                                <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-center justify-between text-xs hover:bg-slate-100/80 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-bold text-[11px] text-slate-800 shadow-2xs">
                                            {{ $booking->unit->building->code ?? 'UDH' }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $booking->guest_name }}</div>
                                            <div class="text-[10px] text-slate-500">
                                                Unit {{ $booking->unit->unit_number ?? 'N/A' }} &bull; {{ $booking->check_in_date->format('M d') }} &rarr; {{ $booking->check_out_date->format('M d') }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $booking->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 py-4 text-center">No stays scheduled for this timeframe.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Bento Footer Bar -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-semibold text-slate-700">{{ $activeBookingsCount }} Active Stay(s)</span>
                    </div>
                    <span class="text-slate-300">&bull;</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span class="font-semibold text-slate-700" x-text="activeBlocksCountForUnit() + ' Blocked Range(s)'"></span>
                    </div>
                </div>
                <span class="text-[11px] text-slate-400">DirectStay Auto-Sync</span>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 2: ACTIVE SECURITY DEPOSITS (Col-Span-1 Square) -->
        <!-- =================================================================== -->
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 p-6 flex flex-col justify-between relative overflow-hidden group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Escrow Vault</span>
                <div class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100/60 group-hover:scale-105 transition-transform">
                    🛡️
                </div>
            </div>

            <div>
                <span class="text-xs font-semibold text-slate-500 block mb-1">Active Deposits</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight tabular-nums">
                    ₱{{ number_format($totalDepositsHeld, 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    ₱1,000 / stay escrow protection
                </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Auto-deductions enabled</span>
                <span class="font-bold text-blue-600 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 3: RECENT PENALTIES & DEDUCTIONS (Col-Span-1 Square) -->
        <!-- =================================================================== -->
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 p-6 flex flex-col justify-between relative overflow-hidden group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Compliance Audit</span>
                <div class="w-9 h-9 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm border border-rose-100/60 group-hover:scale-105 transition-transform">
                    ⚠️
                </div>
            </div>

            <div>
                <span class="text-xs font-semibold text-slate-500 block mb-1">Recent Penalties</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-rose-600 tracking-tight tabular-nums">
                    ₱{{ number_format($totalPenalties ?? 0, 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    Garbage, smoking & key fees
                </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Checkout penalty engine</span>
                <span class="font-bold text-rose-600 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 4: PENDING GATE PASS VERIFICATIONS (Col-Span-1 Square) -->
        <!-- =================================================================== -->
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 p-6 flex flex-col justify-between relative overflow-hidden group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Security Gate Pass</span>
                <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm border border-amber-100/60 group-hover:scale-105 transition-transform">
                    ⏳
                </div>
            </div>

            <div>
                <span class="text-xs font-semibold text-slate-500 block mb-1">Pending Triage</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight tabular-nums flex items-center gap-2">
                    <span>{{ $pendingVerificationsCount }}</span>
                    @if($pendingVerificationsCount > 0)
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                    @endif
                </div>
                <p class="text-[11px] {{ $pendingVerificationsCount > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' }} mt-1">
                    {{ $pendingVerificationsCount > 0 ? 'ID / Signature requires sign-off' : 'All clear & dispatched' }}
                </p>
            </div>

            <a href="{{ route('host.verifications.index') }}" class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-blue-600 font-bold hover:text-blue-700 transition-colors">
                <span>Open Queue</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </a>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 5: 0% COMMISSION / PLATFORM MICRO-FEES (Col-Span-1 Square) -->
        <!-- =================================================================== -->
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 p-6 flex flex-col justify-between relative overflow-hidden group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Host Economics</span>
                <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm border border-emerald-100/60 group-hover:scale-105 transition-transform">
                    ⚡
                </div>
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <span class="text-xs font-semibold text-slate-500">Direct Earnings</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-emerald-100 text-emerald-800">100% Payout</span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight tabular-nums">
                    ₱{{ number_format($totalPlatformFees, 2) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    5% guest micro-fee (0% OTA commission)
                </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Host keeps 100% room rate</span>
                <span class="text-emerald-600 font-bold">✓</span>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 6: MANAGED PROPERTIES (Col-Span-2 Card) -->
        <!-- =================================================================== -->
        <div class="lg:col-span-2 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                            🏢
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 tracking-tight">Managed Properties</h2>
                            <p class="text-[11px] text-slate-400">Urban Deca Homes Ortigas Complexes</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        {{ $buildings->count() }} Buildings &bull; {{ $units->count() }} Units
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($buildings as $b)
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/70 transition-all flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-slate-900 border border-slate-200/80 shadow-2xs">
                                        {{ $b->code }}
                                    </span>
                                    <span class="text-[10px] font-semibold text-emerald-600 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Automated
                                    </span>
                                </div>
                                <h3 class="text-xs font-bold text-slate-900 line-clamp-1">{{ $b->name }}</h3>
                                <p class="text-[10px] text-slate-400 line-clamp-1 mt-0.5">{{ $b->address }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px] text-slate-500">
                                <span>{{ $b->units_count }} Unit(s)</span>
                                <span class="font-mono text-[9px] text-slate-400 truncate max-w-[120px]">{{ $b->admin_email }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>Integrated Pasig PMO DomPDF Dispatch</span>
                <span class="text-blue-600 font-semibold">Active &bull; PDF v1.4</span>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 7: OPERATIONS FAST-ACTIONS & PIPELINE (Col-Span-2 Card) -->
        <!-- =================================================================== -->
        <div class="lg:col-span-2 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm border border-indigo-100/60">
                            🚀
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 tracking-tight">Quick Operations Launchpad</h2>
                            <p class="text-[11px] text-slate-400">Automated gate pass & inspection tools</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ route('host.verifications.index') }}"
                       class="p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-blue-50/60 hover:border-blue-200/60 transition-all group">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-900 group-hover:text-blue-700">
                            <span>ID Verifications</span>
                            <span class="text-slate-400 group-hover:text-blue-600">&rarr;</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Review guest government IDs & signatures</p>
                    </a>

                    <a href="{{ route('host.units.create') }}"
                       class="p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-blue-50/60 hover:border-blue-200/60 transition-all group">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-900 group-hover:text-blue-700">
                            <span>+ Add New Unit</span>
                            <span class="text-slate-400 group-hover:text-blue-600">&rarr;</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Register new condo suite & rates</p>
                    </a>

                    <button type="button" @click="blockModalOpen = true"
                            class="p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-rose-50/60 hover:border-rose-200/60 transition-all text-left group cursor-pointer">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-900 group-hover:text-rose-700">
                            <span>🚫 Block Dates</span>
                            <span class="text-slate-400 group-hover:text-rose-600">&rarr;</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Hold dates for cleaning & personal stays</p>
                    </button>

                    <a href="{{ route('host.units.index') }}"
                       class="p-3 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-blue-50/60 hover:border-blue-200/60 transition-all group">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-900 group-hover:text-blue-700">
                            <span>Manage Units</span>
                            <span class="text-slate-400 group-hover:text-blue-600">&rarr;</span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Edit rates, photos & blocked dates</p>
                    </a>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span>System Health: <strong class="text-emerald-600">Optimal (100% Uptime)</strong></span>
                <span>PHP 8.3 &bull; Laravel</span>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BENTO CARD 8: LIVE RESERVATION LEDGER & INSPECTIONS (Col-Span-4) -->
        <!-- =================================================================== -->
        <div class="lg:col-span-4 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 p-6 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Recent DirectStay Reservations</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-700">
                            {{ $recentBookings->count() }} Recent
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Live guest booking feed, escrow deposit states, and checkout audit</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('host.verifications.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors">
                        <span>View Verification Pipeline</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Modern SaaS Table -->
            <div class="overflow-x-auto -mx-6">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead class="bg-slate-50/75 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-y border-slate-100">
                        <tr>
                            <th class="py-3 px-6">Booking Ref</th>
                            <th class="py-3 px-6">Tower & Unit</th>
                            <th class="py-3 px-6">Lead Guest</th>
                            <th class="py-3 px-6">Stay Schedule</th>
                            <th class="py-3 px-6">Financial Ledger</th>
                            <th class="py-3 px-6">Status</th>
                            <th class="py-3 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($recentBookings as $b)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-800 border border-slate-200/60">
                                        {{ $b->booking_code }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900">{{ $b->unit->building->code ?? 'UDH' }} &bull; Unit {{ $b->unit->unit_number ?? 'N/A' }}</div>
                                    <span class="text-[10px] text-slate-400 block">{{ $b->unit->name ?? 'Standard Suite' }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900">{{ $b->guest_name }}</div>
                                    <span class="text-[10px] text-slate-400 block">{{ $b->guest_phone }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-slate-800">{{ $b->check_in_date->format('M d') }} - {{ $b->check_out_date->format('M d, Y') }}</div>
                                    <span class="text-[10px] text-slate-400">{{ $b->nights_count }} Night(s)</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-900 tabular-nums">₱{{ number_format($b->total_amount, 2) }}</div>
                                    <span class="text-[10px] text-blue-600 font-medium">Dep: ₱{{ number_format($b->advance_deposit_amount, 2) }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    @if($b->status === 'confirmed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Confirmed
                                        </span>
                                    @elseif($b->status === 'checked_out')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            Checked Out
                                        </span>
                                    @elseif($b->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Pending Review
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="{{ route('host.verifications.show', $b) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200/60 transition-colors">
                                        Verify Pass
                                    </a>
                                    @if($b->status === 'confirmed' || $b->status === 'checked_in')
                                        <a href="{{ route('host.checkouts.show', $b) }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200/80 border border-slate-200 transition-colors">
                                            Checkout Inspect
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                                        📋
                                    </div>
                                    <p class="font-medium text-slate-600">No booking reservations found</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Reservations will stream directly here upon guest checkout.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Quick Manual Date Blocker Modal -->
    <div x-show="blockModalOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div @click.away="blockModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl border border-slate-100 p-6 sm:p-7 relative">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs border border-rose-100">
                        🚫
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Manual Date Blocker</h3>
                        <p class="text-xs text-slate-400">Prevent booking during specific dates</p>
                    </div>
                </div>
                <button type="button" @click="blockModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                    ✕
                </button>
            </div>

            <form action="{{ route('host.blocked-dates.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Target Unit</label>
                        <button type="button" @click="blockModalOpen = false" class="text-[10px] text-blue-600 font-semibold hover:underline">
                            Use Main Calendar &rarr;
                        </button>
                    </div>
                    <select name="unit_id" x-model="blockFormUnitId" required class="w-full text-xs px-3.5 py-2.5 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500 bg-white font-medium cursor-pointer">
                        <option value="all">⚡ All Units (Fleet-wide Block)</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">
                                {{ $unit->building->code ?? 'UDH' }} &bull; Unit {{ $unit->unit_number }} ({{ $unit->title }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" min="{{ date('Y-m-d') }}" :value="startDate || '{{ date('Y-m-d') }}'" @change="startDate = $event.target.value" required
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">End Date</label>
                        <input type="date" name="end_date" min="{{ date('Y-m-d') }}" :value="endDate || startDate || '{{ date('Y-m-d', strtotime('+1 day')) }}'" @change="endDate = $event.target.value" required
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Block Reason</label>
                    <select name="reason" class="w-full text-xs px-3.5 py-2.5 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500 bg-white cursor-pointer">
                        <option value="Host Personal Staycation">Host Personal Stay</option>
                        <option value="Unit Maintenance & Deep Cleaning">Maintenance / Deep Cleaning</option>
                        <option value="Aircon / Appliance Servicing">Appliance Servicing</option>
                        <option value="Direct / Offline Guest Reservation">Direct / Offline Reservation</option>
                        <option value="Building Elevator / Power Maintenance">Building Maintenance</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="blockModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-all cursor-pointer">
                        Confirm Block
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
