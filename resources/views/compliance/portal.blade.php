@extends('layouts.app')

@section('title', 'Security Clearance & Identity Vault - DirectStay')
@section('hideNav', 'true')

@section('content')
@php
    $guestCount = $booking->guest_count;
    $hasAllIds = $governmentIds->count() >= $guestCount;
    $hasSelfie = (bool) $selfieDoc;
    $hasPayment = (bool) $paymentDoc;
    $hasWaiver = (bool) $booking->waiver_accepted_at;

    // Determine initial active step based on progress
    $initialStep = 1;
    if ($booking->guest_roster && count($booking->guest_roster) >= max(1, $guestCount - 1)) {
        $initialStep = 2;
    }
    if ($hasAllIds && $hasSelfie) {
        $initialStep = 3;
    }
    if ($hasAllIds && $hasSelfie && $hasPayment) {
        $initialStep = 4;
    }
    if ($hasAllIds && $hasSelfie && $hasPayment && $hasWaiver) {
        $initialStep = 5;
    }
    if (request()->has('step')) {
        $initialStep = (int) request('step');
    }
@endphp

<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8 px-4 sm:px-6 lg:px-8 transition-colors"
     x-data="{
        currentStep: {{ $initialStep }},
        guestCount: {{ $guestCount }},
        leadName: `{{ old('lead_name', $booking->guest_name) }}`,
        leadPhone: `{{ old('lead_phone', $booking->guest_phone) }}`,
        leadEmail: `{{ old('lead_email', $booking->guest_email) }}`,
        leadIdType: `{{ old('lead_id_type', 'Philippine National ID (PhilSys)') }}`,
        
        // Modal / Lightbox for viewing full uncropped ID pictures
        modalImage: null,
        modalTitle: '',
        openModal(url, title) {
            this.modalImage = url;
            this.modalTitle = title;
        },
        closeModal() {
            this.modalImage = null;
            this.modalTitle = '';
        },

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            const queryStep = urlParams.get('step');
            if (queryStep && [1, 2, 3, 4, 5].includes(parseInt(queryStep))) {
                this.currentStep = parseInt(queryStep);
                localStorage.setItem('directstay_step_{{ $booking->booking_code }}', queryStep);
                return;
            }
            const savedStep = localStorage.getItem('directstay_step_{{ $booking->booking_code }}');
            if (savedStep && [1, 2, 3, 4, 5].includes(parseInt(savedStep))) {
                this.currentStep = parseInt(savedStep);
            }
        },

        goToStep(stepNum) {
            this.currentStep = stepNum;
            localStorage.setItem('directstay_step_{{ $booking->booking_code }}', stepNum);
            const url = new URL(window.location);
            url.searchParams.set('step', stepNum);
            window.history.replaceState({}, '', url);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        
        // Companion data array initialized for companion slots
        companions: [
            @for($i = 1; $i < $guestCount; $i++)
                @php
                    $savedComp = $booking->guest_roster[$i - 1] ?? null;
                @endphp
                {
                    name: `{{ old('companion_names.'.($i-1), $savedComp['name'] ?? '') }}`,
                    relationship: `{{ old('companion_relationships.'.($i-1), $savedComp['relationship'] ?? ($i === 1 ? 'Spouse' : 'Family / Companion')) }}`,
                    idType: `{{ old('companion_id_types.'.($i-1), $savedComp['id_type'] ?? 'Philippine National ID (PhilSys)') }}`
                }{{ $i < $guestCount - 1 ? ',' : '' }}
            @endfor
        ],

        copiedGcash: false,
        copyGcash(text) {
            navigator.clipboard.writeText(text);
            this.copiedGcash = true;
            setTimeout(() => { this.copiedGcash = false; }, 2500);
        },

        autofillCompanions() {
            const demoNames = [
                { name: 'Maria Santos Batungbakal', rel: 'Spouse', id: 'Driver\'s License' },
                { name: 'Carlos Miguel Batungbakal', rel: 'Sibling', id: 'Philippine Passport' },
                { name: 'Jessica Joy Cruz', rel: 'Colleague', id: 'UMID Card' },
                { name: 'Gabriel Antonio Reyes', rel: 'Friend', id: 'Postal ID' }
            ];
            this.companions.forEach((comp, idx) => {
                const sample = demoNames[idx % demoNames.length];
                comp.name = sample.name;
                comp.relationship = sample.rel;
                comp.idType = sample.id;
            });
        },

        calculateProgress() {
            let p = 0;
            @if(!empty($booking->guest_roster) || $guestCount === 1) p += 20; @endif
            @if($hasAllIds && $hasSelfie) p += 30; @elseif($governmentIds->count() > 0 || $hasSelfie) p += 15; @endif
            @if($hasPayment) p += 25; @endif
            @if($hasWaiver) p += 25; @endif
            return Math.min(100, p);
        }
     }">

    <!-- ======================================================================= -->
    <!-- 1. FINTECH HEADER                                                       -->
    <!-- ======================================================================= -->
    <div class="max-w-5xl mx-auto mb-6">
        <div class="flex items-center justify-between pb-5 border-b border-slate-200 dark:border-slate-800">
            <!-- Security Shield & Brand -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-blue-600/30 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm sm:text-base font-black tracking-tight text-slate-900 dark:text-white">DirectStay Identity Vault</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            256-Bit Encrypted
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Urban Deca Homes Ortigas &bull; Pasig PMO Gate Pass Protocol</p>
                </div>
            </div>

            <!-- Booking Ref & Exit -->
            <div class="flex items-center gap-3 text-right">
                <div class="hidden sm:block">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Reservation Ref</span>
                    <span class="font-mono text-xs font-black text-slate-900 dark:text-white">{{ $booking->booking_code }}</span>
                </div>
                <a href="{{ route('customer.bookings') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <span>My Bookings</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Banners -->
    <div class="max-w-5xl mx-auto mb-6">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-200 font-semibold flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200 font-semibold flex items-center gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/80 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200 space-y-1 shadow-xs">
                <p class="font-bold flex items-center gap-2">
                    <span>⚠️</span> Please correct the following before continuing:
                </p>
                <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-700 dark:text-rose-300">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- ======================================================================= -->
    <!-- 2. PHASE-BY-PHASE STEPPER NAVIGATION (STEPS 1 - 5)                     -->
    <!-- ======================================================================= -->
    <div class="max-w-5xl mx-auto mb-8">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-5 sm:p-6 transition-colors">
            
            <!-- Progress Top Info -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                <div>
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        Building Security &amp; PMO Clearance Stepper
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Tower: <strong class="text-slate-800 dark:text-slate-200">{{ $building->name }}</strong> &bull; Unit: <strong class="text-blue-600 font-mono">{{ $unit->unit_number }}</strong> &bull; Occupancy: <strong class="text-slate-800 dark:text-slate-200">{{ $guestCount }} Guests ({{ $guestCount }} IDs Required)</strong>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400 tabular-nums" x-text="calculateProgress() + '% Clearance Complete'"></span>
                    @if($booking->status === 'confirmed' || $booking->status === 'checked_in')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800">
                            ✓ Gate Pass Approved
                        </span>
                    @endif
                </div>
            </div>

            <!-- Segmented Stepper Phase Tabs -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                <!-- Step 1 Tab -->
                <button type="button" @click="goToStep(1)"
                        class="p-2.5 rounded-2xl text-left transition-all border cursor-pointer"
                        :class="currentStep === 1 
                            ? 'bg-blue-50/80 dark:bg-blue-950/60 border-blue-500 ring-2 ring-blue-500/20' 
                            : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <div class="flex items-center justify-between">
                        <span class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center"
                              :class="currentStep === 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'">
                            1
                        </span>
                        @if(!empty($booking->guest_roster) || $guestCount === 1)
                            <span class="text-emerald-600 dark:text-emerald-400 text-xs font-bold">✓</span>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white mt-1.5 truncate">Guest Roster</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">{{ $guestCount }} Profiles</p>
                </button>

                <!-- Step 2 Tab -->
                <button type="button" @click="goToStep(2)"
                        class="p-2.5 rounded-2xl text-left transition-all border cursor-pointer"
                        :class="currentStep === 2 
                            ? 'bg-blue-50/80 dark:bg-blue-950/60 border-blue-500 ring-2 ring-blue-500/20' 
                            : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <div class="flex items-center justify-between">
                        <span class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center"
                              :class="currentStep === 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'">
                            2
                        </span>
                        @if($hasAllIds && $hasSelfie)
                            <span class="text-emerald-600 dark:text-emerald-400 text-xs font-bold">✓</span>
                        @else
                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">{{ $governmentIds->count() }}/{{ $guestCount }}</span>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white mt-1.5 truncate">Identity Vault</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">{{ $guestCount }} IDs + Selfie</p>
                </button>

                <!-- Step 3 Tab -->
                <button type="button" @click="goToStep(3)"
                        class="p-2.5 rounded-2xl text-left transition-all border cursor-pointer"
                        :class="currentStep === 3 
                            ? 'bg-blue-50/80 dark:bg-blue-950/60 border-blue-500 ring-2 ring-blue-500/20' 
                            : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <div class="flex items-center justify-between">
                        <span class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center"
                              :class="currentStep === 3 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'">
                            3
                        </span>
                        @if($hasPayment)
                            <span class="text-emerald-600 dark:text-emerald-400 text-xs font-bold">✓</span>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white mt-1.5 truncate">Advance Deposit</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">₱1,000 GCash</p>
                </button>

                <!-- Step 4 Tab -->
                <button type="button" @click="goToStep(4)"
                        class="p-2.5 rounded-2xl text-left transition-all border cursor-pointer"
                        :class="currentStep === 4 
                            ? 'bg-blue-50/80 dark:bg-blue-950/60 border-blue-500 ring-2 ring-blue-500/20' 
                            : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <div class="flex items-center justify-between">
                        <span class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center"
                              :class="currentStep === 4 ? 'bg-blue-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'">
                            4
                        </span>
                        @if($hasWaiver)
                            <span class="text-emerald-600 dark:text-emerald-400 text-xs font-bold">✓</span>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white mt-1.5 truncate">Rules &amp; Lease</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Deca PMO Rules</p>
                </button>

                <!-- Step 5 Tab -->
                <button type="button" @click="goToStep(5)"
                        class="col-span-2 sm:col-span-1 p-2.5 rounded-2xl text-left transition-all border cursor-pointer"
                        :class="currentStep === 5 
                            ? 'bg-emerald-50/80 dark:bg-emerald-950/60 border-emerald-500 ring-2 ring-emerald-500/20' 
                            : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                    <div class="flex items-center justify-between">
                        <span class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center"
                              :class="currentStep === 5 ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'">
                            5
                        </span>
                        <span class="text-xs">📋</span>
                    </div>
                    <p class="text-xs font-bold text-slate-900 dark:text-white mt-1.5 truncate">Final Review</p>
                    <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Summary &amp; Pass</p>
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 3. STEP 1: GUEST INFORMATION & COMPANION ROSTER FORM                    -->
    <!-- ======================================================================= -->
    <div x-show="currentStep === 1" x-cloak class="max-w-5xl mx-auto space-y-6">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 mb-2">
                        <span>Phase 1 of 5</span> &bull; <span>Building Administration Occupant Registry</span>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        Guest Information &amp; Roster ({{ $guestCount }} Guests)
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Pasig City Property Management Office (PMO) requires exact legal names of all occupants entering the tower lobby gates.
                    </p>
                </div>

                @if($guestCount > 1)
                    <button type="button" @click="autofillCompanions()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 border border-blue-200 dark:border-blue-800 transition-all cursor-pointer shrink-0">
                        <span>⚡ Quick-Fill Companion Details</span>
                    </button>
                @endif
            </div>

            <form action="{{ route('compliance.roster', $booking->booking_code) }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="step" value="2">

                <!-- Guest 1: Lead Guest (Auto-filled) -->
                <div class="p-5 rounded-2xl bg-blue-50/40 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/80 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center">1</span>
                            <div>
                                <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
                                    Lead Guest &bull; Primary Booker
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Auto-filled from reservation details</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-900/80 text-blue-800 dark:text-blue-200">
                            Auto-filled
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Full Legal Name</label>
                            <input type="text" name="lead_name" x-model="leadName" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Mobile Contact</label>
                            <input type="text" name="lead_phone" x-model="leadPhone" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                            <input type="email" name="lead_email" x-model="leadEmail" readonly
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-mono">
                        </div>
                    </div>
                </div>

                <!-- Companion Guests 2..N -->
                @if($guestCount > 1)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Accompanying Companions ({{ $guestCount - 1 }} Additional Occupant{{ $guestCount > 2 ? 's' : '' }})
                            </h3>
                            <span class="text-[11px] text-slate-400">All occupants must be named</span>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(companion, idx) in companions" :key="idx">
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-black flex items-center justify-center" x-text="idx + 2"></span>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="'Companion ' + (idx + 2)"></span>
                                        </div>
                                        <span class="text-[10px] text-slate-400" x-text="'ID will be uploaded in Step 2'"></span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">Full Legal Name *</label>
                                            <input type="text" name="companion_names[]" x-model="companion.name" placeholder="First Name Last Name" required
                                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">Relationship</label>
                                            <select name="companion_relationships[]" x-model="companion.relationship"
                                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                                <option value="Spouse">Spouse / Partner</option>
                                                <option value="Child">Child / Dependent</option>
                                                <option value="Sibling">Sibling / Relative</option>
                                                <option value="Parent">Parent</option>
                                                <option value="Friend">Friend</option>
                                                <option value="Colleague">Colleague</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">ID Type to Present</label>
                                            <select name="companion_id_types[]" x-model="companion.idType"
                                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                                <option value="Philippine National ID (PhilSys)">National ID (PhilSys)</option>
                                                <option value="Driver's License">Driver's License</option>
                                                <option value="Philippine Passport">Passport</option>
                                                <option value="UMID Card">UMID / SSS</option>
                                                <option value="Postal ID">Postal ID</option>
                                                <option value="PRC ID">PRC License</option>
                                                <option value="School / Company ID">School / Company ID</option>
                                                <option value="Other Valid ID">Other Valid ID</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="goToStep(2)"
                            class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Skip to Step 2 &rarr;
                    </button>
                    <button type="submit"
                            class="px-6 py-3 rounded-2xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 transition-all cursor-pointer">
                        Save Guest Profiles &amp; Proceed to Step 2 (Identity Vault) &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 4. STEP 2: DIRECTSTAY IDENTITY VAULT (GOV ID PER GUEST + SELFIE)        -->
    <!-- ======================================================================= -->
    <div x-show="currentStep === 2" x-cloak class="max-w-5xl mx-auto space-y-6">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            
            <!-- Vault Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 mb-2">
                        <span>Phase 2 of 5</span> &bull; <span>DirectStay Identity Vault</span>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        Upload Government Photo IDs ({{ $guestCount }} Guests)
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Building administration requires <strong>1 valid photo ID for each of your {{ $guestCount }} registered occupants</strong>, plus 1 verification selfie for the primary booker.
                    </p>
                </div>

                <div class="shrink-0 text-left sm:text-right">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold {{ $hasAllIds && $hasSelfie ? 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800' : 'bg-blue-50 dark:bg-blue-950/80 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800' }}">
                        <span>Vault Status:</span>
                        <strong>{{ $governmentIds->count() }} of {{ $guestCount }} IDs Uploaded</strong>
                    </span>
                </div>
            </div>

            <!-- Vault Security Guarantee Note -->
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-start gap-3 text-xs text-slate-600 dark:text-slate-300">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="space-y-0.5">
                    <strong class="text-slate-900 dark:text-white font-bold">Privacy &amp; Data Protection:</strong>
                    <p class="text-[11px] leading-relaxed">
                        Uploaded ID files are encrypted and accessible exclusively by DirectStay and the Urban Deca PMO gate security. They are never shared publicly and are purged in accordance with Data Privacy regulations.
                    </p>
                </div>
            </div>

            <!-- DYNAMIC OCCUPANT ID UPLOAD CARDS (1 CARD PER GUEST) -->
            <div class="space-y-5">
                @for($idx = 0; $idx < $guestCount; $idx++)
                    @php
                        $existingId = $governmentIds->get($idx);
                        $occupantName = $idx === 0 
                            ? $booking->guest_name . ' (Lead Guest)'
                            : ($booking->guest_roster[$idx - 1]['name'] ?? 'Companion ' . ($idx + 1));
                        $occupantRole = $idx === 0 
                            ? 'Primary Booker & Lease Signer' 
                            : ($booking->guest_roster[$idx - 1]['relationship'] ?? 'Accompanying Guest');
                    @endphp

                    <div class="p-5 rounded-3xl border {{ $existingId ? 'bg-emerald-50/30 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-slate-50/60 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700' }} transition-colors"
                         x-data="{
                            previewUrl: null,
                            fileName: '',
                            fileSize: '',
                            handleFile(e) {
                                const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
                                if (file) {
                                    this.fileName = file.name;
                                    this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                                    const reader = new FileReader();
                                    reader.onload = (event) => { this.previewUrl = event.target.result; };
                                    reader.readAsDataURL(file);
                                }
                            }
                         }">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl {{ $existingId ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white' }} font-black text-xs flex items-center justify-center shadow-xs">
                                    {{ $idx + 1 }}
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                            {{ $occupantName }}
                                        </h3>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                            {{ $occupantRole }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Government Photo ID (Passport, PhilSys, Driver's License, UMID)</p>
                                </div>
                            </div>

                            <div>
                                @if($existingId)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-700">
                                        <span>✓ Registered in Vault</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/80 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-700">
                                        <span>Pending Upload</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- If ID already registered in vault, display thumbnail with full view modal -->
                        @if($existingId)
                            <div class="p-3 bg-white dark:bg-slate-900 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 mb-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $existingId->id]) }}', '{{ $occupantName }} - Government ID')"
                                            class="w-16 h-16 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 bg-slate-100 dark:bg-slate-950 p-1 cursor-pointer hover:opacity-85 transition-opacity" title="Click to view full picture">
                                        <img src="{{ route('compliance.document', [$booking->booking_code, $existingId->id]) }}" 
                                             alt="ID Thumbnail"
                                             class="w-full h-full object-contain">
                                    </button>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ $existingId->original_filename }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                            {{ $existingId->notes ?? 'Government Photo ID' }} &bull; Stored securely
                                        </p>
                                        <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $existingId->id]) }}', '{{ $occupantName }} - Government ID')"
                                                class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline mt-0.5 inline-block cursor-pointer">
                                            View Full Picture &nearr;
                                        </button>
                                    </div>
                                </div>
                                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 shrink-0">Active ID ✓</span>
                            </div>
                        @endif

                        <!-- Dedicated Upload Form for This Occupant -->
                        <form action="{{ route('compliance.identity', $booking->booking_code) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="hidden" name="step" value="2">
                            <input type="hidden" name="occupant_index" value="{{ $idx }}">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">ID Document Type</label>
                                    <select name="id_type" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                                        <option value="Philippine National ID (PhilSys)">National ID (PhilSys)</option>
                                        <option value="Driver's License">Driver's License</option>
                                        <option value="Philippine Passport">Passport</option>
                                        <option value="UMID / SSS">UMID / SSS</option>
                                        <option value="Postal ID">Postal ID</option>
                                        <option value="PRC ID">PRC License</option>
                                        <option value="Other Government ID">Other Government ID</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                        {{ $existingId ? 'Upload New Image to Replace ID' : 'Select or Drop ID Image' }}
                                    </label>
                                    
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 relative">
                                            <input type="file" name="gov_id_single" accept="image/*" required @change="handleFile($event)"
                                                   class="w-full text-xs file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 text-slate-600 dark:text-slate-300 cursor-pointer">
                                        </div>
                                        <button type="submit"
                                                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 dark:bg-slate-100 dark:text-slate-900 hover:bg-slate-800 transition-all shrink-0 cursor-pointer">
                                            {{ $existingId ? 'Replace ID' : 'Register ID' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Local Preview when chosen -->
                            <template x-if="previewUrl">
                                <div class="p-3 bg-blue-50/60 dark:bg-blue-950/40 rounded-2xl border border-blue-200 dark:border-blue-800 flex items-center gap-3">
                                    <img :src="previewUrl" class="w-14 h-14 rounded-xl object-contain bg-white dark:bg-slate-900 border border-blue-300 dark:border-blue-700 shrink-0 p-1">
                                    <div class="min-w-0 flex-1">
                                        <span class="text-xs font-bold text-slate-900 dark:text-white truncate block" x-text="fileName"></span>
                                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold" x-text="'Ready to register: ' + fileSize"></span>
                                    </div>
                                </div>
                            </template>
                        </form>

                    </div>
                @endfor
            </div>

            <!-- LEAD GUEST BIOMETRIC SELFIE CARD -->
            <div class="p-5 rounded-3xl border {{ $selfieDoc ? 'bg-emerald-50/30 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-slate-50/60 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700' }} transition-colors"
                 x-data="{
                    selfiePreview: null,
                    selfieName: '',
                    handleSelfie(e) {
                        const file = e.target.files ? e.target.files[0] : null;
                        if (file) {
                            this.selfieName = file.name;
                            const reader = new FileReader();
                            reader.onload = (event) => { this.selfiePreview = event.target.result; };
                            reader.readAsDataURL(file);
                        }
                    }
                 }">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-xs">
                            🤳
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-black text-slate-900 dark:text-white">
                                    Live Biometric Verification Selfie
                                </h3>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/80 text-indigo-800 dark:text-indigo-200">
                                    Lead Guest Only
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Selfie holding government ID or clear face portrait</p>
                        </div>
                    </div>

                    <div>
                        @if($selfieDoc)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-700">
                                <span>✓ Biometric Selfie Secured</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/80 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-700">
                                <span>Pending Upload</span>
                            </span>
                        @endif
                    </div>
                </div>

                @if($selfieDoc)
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 mb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $selfieDoc->id]) }}', '{{ $booking->guest_name }} - Biometric Selfie')"
                                    class="w-16 h-16 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 bg-slate-100 dark:bg-slate-950 p-1 cursor-pointer hover:opacity-85 transition-opacity" title="Click to view full picture">
                                <img src="{{ route('compliance.document', [$booking->booking_code, $selfieDoc->id]) }}" 
                                     alt="Selfie"
                                     class="w-full h-full object-contain">
                            </button>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                    {{ $selfieDoc->original_filename }}
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Biometric facial verification &bull; Encrypted</p>
                                <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $selfieDoc->id]) }}', '{{ $booking->guest_name }} - Biometric Selfie')"
                                        class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline mt-0.5 inline-block cursor-pointer">
                                    View Full Picture &nearr;
                                </button>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 shrink-0">Active Selfie ✓</span>
                    </div>
                @endif

                <form action="{{ route('compliance.identity', $booking->booking_code) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="hidden" name="step" value="2">

                    <div class="flex items-center gap-3">
                        <div class="flex-1 relative">
                            <input type="file" name="selfie" accept="image/*" capture="user" required @change="handleSelfie($event)"
                                   class="w-full text-xs file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 text-slate-600 dark:text-slate-300 cursor-pointer">
                        </div>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 dark:bg-slate-100 dark:text-slate-900 hover:bg-slate-800 transition-all shrink-0 cursor-pointer">
                            {{ $selfieDoc ? 'Replace Selfie' : 'Upload Selfie' }}
                        </button>
                    </div>

                    <template x-if="selfiePreview">
                        <div class="p-3 bg-indigo-50/60 dark:bg-indigo-950/40 rounded-2xl border border-indigo-200 dark:border-indigo-800 flex items-center gap-3">
                            <img :src="selfiePreview" class="w-14 h-14 rounded-xl object-contain bg-white dark:bg-slate-900 border border-indigo-300 dark:border-indigo-700 shrink-0 p-1">
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-bold text-slate-900 dark:text-white truncate block" x-text="selfieName"></span>
                                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold">Selfie ready to register</span>
                            </div>
                        </div>
                    </template>
                </form>
            </div>

            <!-- Stepper Actions Navigation -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="goToStep(1)"
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    &larr; Back to Step 1 (Roster)
                </button>
                <button type="button" @click="goToStep(3)"
                        class="px-6 py-3 rounded-2xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 transition-all cursor-pointer">
                    Proceed to Step 3 (Advance Deposit) &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 5. STEP 3: ADVANCE DEPOSIT ESCROW (GCASH RECEIPT)                       -->
    <!-- ======================================================================= -->
    <div x-show="currentStep === 3" x-cloak class="max-w-5xl mx-auto space-y-6">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 mb-2">
                        <span>Phase 3 of 5</span> &bull; <span>Advance Security Deposit Escrow</span>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        Transfer Advance Deposit (₱{{ number_format($booking->advance_deposit_amount, 2) }})
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Locks your reservation dates and guarantees compliance with Deca Ortigas building regulations.
                    </p>
                </div>

                @if($paymentDoc)
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800">
                        Receipt Submitted ✓
                    </span>
                @endif
            </div>

            <!-- Host GCash Account Card with Copy Button -->
            <div class="p-5 rounded-3xl bg-blue-50/50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 space-y-3">
                <div class="flex items-center justify-between border-b border-blue-100 dark:border-blue-900 pb-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-blue-700 dark:text-blue-300">
                        Host Official GCash Transfer Account
                    </span>
                    <span class="text-[10px] font-bold text-slate-400">Direct-To-Host Escrow</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 dark:text-slate-400 block text-[11px]">GCash Account Name:</span>
                        <strong class="text-slate-900 dark:text-white font-bold text-sm">
                            {{ $booking->unit->host->name ?? 'Aurelio J. Budol Sr.' }}
                        </strong>
                    </div>
                    <div>
                        <span class="text-slate-500 dark:text-slate-400 block text-[11px]">GCash Mobile Number:</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-sm font-black text-blue-600 dark:text-blue-400">
                                {{ $booking->unit->host->phone ?? '09478847463' }}
                            </span>
                            <button type="button" @click="copyGcash('{{ $booking->unit->host->phone ?? '09478847463' }}')"
                                    class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 hover:bg-blue-200 transition-colors cursor-pointer">
                                <span x-show="!copiedGcash">Copy</span>
                                <span x-show="copiedGcash">Copied! ✓</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-500 dark:text-slate-400 block text-[11px]">Required Escrow Amount:</span>
                        <strong class="text-slate-900 dark:text-white font-mono text-sm font-black tabular-nums">
                            ₱{{ number_format($booking->advance_deposit_amount, 2) }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Existing Receipt Display if uploaded -->
            @if($paymentDoc)
                <div class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $paymentDoc->id]) }}', 'GCash Deposit Receipt')"
                                class="w-16 h-16 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 bg-slate-100 dark:bg-slate-950 p-1 cursor-pointer hover:opacity-85 transition-opacity" title="Click to view full picture">
                            <img src="{{ route('compliance.document', [$booking->booking_code, $paymentDoc->id]) }}" 
                                 alt="Payment Receipt"
                                 class="w-full h-full object-contain">
                        </button>
                        <div>
                            <p class="text-xs font-bold text-slate-900 dark:text-white">
                                {{ $paymentDoc->original_filename }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                {{ $paymentDoc->notes ?? 'GCash Deposit Screenshot' }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">Status: {{ ucfirst($booking->payment_status) }}</span>
                                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                                <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $paymentDoc->id]) }}', 'GCash Deposit Receipt')"
                                        class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                    View Full Picture &nearr;
                                </button>
                            </div>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">Proof Locked in Vault ✓</span>
                </div>
            @endif

            <!-- Upload Receipt Form -->
            <form action="{{ route('compliance.payment', $booking->booking_code) }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                  x-data="{
                    receiptPreview: null,
                    receiptName: '',
                    handleReceipt(e) {
                        const file = e.target.files ? e.target.files[0] : null;
                        if (file) {
                            this.receiptName = file.name;
                            const reader = new FileReader();
                            reader.onload = (event) => { this.receiptPreview = event.target.result; };
                            reader.readAsDataURL(file);
                        }
                    }
                  }">
                @csrf
                <input type="hidden" name="step" value="3">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            GCash / Bank Reference Number
                        </label>
                        <input type="text" name="reference_number" placeholder="e.g. 1002 9384 1928"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Payment Screenshot (Receipt) *
                        </label>
                        <input type="file" name="payment_receipt" accept="image/*" required @change="handleReceipt($event)"
                               class="w-full text-xs file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 text-slate-600 dark:text-slate-300 cursor-pointer">
                    </div>
                </div>

                <template x-if="receiptPreview">
                    <div class="p-3 bg-blue-50/60 dark:bg-blue-950/40 rounded-2xl border border-blue-200 dark:border-blue-800 flex items-center gap-3">
                        <img :src="receiptPreview" class="w-14 h-14 rounded-xl object-contain bg-white dark:bg-slate-900 border border-blue-300 dark:border-blue-700 shrink-0 p-1">
                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-bold text-slate-900 dark:text-white truncate block" x-text="receiptName"></span>
                            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">Screenshot ready to upload</span>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="goToStep(2)"
                            class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        &larr; Back to Step 2 (Identity Vault)
                    </button>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="goToStep(4)"
                                class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                            Skip to Step 4 &rarr;
                        </button>
                        <button type="submit"
                                class="px-6 py-3 rounded-2xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 transition-all cursor-pointer">
                            {{ $paymentDoc ? 'Re-upload Receipt & Save' : 'Upload Payment Receipt & Proceed' }} &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 6. STEP 4: STAYCATION LEASE AGREEMENT & HOUSE RULES                     -->
    <!-- ======================================================================= -->
    <div x-show="currentStep === 4" x-cloak class="max-w-5xl mx-auto space-y-6">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 mb-2">
                        <span>Phase 4 of 5</span> &bull; <span>Building Regulations &amp; Lease Terms</span>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        House Rules &amp; Digital Rental Agreement
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Binding short-term residential contract for {{ $unit->title }} ({{ $building->name }} Unit {{ $unit->unit_number }}).
                    </p>
                </div>

                @if($booking->waiver_accepted_at)
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800">
                        Contract Executed ✓
                    </span>
                @endif
            </div>

            <!-- Urban Deca PMO Rules Summary Grid -->
            <div class="p-5 rounded-3xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs space-y-3">
                <div class="flex items-center justify-between font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-2">
                    <span class="uppercase tracking-wider text-[11px] text-blue-600 dark:text-blue-400 font-extrabold">Agreement Key Terms &amp; Policies</span>
                    <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">Unit {{ $unit->unit_number }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-600 dark:text-slate-300">
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                        <strong class="text-slate-900 dark:text-white block mb-0.5">🚭 Strictly No Smoking inside Unit</strong>
                        Violation incurs an automatic building administration fine of <strong>₱2,000.00</strong>.
                    </div>
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                        <strong class="text-slate-900 dark:text-white block mb-0.5">🗑️ Clean As You Go (CLAYGO)</strong>
                        Dispose of trash bags at the ground floor bin depot. Unthrown garbage penalty: <strong>₱500.00</strong>.
                    </div>
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                        <strong class="text-slate-900 dark:text-white block mb-0.5">🔑 Elevator RFID &amp; Smart Lock Keys</strong>
                        Lost RFID elevator pass or unit keys require a replacement fee of <strong>₱500.00</strong> each.
                    </div>
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                        <strong class="text-slate-900 dark:text-white block mb-0.5">⏰ Check-In (2:00 PM) &amp; Check-Out (12:00 NN)</strong>
                        Strict check-in and check-out windows enforced by building lobby security.
                    </div>
                </div>
            </div>

            <!-- Agreement Form -->
            <form action="{{ route('compliance.waiver', $booking->booking_code) }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="step" value="5">

                <div class="p-4 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 flex items-start gap-3">
                    <input type="checkbox" name="agree_rules" id="agreeRules" value="1" {{ $booking->waiver_accepted_at ? 'checked' : '' }} required
                           class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 mt-0.5 cursor-pointer">
                    <label for="agreeRules" class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed cursor-pointer font-medium">
                        I, <strong class="text-slate-900 dark:text-white">{{ $booking->guest_name }}</strong>, certify that all provided details, companion names, and government documents are authentic. I hereby digitally execute the <strong>DirectStay Staycation Rental Agreement</strong>, accept all PMO regulations of <strong>{{ $building->name }}</strong>, and authorize deposit deductions for any property infractions.
                    </label>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="goToStep(3)"
                            class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        &larr; Back to Step 3 (Deposit)
                    </button>
                    <button type="submit"
                            class="px-6 py-3 rounded-2xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 transition-all cursor-pointer">
                        Execute Agreement &amp; Proceed to Step 5 (Final Review) &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 7. STEP 5: FINAL REVIEW INFORMATION & GATE PASS CLEARANCE               -->
    <!-- ======================================================================= -->
    <div x-show="currentStep === 5" x-cloak class="max-w-5xl mx-auto space-y-6">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 transition-colors">
            
            <!-- Final Review Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 mb-2">
                        <span>Phase 5 of 5</span> &bull; <span>Final Review &amp; Security Gate Clearance</span>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        Final Review Information &amp; Official Gate Pass
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Review all submitted occupant credentials, identity vault records, and PMO clearance documentation.
                    </p>
                </div>

                <div>
                    @if($booking->status === 'confirmed' || $booking->status === 'checked_in')
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl text-xs font-bold bg-emerald-500 text-white shadow-md shadow-emerald-500/25">
                            ✓ Gate Pass Approved
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl text-xs font-bold bg-amber-50 dark:bg-amber-950/80 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Awaiting Host Sign-off
                        </span>
                    @endif
                </div>
            </div>

            <!-- REVIEW BENTO CARD 1: RESERVATION OVERVIEW -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Condo &amp; Tower</span>
                    <strong class="text-slate-900 dark:text-white text-sm block mt-0.5">{{ $unit->title }}</strong>
                    <span class="text-slate-500 dark:text-slate-400 font-mono">{{ $building->name }} &bull; Unit {{ $unit->unit_number }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Stay Dates</span>
                    <strong class="text-slate-900 dark:text-white text-sm block mt-0.5">
                        {{ $booking->check_in_date->format('M d') }} &ndash; {{ $booking->check_out_date->format('M d, Y') }}
                    </strong>
                    <span class="text-slate-500 dark:text-slate-400">{{ $booking->nights_count }} Nights &bull; In: 2PM / Out: 12NN</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Occupants Roster</span>
                    <strong class="text-slate-900 dark:text-white text-sm block mt-0.5">
                        {{ $guestCount }} Registered Guests
                    </strong>
                    <span class="text-slate-500 dark:text-slate-400">Lead: {{ $booking->guest_name }}</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Financial Summary</span>
                    <strong class="text-blue-600 dark:text-blue-400 text-sm font-black font-mono block mt-0.5">
                        ₱{{ number_format($booking->total_amount, 2) }}
                    </strong>
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Deposit: ₱{{ number_format($booking->advance_deposit_amount, 2) }}</span>
                </div>
            </div>

            <!-- REVIEW BENTO CARD 2: GUEST IDENTITY VAULT AUDIT WITH UNCROPPED FULL PREVIEWS -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        Identity Vault Audit ({{ $guestCount }} Occupant IDs + Biometric Selfie)
                    </h3>
                    <span class="text-xs font-bold {{ $hasAllIds && $hasSelfie ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $governmentIds->count() }} of {{ $guestCount }} IDs Registered
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ min(4, $guestCount + 1) }} gap-4 text-xs">
                    @for($i = 0; $i < $guestCount; $i++)
                        @php
                            $idDoc = $governmentIds->get($i);
                            $name = $i === 0 
                                ? $booking->guest_name . ' (Lead)'
                                : ($booking->guest_roster[$i - 1]['name'] ?? 'Companion ' . ($i + 1));
                        @endphp
                        <div class="p-4 rounded-3xl border {{ $idDoc ? 'bg-emerald-50/30 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50/30 dark:bg-rose-950/20 border-rose-200 dark:border-rose-800' }} flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-black uppercase text-slate-400">Guest {{ $i + 1 }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $idDoc ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200' }}">
                                        {{ $idDoc ? '✓ In Vault' : 'Missing ID' }}
                                    </span>
                                </div>

                                @if($idDoc)
                                    <!-- Uncropped Container with Click-To-Enlarge Lightbox -->
                                    <div class="relative group/preview overflow-hidden rounded-2xl bg-white dark:bg-slate-900 p-2 border border-slate-200 dark:border-slate-700 mb-2 cursor-pointer shadow-2xs"
                                         @click="openModal('{{ route('compliance.document', [$booking->booking_code, $idDoc->id]) }}', '{{ $name }} - Government Photo ID')">
                                        <img src="{{ route('compliance.document', [$booking->booking_code, $idDoc->id]) }}" 
                                             alt="ID {{ $i + 1 }}"
                                             class="w-full h-44 object-contain mx-auto rounded-xl">
                                        <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover/preview:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5 backdrop-blur-[2px] rounded-2xl">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                            <span>Click to View Full Size</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] mb-2">
                                        <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $idDoc->id]) }}', '{{ $name }} - Government Photo ID')"
                                                class="font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <span>🔍 Enlarge Picture</span>
                                        </button>
                                        <a href="{{ route('compliance.document', [$booking->booking_code, $idDoc->id]) }}" target="_blank"
                                           class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                            Open File &nearr;
                                        </a>
                                    </div>
                                @else
                                    <div class="w-full h-44 rounded-2xl bg-slate-100 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 flex flex-col items-center justify-center text-slate-400 mb-2">
                                        <span class="text-2xl mb-1">🪪</span>
                                        <span class="text-xs font-semibold">No ID picture uploaded</span>
                                        <button type="button" @click="goToStep(2)" class="text-[11px] text-blue-600 dark:text-blue-400 font-bold underline mt-1">Upload in Step 2</button>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <p class="font-black text-slate-900 dark:text-white truncate text-xs">{{ $name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $idDoc->original_filename ?? 'Action required in Step 2' }}</p>
                            </div>
                        </div>
                    @endfor

                    <!-- Biometric Selfie Card -->
                    <div class="p-4 rounded-3xl border {{ $selfieDoc ? 'bg-emerald-50/30 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50/30 dark:bg-rose-950/20 border-rose-200 dark:border-rose-800' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black uppercase text-slate-400">Biometric Selfie</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $selfieDoc ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200' }}">
                                    {{ $selfieDoc ? '✓ Verified' : 'Missing' }}
                                </span>
                            </div>

                            @if($selfieDoc)
                                <div class="relative group/preview overflow-hidden rounded-2xl bg-white dark:bg-slate-900 p-2 border border-slate-200 dark:border-slate-700 mb-2 cursor-pointer shadow-2xs"
                                     @click="openModal('{{ route('compliance.document', [$booking->booking_code, $selfieDoc->id]) }}', '{{ $booking->guest_name }} - Biometric Selfie')">
                                    <img src="{{ route('compliance.document', [$booking->booking_code, $selfieDoc->id]) }}" 
                                         alt="Selfie"
                                         class="w-full h-44 object-contain mx-auto rounded-xl">
                                    <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover/preview:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5 backdrop-blur-[2px] rounded-2xl">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                        <span>Click to View Full Size</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] mb-2">
                                    <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $selfieDoc->id]) }}', '{{ $booking->guest_name }} - Biometric Selfie')"
                                            class="font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                        <span>🔍 Enlarge Picture</span>
                                    </button>
                                    <a href="{{ route('compliance.document', [$booking->booking_code, $selfieDoc->id]) }}" target="_blank"
                                       class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                        Open File &nearr;
                                    </a>
                                </div>
                            @else
                                <div class="w-full h-44 rounded-2xl bg-slate-100 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 flex flex-col items-center justify-center text-slate-400 mb-2">
                                    <span class="text-2xl mb-1">🤳</span>
                                    <span class="text-xs font-semibold">No selfie yet</span>
                                    <button type="button" @click="goToStep(2)" class="text-[11px] text-blue-600 dark:text-blue-400 font-bold underline mt-1">Upload in Step 2</button>
                                </div>
                            @endif
                        </div>

                        <div>
                            <p class="font-black text-slate-900 dark:text-white truncate text-xs">{{ $booking->guest_name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ $selfieDoc->original_filename ?? 'Action required in Step 2' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REVIEW BENTO CARD 3: ESCROW & LEASE SIGNATURE AUDIT -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- Escrow Audit -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white">Security Deposit Escrow</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $paymentDoc ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                            {{ $paymentDoc ? 'Proof Uploaded ✓' : 'Pending Upload' }}
                        </span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                        Amount: <strong>₱{{ number_format($booking->advance_deposit_amount, 2) }}</strong> &bull; Status: <strong class="text-blue-600">{{ ucfirst($booking->payment_status) }}</strong>
                    </p>
                    @if($paymentDoc)
                        <div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 pt-2 mt-2">
                            <span class="text-[11px] font-mono text-slate-600 dark:text-slate-300">
                                {{ $paymentDoc->notes ?? 'GCash Transfer Receipt Stored' }}
                            </span>
                            <button type="button" @click="openModal('{{ route('compliance.document', [$booking->booking_code, $paymentDoc->id]) }}', 'GCash Deposit Receipt Proof')"
                                    class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                View Receipt &nearr;
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Lease Audit with Clean Philippine Local Time Format -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 dark:text-white">Rental Agreement Execution</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $booking->waiver_accepted_at ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                            {{ $booking->waiver_accepted_at ? 'Executed ✓' : 'Awaiting Signature' }}
                        </span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                        Signer: <strong>{{ $booking->guest_name }}</strong>
                    </p>
                    @if($booking->waiver_accepted_at)
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                            Signed on {{ $booking->waiver_accepted_at->timezone('Asia/Manila')->format('M d, Y') }} &bull; {{ $booking->waiver_accepted_at->timezone('Asia/Manila')->format('h:i A') }} PHT
                        </p>
                    @endif
                </div>
            </div>

            <!-- GATE PASS DOWNLOAD ACTIONS (WHEN CONFIRMED) -->
            <div class="p-6 rounded-3xl {{ $booking->status === 'confirmed' || $booking->status === 'checked_in' ? 'bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-500' : 'bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700' }} transition-all">
                @if($booking->status === 'confirmed' || $booking->status === 'checked_in')
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 mb-2">
                                <span>🎉</span> PMO Security Gate Pass Ready
                            </span>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">
                                Official Building Gate Clearance Pass (Deca Guest Form)
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Present this digital pass or printed copy at the {{ $building->name }} lobby security guard post upon arrival.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="{{ route('compliance.downloadGatePass', $booking->booking_code) }}"
                               class="px-5 py-3 rounded-2xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/30 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Download PMO Gate Pass PDF</span>
                            </a>
                            <a href="{{ route('compliance.downloadRentalAgreement', $booking->booking_code) }}"
                               class="px-4 py-3 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2">
                                <span>Rental Contract</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Clearance Documents Locked in Identity Vault
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                                Your {{ $guestCount }} occupant photo IDs, biometric selfie, GCash deposit proof, and signed agreement are safely received in our encrypted vault. The host will review and approve your PMO gate pass shortly.
                            </p>
                        </div>
                        <a href="{{ route('customer.bookings') }}"
                           class="px-5 py-2.5 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 transition-all shrink-0">
                            Return to My Bookings &rarr;
                        </a>
                    </div>
                @endif
            </div>

            <!-- Footer navigation -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="goToStep(4)"
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    &larr; Back to Step 4 (Rules &amp; Lease)
                </button>
                <button type="button" @click="goToStep(1)"
                        class="px-5 py-2.5 rounded-xl text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                    Edit Guest Information &crarr;
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- LIGHTBOX / FULL-SCREEN IMAGE PREVIEW MODAL                              -->
    <!-- ======================================================================= -->
    <div x-show="modalImage"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         @keydown.escape.window="closeModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        
        <div @click.outside="closeModal()"
             class="relative max-w-4xl w-full max-h-[90vh] bg-slate-900 rounded-3xl overflow-hidden border border-slate-700 shadow-2xl flex flex-col">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 px-6 border-b border-slate-800 bg-slate-900/95 text-white">
                <div>
                    <h3 class="text-sm font-bold text-white truncate" x-text="modalTitle"></h3>
                    <p class="text-[11px] text-slate-400">DirectStay Encrypted Identity Vault &bull; Full Uncropped View</p>
                </div>
                <div class="flex items-center gap-3">
                    <a :href="modalImage" target="_blank"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-blue-400 border border-slate-700 transition-colors inline-flex items-center gap-1">
                        <span>Open Original &nearr;</span>
                    </a>
                    <button type="button" @click="closeModal()"
                            class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center text-lg font-bold transition-colors cursor-pointer">
                        &times;
                    </button>
                </div>
            </div>

            <!-- Modal Image Body (Uncropped, Full view) -->
            <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-black/50">
                <img :src="modalImage" :alt="modalTitle" class="max-w-full max-h-[75vh] object-contain rounded-xl shadow-lg">
            </div>
        </div>
    </div>

</div>
@endsection
