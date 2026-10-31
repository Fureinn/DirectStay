@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('host.units.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Units List
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Edit Unit: {{ $unit->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage unit details, rental rates, and visual gallery photos</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('units.show', $unit) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Public Listing
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 mr-3 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/80 text-rose-800 dark:text-rose-200 text-sm shadow-sm">
            <p class="font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Unit Details Form -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl rounded-3xl shadow-sm border border-slate-200/80 dark:border-white/10 p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Unit Information
                </h2>

                <form action="{{ route('host.units.update', $unit) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Unit Title</label>
                        <input type="text" name="title" value="{{ old('title', $unit->title) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs font-medium transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Unit Number</label>
                        <input type="text" name="unit_number" value="{{ old('unit_number', $unit->unit_number) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Building</label>
                        <input type="text" disabled value="{{ $unit->building ? $unit->building->name . ' (' . $unit->building->code . ')' : 'Urban Deca Homes Ortigas' }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-sm shadow-xs">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ $unit->building ? $unit->building->address : 'Ortigas Avenue Extension, Pasig City' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nightly Rate (₱)</label>
                            <input type="number" step="0.01" name="base_price_per_night" value="{{ old('base_price_per_night', $unit->base_price_per_night) }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs font-semibold transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Deposit (₱)</label>
                            <input type="number" step="0.01" name="advance_deposit_required" value="{{ old('advance_deposit_required', $unit->advance_deposit_required) }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Max Guests</label>
                        <input type="number" name="max_guests" value="{{ old('max_guests', $unit->max_guests) }}" min="1" max="20" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Description &amp; Highlights</label>
                        <textarea name="description" rows="4"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-xs transition">{{ old('description', $unit->description) }}</textarea>
                    </div>

                    <button type="submit" class="w-full mt-4 py-3 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-md shadow-emerald-500/25 transition-all hover:shadow-lg cursor-pointer">
                        Save Unit Details
                    </button>
                </form>
            </div>

            <!-- Manual Date Blocker Bento Card with Visual Calendar -->
            @php
                $blockedArray = $unit->blockedDates->map(fn($b) => [
                    'id' => $b->id,
                    'start_date' => $b->start_date->format('Y-m-d'),
                    'end_date' => $b->end_date->format('Y-m-d'),
                    'reason' => $b->reason,
                ]);
            @endphp
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl rounded-3xl shadow-sm border border-slate-200/80 dark:border-white/10 p-6 space-y-4"
                 x-data="{
                     currentYear: {{ now()->year }},
                     currentMonth: {{ now()->month - 1 }},
                     startDate: '{{ date('Y-m-d') }}',
                     endDate: '{{ date('Y-m-d', strtotime('+1 day')) }}',
                     isSelecting: false,
                     blocks: @json($blockedArray),
                     get monthName() {
                         const dt = new Date(this.currentYear, this.currentMonth, 1);
                         return dt.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                     },
                     prevMonth() {
                         if (this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; }
                         else { this.currentMonth--; }
                     },
                     nextMonth() {
                         if (this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; }
                         else { this.currentMonth++; }
                     },
                     get days() {
                         const y = this.currentYear, m = this.currentMonth;
                         const firstDay = new Date(y, m, 1).getDay();
                         const totalDays = new Date(y, m + 1, 0).getDate();
                         const today = '{{ now()->toDateString() }}';
                         const list = [];
                         for (let i = 0; i < firstDay; i++) list.push({ isBlank: true, key: 'b-' + i });
                         for (let d = 1; d <= totalDays; d++) {
                             const mStr = String(m + 1).padStart(2, '0');
                             const dStr = String(d).padStart(2, '0');
                             const dateStr = `${y}-${mStr}-${dStr}`;
                             const isBlocked = this.blocks.some(b => dateStr >= b.start_date && dateStr <= b.end_date);
                             list.push({ isBlank: false, key: dateStr, day: d, dateStr, isToday: dateStr === today, isBlocked, isPast: dateStr < today });
                         }
                         return list;
                     },
                     handleDayClick(cell) {
                         if (cell.isBlank) return;
                         if (!this.isSelecting) {
                             this.startDate = cell.dateStr;
                             this.endDate = cell.dateStr;
                             this.isSelecting = true;
                         } else {
                             if (cell.dateStr < this.startDate) {
                                 this.startDate = cell.dateStr;
                                 this.endDate = cell.dateStr;
                             } else {
                                 this.endDate = cell.dateStr;
                                 this.isSelecting = false;
                             }
                         }
                     },
                     isInRange(dateStr) {
                         if (!this.startDate || !this.endDate) return false;
                         return dateStr >= this.startDate && dateStr <= this.endDate;
                     }
                 }">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-rose-50 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs border border-rose-100 dark:border-rose-800">
                            🚫
                        </span>
                        <span>Interactive Date Blocker</span>
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        {{ $unit->blockedDates->count() }} Blocked
                    </span>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Click dates directly on the calendar below to block reservation availability for this unit.
                </p>

                <!-- Mini Month Calendar in Unit Edit -->
                <div class="p-3 bg-slate-50/80 dark:bg-slate-800/50 rounded-2xl border border-slate-200/80 dark:border-slate-700/60">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-800 dark:text-white" x-text="monthName"></span>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="prevMonth()" class="p-1 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 cursor-pointer transition-colors">
                                ◀
                            </button>
                            <button type="button" @click="nextMonth()" class="p-1 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 cursor-pointer transition-colors">
                                ▶
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center font-bold text-[9px] text-slate-400 dark:text-slate-500 mb-1">
                        <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center">
                        <template x-for="cell in days" :key="cell.key">
                            <div>
                                <template x-if="cell.isBlank">
                                    <div class="h-7 rounded-lg"></div>
                                </template>
                                <template x-if="!cell.isBlank">
                                    <button type="button" @click="handleDayClick(cell)"
                                            :class="{
                                                'bg-rose-600 text-white font-bold shadow-xs': cell.dateStr === startDate || cell.dateStr === endDate,
                                                'bg-rose-100 dark:bg-rose-950/60 text-rose-900 dark:text-rose-200 font-semibold': isInRange(cell.dateStr) && cell.dateStr !== startDate && cell.dateStr !== endDate,
                                                'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900 line-through opacity-70': cell.isBlocked && !isInRange(cell.dateStr),
                                                'text-slate-700 dark:text-slate-200 hover:bg-slate-200/70 dark:hover:bg-slate-700': !cell.isBlocked && !isInRange(cell.dateStr)
                                            }"
                                            class="w-full h-7 rounded-lg text-[11px] font-mono flex items-center justify-center transition-colors cursor-pointer"
                                            x-text="cell.day">
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200/60 dark:border-slate-700 text-[10px] text-slate-500 dark:text-slate-400">
                        <span>Click start date, then end date</span>
                        <span class="font-mono text-rose-600 dark:text-rose-400 font-semibold" x-text="startDate + ' → ' + endDate"></span>
                    </div>
                </div>

                <!-- Block Dates Form -->
                <form action="{{ route('host.blocked-dates.store') }}" method="POST" class="space-y-3 pt-1">
                    @csrf
                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                    <input type="hidden" name="start_date" :value="startDate">
                    <input type="hidden" name="end_date" :value="endDate">

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Start Date</label>
                            <input type="date" min="{{ date('Y-m-d') }}" x-model="startDate" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-bold text-slate-900 focus:ring-2 focus:ring-rose-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">End Date</label>
                            <input type="date" min="{{ date('Y-m-d') }}" x-model="endDate" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-bold text-slate-900 focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Block Reason</label>
                        <select name="reason" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-rose-500">
                            <option value="Host Personal Staycation">Host Personal Stay</option>
                            <option value="Unit Maintenance & Deep Cleaning">Maintenance / Deep Cleaning</option>
                            <option value="Aircon / Appliance Servicing">Appliance Servicing</option>
                            <option value="Direct / Offline Guest Reservation">Direct / Offline Reservation</option>
                            <option value="Building Elevator / Power Maintenance">Building Maintenance</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Block Selected Dates</span>
                    </button>
                </form>

                <!-- Currently Blocked Dates List -->
                @if($unit->blockedDates->count() > 0)
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Active Date Blocks</span>
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                            @foreach($unit->blockedDates as $block)
                                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white font-mono text-[11px]">
                                            {{ $block->start_date->format('M d, Y') }} &rarr; {{ $block->end_date->format('M d, Y') }}
                                        </div>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate max-w-[180px]">{{ $block->reason }}</span>
                                    </div>
                                    <form action="{{ route('host.blocked-dates.destroy', $block) }}" method="POST" onsubmit="return confirm('Unblock these dates?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded-lg text-[10px] font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition-colors cursor-pointer">
                                            Unblock
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Photo Gallery & Upload Section -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Upload Card -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl rounded-3xl shadow-sm border border-slate-200/80 dark:border-white/10 p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Upload Unit Photos
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Add high quality photos of the living room, bedrooms, kitchen, views, and amenities. Supported formats: JPG, PNG, WEBP (up to 5MB each).</p>

                <form action="{{ route('host.units.photos.upload', $unit) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-6 text-center hover:border-emerald-500 dark:hover:border-emerald-500 transition bg-slate-50/50 dark:bg-slate-800/30">
                        <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="mt-4 flex text-sm leading-6 text-slate-600 dark:text-slate-400 justify-center">
                            <label for="photos" class="relative cursor-pointer rounded-md font-semibold text-emerald-600 dark:text-emerald-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-emerald-600 focus-within:ring-offset-2 hover:text-emerald-500 dark:hover:text-emerald-300">
                                <span>Choose files to upload</span>
                                <input id="photos" name="photos[]" type="file" multiple accept="image/*" class="sr-only" required onchange="updateSelectedFiles(this)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p id="fileSelectionText" class="text-xs text-slate-400 dark:text-slate-500 mt-2">You can select multiple photos at once</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm transition inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Upload Selected Photos
                        </button>
                    </div>
                </form>
            </div>

            <!-- Existing Photos Gallery -->
            <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl rounded-3xl shadow-sm border border-slate-200/80 dark:border-white/10 p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Current Unit Photos</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">The cover photo is featured as the main card thumbnail.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        {{ count($unit->images ?? []) }} {{ Str::plural('Photo', count($unit->images ?? [])) }}
                    </span>
                </div>

                @if (empty($unit->images) || count($unit->images) === 0)
                    <div class="text-center py-12 px-4 border border-dashed border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-800/30">
                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No photos uploaded yet</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Upload images above to showcase this unit to prospective guests.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($unit->images as $photo)
                            @php
                                $isCover = ($unit->cover_image === $photo);
                            @endphp
                            <div class="group relative rounded-2xl overflow-hidden border {{ $isCover ? 'border-2 border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700' }} bg-slate-100 dark:bg-slate-800 flex flex-col justify-between shadow-sm hover:shadow-md transition-all">
                                <div class="relative h-44 w-full overflow-hidden bg-slate-200 dark:bg-slate-700">
                                    <img src="{{ asset($photo) }}" alt="{{ $unit->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">

                                    @if ($isCover)
                                        <span class="absolute top-2 left-2 px-2.5 py-1 rounded-md bg-emerald-600 text-white text-[11px] font-bold shadow tracking-wide uppercase">
                                            Cover Photo
                                        </span>
                                    @endif
                                </div>

                                <div class="p-3 bg-white dark:bg-slate-900/80 flex items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-800">
                                    @if (!$isCover)
                                        <form action="{{ route('host.units.photos.cover', $unit) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="image" value="{{ $photo }}">
                                            <button type="submit" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 hover:underline">
                                                Set as Cover
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Main Cover
                                        </span>
                                    @endif

                                    <form action="{{ route('host.units.photos.delete', $unit) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this photo?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="image" value="{{ $photo }}">
                                        <button type="submit" class="text-xs font-semibold text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function updateSelectedFiles(input) {
    const textElement = document.getElementById('fileSelectionText');
    if (input.files && input.files.length > 0) {
        textElement.textContent = input.files.length === 1 
            ? input.files[0].name 
            : `${input.files.length} photos selected ready to upload`;
        textElement.classList.add('text-emerald-600', 'font-medium');
        textElement.classList.remove('text-slate-400');
    }
}
</script>
@endsection
