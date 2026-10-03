@extends('layouts.app')

@section('title', 'Security Clearance & ID Verification - DirectStay')
@section('hideNav', 'true')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8 px-4 sm:px-6 lg:px-8"
     x-data="{
        calculateProgress() {
            let p = 25; // Reservation locked = 25%
            @if($governmentIds->count() === $booking->guest_count && $selfieDoc) p += 25; @endif
            @if($paymentDoc) p += 25; @endif
            @if($booking->waiver_accepted_at) p += 25; @endif
            return p;
        }
     }">

    <!-- ======================================================================= -->
    <!-- 1. DISTRACTION-FREE FINTECH HEADER (BANKING VERIFICATION FOCUS) -->
    <!-- ======================================================================= -->
    <div class="max-w-4xl mx-auto mb-8">
        <div class="flex items-center justify-between pb-6 border-b border-slate-200/80">
            <!-- Security Shield & Brand -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-blue-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black tracking-tight text-slate-900">DirectStay Identity Vault</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            256-Bit SSL Encrypted
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400">Urban Deca Homes Ortigas &bull; Pasig PMO Gate Pass Protocol</p>
                </div>
            </div>

            <!-- Booking Ref & Exit -->
            <div class="flex items-center gap-3 text-right">
                <div class="hidden sm:block">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Reservation Ref</span>
                    <span class="font-mono text-xs font-bold text-slate-900">{{ $booking->booking_code }}</span>
                </div>
                <a href="{{ route('customer.bookings') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 shadow-2xs hover:bg-slate-50 transition-all">
                    <span>Exit Flow</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 2. STATUS & PROGRESS TRACKER (FINTECH STEPPER) -->
    <!-- ======================================================================= -->
    <div class="max-w-4xl mx-auto mb-8">
        <div class="ring-1 ring-blue-500/30 shadow-[0_0_30px_rgba(59,130,246,0.1)] rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-5">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 mb-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Mandatory Security Clearance</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Building Security Gate Pass Verification
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tower: <strong class="text-slate-800">{{ $building->name }}</strong> &bull; Unit: <strong class="text-slate-800 font-mono">{{ $unit->unit_number }}</strong> &bull; Lead Guest: <strong class="text-slate-800">{{ $booking->guest_name }}</strong>
                    </p>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-[11px] text-slate-400 font-semibold block mb-1">Clearance Status</span>
                    @if($booking->status === 'confirmed' || $booking->status === 'checked_in')
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Gate Pass Approved
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Action Required
                        </span>
                    @endif
                </div>
            </div>

            <!-- Stepper Progress Bar -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Security Clearance Progress</span>
                    <span class="text-xs font-mono font-bold text-blue-600 tabular-nums" x-text="calculateProgress() + '% Completed'"></span>
                </div>

                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden mb-5 p-0.5">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 transition-all duration-500"
                         :style="'width: ' + calculateProgress() + '%'"></div>
                </div>

                <!-- 4 Step Indicator Pills -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                    <div class="p-3 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 text-emerald-900">
                        <div class="flex items-center gap-1.5 font-bold mb-0.5">
                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                            <span>1. Dates Locked</span>
                        </div>
                        <span class="text-[10px] text-emerald-700 block">Unit Reserved</span>
                    </div>

                    <div class="p-3 rounded-2xl border transition-all {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'bg-emerald-50/80 border-emerald-200/80 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                        <div class="flex items-center gap-1.5 font-bold mb-0.5">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white' }}">
                                {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? '✓' : '2' }}
                            </span>
                            <span>2. Identity &amp; Biometric</span>
                        </div>
                        <span class="text-[10px] {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'text-emerald-700' : 'text-slate-400' }} block">
                            {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'Verified' : 'Photo ID + Selfie' }}
                        </span>
                    </div>

                    <div class="p-3 rounded-2xl border transition-all {{ $paymentDoc ? 'bg-emerald-50/80 border-emerald-200/80 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                        <div class="flex items-center gap-1.5 font-bold mb-0.5">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] {{ $paymentDoc ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }}">
                                {{ $paymentDoc ? '✓' : '3' }}
                            </span>
                            <span>3. Advance Deposit</span>
                        </div>
                        <span class="text-[10px] {{ $paymentDoc ? 'text-emerald-700' : 'text-slate-400' }} block">
                            {{ $paymentDoc ? 'Proof Received' : '₱1,000 Escrow' }}
                        </span>
                    </div>

                    <div class="p-3 rounded-2xl border transition-all {{ $booking->waiver_accepted_at ? 'bg-emerald-50/80 border-emerald-200/80 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                        <div class="flex items-center gap-1.5 font-bold mb-0.5">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] {{ $booking->waiver_accepted_at ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }}">
                                {{ $booking->waiver_accepted_at ? '✓' : '4' }}
                            </span>
                            <span>4. Lease Agreement</span>
                        </div>
                        <span class="text-[10px] {{ $booking->waiver_accepted_at ? 'text-emerald-700' : 'text-slate-400' }} block">
                            {{ $booking->waiver_accepted_at ? 'Digitally Signed' : 'Sign & Complete' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 3. FINTECH VERIFICATION DROPZONES (SECURE BANKING STYLE) -->
    <!-- ======================================================================= -->
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        <!-- DROPZONE CARD 1: IDENTITY & BIOMETRIC VERIFICATION -->
        <div class="ring-1 ring-blue-500/30 shadow-[0_0_30px_rgba(59,130,246,0.1)] rounded-3xl bg-white border border-slate-100 p-6 sm:p-7 flex flex-col justify-between hover:ring-blue-500/50 transition-all duration-300 relative overflow-hidden">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-100">
                            🪪
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 tracking-tight">Identity Verification</h2>
                            <p class="text-[11px] text-slate-400">Government Photo ID &amp; Biometric</p>
                        </div>
                    </div>
                    @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Vault Secured ✓</span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Building Administration ({{ $building->code }}) requires 1 valid government ID per occupant plus a live verification selfie from the lead guest.
                </p>

                <!-- Banking Trust Note -->
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 text-[11px] text-slate-600 flex items-start gap-2 mb-4">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>ID images are stored in a non-public encrypted bucket and automatically expunged after checkout.</span>
                </div>

                @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                    <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-900 space-y-1 mb-4">
                        <div>✓ Government IDs: <strong>{{ $governmentIds->count() }} of {{ $booking->guest_count }}</strong> uploaded</div>
                        <div>✓ Biometric Selfie: <strong>{{ $selfieDoc->original_filename }}</strong></div>
                    </div>
                @endif
            </div>

            <form action="{{ route('compliance.identity', ['bookingCode' => $booking->booking_code]) }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-3 border-t border-slate-100">
                @csrf
                <div class="space-y-3">
                    <p class="text-xs font-bold text-slate-800">Occupant Photo IDs ({{ $booking->guest_count }} Required)</p>
                    
                    @for($occupantIndex = 0; $occupantIndex < $booking->guest_count; $occupantIndex++)
                        @php
                            $occupant = $occupantIndex === 0
                                ? $booking->guest_name.' (Lead Guest)'
                                : ($booking->guest_roster[$occupantIndex - 1]['name'] ?? 'Companion '.($occupantIndex + 1));
                        @endphp
                        
                        <div x-data="{
                            isDragging: false,
                            previewUrl: null,
                            fileName: '',
                            handleFile(e) {
                                const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
                                if (file) {
                                    this.fileName = file.name;
                                    const reader = new FileReader();
                                    reader.onload = (event) => { this.previewUrl = event.target.result; };
                                    reader.readAsDataURL(file);
                                }
                            }
                        }">
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Occupant {{ $occupantIndex + 1 }}: {{ $occupant }}</label>
                            
                            <!-- Banking Verification Dropzone -->
                            <div class="border-2 border-dashed border-blue-200 hover:border-blue-500 bg-blue-50/20 hover:bg-blue-50/40 rounded-2xl p-4 text-center cursor-pointer transition-all relative group"
                                 :class="{ 'border-blue-600 bg-blue-50/60': isDragging }"
                                 @dragover.prevent="isDragging = true"
                                 @dragleave.prevent="isDragging = false"
                                 @drop.prevent="isDragging = false; handleFile($event)">
                                
                                <input type="file" name="gov_ids[{{ $occupantIndex }}]" accept="image/*" required
                                       @change="handleFile($event)"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                
                                <template x-if="!previewUrl">
                                    <div class="flex items-center justify-center gap-2 text-xs text-slate-600">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="font-medium text-slate-700 group-hover:text-blue-600">Drop Government ID or Browse</span>
                                    </div>
                                </template>

                                <template x-if="previewUrl">
                                    <div class="flex items-center gap-2.5 text-left">
                                        <img :src="previewUrl" alt="ID preview" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold">✓</span>
                                                <span class="text-xs font-bold text-slate-900 truncate" x-text="fileName"></span>
                                            </div>
                                            <span class="text-[10px] text-emerald-600 font-semibold">Document loaded</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- Live Selfie Dropzone -->
                <div x-data="{
                    isDragging: false,
                    previewUrl: null,
                    fileName: '',
                    handleFile(e) {
                        const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
                        if (file) {
                            this.fileName = file.name;
                            const reader = new FileReader();
                            reader.onload = (event) => { this.previewUrl = event.target.result; };
                            reader.readAsDataURL(file);
                        }
                    }
                }">
                    <label class="block text-xs font-bold text-slate-800 mb-1">Live Biometric Selfie (Lead Guest)</label>
                    <div class="border-2 border-dashed border-blue-200 hover:border-blue-500 bg-blue-50/20 hover:bg-blue-50/40 rounded-2xl p-4 text-center cursor-pointer transition-all relative group"
                         :class="{ 'border-blue-600 bg-blue-50/60': isDragging }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; handleFile($event)">
                        
                        <input type="file" name="selfie" accept="image/*" capture="user" required
                               @change="handleFile($event)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                        <template x-if="!previewUrl">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block group-hover:text-blue-600">Snap or Upload Real-time Selfie</span>
                                <span class="text-[11px] text-slate-400">Clear face lighting, holding ID recommended</span>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="flex items-center gap-3 text-left">
                                <img :src="previewUrl" alt="Selfie preview" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                                        <span class="text-xs font-bold text-slate-900 truncate" x-text="fileName"></span>
                                    </div>
                                    <span class="text-[10px] text-emerald-600 font-semibold block">Selfie captured</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-2xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-all shadow-sm cursor-pointer">
                    {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'Re-upload Identity Documents' : 'Submit Identity Documents' }}
                </button>
            </form>
        </div>

        <!-- DROPZONE CARD 2: ADVANCE DEPOSIT PAYMENT VERIFICATION -->
        <div class="ring-1 ring-blue-500/30 shadow-[0_0_30px_rgba(59,130,246,0.1)] rounded-3xl bg-white border border-slate-100 p-6 sm:p-7 flex flex-col justify-between hover:ring-blue-500/50 transition-all duration-300 relative overflow-hidden"
             x-data="{
                isDragging: false,
                previewUrl: null,
                fileName: '',
                handleFile(e) {
                    const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null);
                    if (file) {
                        this.fileName = file.name;
                        const reader = new FileReader();
                        reader.onload = (event) => { this.previewUrl = event.target.result; };
                        reader.readAsDataURL(file);
                    }
                }
             }">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-100">
                            🛡️
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 tracking-tight">Advance Deposit Escrow</h2>
                            <p class="text-[11px] text-slate-400">GCash or Online Banking Receipt</p>
                        </div>
                    </div>
                    @if($paymentDoc)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Receipt Received ✓</span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Transfer the mandatory <strong class="text-slate-800 tabular-nums">₱{{ number_format($booking->advance_deposit_amount, 2) }} Advance Deposit</strong> to hold the calendar lock.
                </p>

                <!-- Banking Transfer Details Card -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs mb-4 space-y-1.5">
                    <div class="font-bold text-slate-900 mb-1">Host GCash Transfer Account:</div>
                    <div class="text-slate-600 flex justify-between">
                        <span>Account Name:</span>
                        <strong class="text-slate-800">{{ $booking->unit->host->name ?? 'Aurelio J. Budol Sr. (A-F Staycation)' }}</strong>
                    </div>
                    <div class="text-slate-600 flex justify-between">
                        <span>GCash Mobile:</span>
                        <strong class="text-blue-600 font-mono">{{ $booking->unit->host->phone ?? '09478847463' }}</strong>
                    </div>
                    <div class="text-slate-600 flex justify-between border-t border-slate-200/80 pt-1.5 mt-1">
                        <span>Required Escrow:</span>
                        <strong class="text-slate-900 font-mono tabular-nums">₱{{ number_format($booking->advance_deposit_amount, 2) }}</strong>
                    </div>
                </div>

                @if($paymentDoc)
                    <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-900 flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="truncate">Receipt verified: <strong>{{ $paymentDoc->original_filename }}</strong></span>
                    </div>
                @endif
            </div>

            <form action="{{ route('compliance.payment', ['bookingCode' => $booking->booking_code]) }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-3 border-t border-slate-100">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">GCash / Bank Reference Number</label>
                    <input type="text" name="reference_number" placeholder="e.g. 1002 9384 1928"
                           class="w-full text-xs px-3.5 py-2.5 rounded-2xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono text-slate-900 bg-white">
                </div>

                <!-- Banking Proof Dropzone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Screenshot Proof</label>
                    <div class="border-2 border-dashed border-blue-200 hover:border-blue-500 bg-blue-50/20 hover:bg-blue-50/40 rounded-2xl p-5 text-center cursor-pointer transition-all relative group"
                         :class="{ 'border-blue-600 bg-blue-50/60': isDragging }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; handleFile($event)">
                        
                        <input type="file" name="payment_receipt" id="paymentReceiptInput" accept="image/*" required
                               @change="handleFile($event)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                        <template x-if="!previewUrl">
                            <div>
                                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-2 border border-blue-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-700 block group-hover:text-blue-600">Drop payment receipt here</span>
                                <span class="text-[11px] text-slate-400">or click to browse from device</span>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="flex items-center gap-3 text-left">
                                <img :src="previewUrl" alt="Receipt preview" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                                        <span class="text-xs font-bold text-slate-900 truncate" x-text="fileName"></span>
                                    </div>
                                    <span class="text-[11px] text-emerald-600 font-semibold block">Receipt verified</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-2xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-all shadow-sm cursor-pointer">
                    {{ $paymentDoc ? 'Re-upload Payment Proof' : 'Upload Payment Proof' }}
                </button>
            </form>
        </div>

    </div>

    <!-- ======================================================================= -->
    <!-- 4. DIGITAL RENTAL AGREEMENT EXECUTION & COMPANION ROSTER -->
    <!-- ======================================================================= -->
    <div class="max-w-4xl mx-auto">
        <div class="ring-1 ring-blue-500/30 shadow-[0_0_30px_rgba(59,130,246,0.1)] rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 relative">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center font-black text-xs">3</span>
                        Digital Staycation Lease Agreement &amp; Building Roster
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Binding short-term residential contract for Unit {{ $unit->unit_number }} between Tenant and Landlord
                    </p>
                </div>
                @if($booking->waiver_accepted_at)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Contract Executed ✓</span>
                @endif
            </div>

            <form action="{{ route('compliance.waiver', ['bookingCode' => $booking->booking_code]) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Contract Terms Summary Box -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-800 space-y-3">
                    <div class="flex items-center justify-between font-bold text-slate-900 border-b border-slate-200 pb-2">
                        <span class="uppercase tracking-wider text-[11px] text-blue-600 font-extrabold">Agreement Key Terms (Sections I &ndash; XII)</span>
                        <span class="text-slate-500 font-mono text-[11px] font-bold">{{ $unit->title }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[11px] text-slate-600">
                        <div><strong>I. The Parties:</strong> Tenant {{ $booking->guest_name }} &amp; Landlord Ferlyn Miranda / Aurelio J. Budol Sr.</div>
                        <div><strong>II. Premises:</strong> 2 Bedrooms, 1 Bathroom ({{ $unit->unit_number }})</div>
                        <div><strong>III. Lease Term:</strong> {{ $booking->check_in_date->format('M d, Y') }} (2 PM) &rarr; {{ $booking->check_out_date->format('M d, Y') }} (12 NN)</div>
                        <div><strong>IV. Occupants:</strong> Total {{ $booking->guest_count }} Authorized Guests/Visitors</div>
                        <div><strong>V. Rent:</strong> ₱1,000 Advance Deposit (Non-refundable upon cancellation; no rescheduling)</div>
                        <div><strong>VI. Security Deposit:</strong> ₱1,000 for faithful performance, returned less itemized deductions</div>
                        <div><strong>VII. Smoking Policy:</strong> Strictly Prohibited inside unit (Violation Fine: ₱2,000.00)</div>
                        <div><strong>VIII. Pets:</strong> Not allowed within premises (except tropical fishes)</div>
                        <div><strong>IX. Cleanliness (CLAYGO):</strong> Dispose of trash at back of bldg. Unthrown trash fine: ₱500.00</div>
                        <div><strong>X. Key / Elevator RFID Pass:</strong> ₱500.00 replacement fee each if lost</div>
                    </div>
                </div>

                <!-- Companion Roster Inputs -->
                @if($booking->guest_count > 1)
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">
                            Companion Guest Roster ({{ $booking->guest_count - 1 }} Additional Occupant{{ $booking->guest_count > 2 ? 's' : '' }}):
                        </label>
                        <p class="text-[11px] text-slate-400 mb-3">
                            Lead guest, <strong>{{ $booking->guest_name }}</strong>, is automatically registered. Please name all accompanying guests for official Pasig PMO gate clearance.
                        </p>

                        <div class="space-y-2">
                            @for($i = 1; $i < $booking->guest_count; $i++)
                                @php
                                    $existingCompanion = $booking->guest_roster[$i - 1] ?? null;
                                @endphp
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Companion {{ $i + 1 }} Legal Name</label>
                                        <input type="text" name="companion_names[]" value="{{ $existingCompanion['name'] ?? '' }}" placeholder="Full Legal Name"
                                               class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Relationship / Designation</label>
                                        <input type="text" name="companion_relationships[]" value="{{ $existingCompanion['relationship'] ?? 'Companion' }}" placeholder="e.g. Spouse, Friend, Child"
                                               class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endif

                <!-- Digital Rental Agreement Execution Checkbox -->
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 flex items-start gap-3">
                    <input type="checkbox" name="agree_rules" id="agreeRules" value="1" {{ $booking->waiver_accepted_at ? 'checked' : '' }} required
                           class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 mt-0.5 cursor-pointer">
                    <label for="agreeRules" class="text-xs text-slate-700 leading-relaxed cursor-pointer font-medium">
                        I, <strong>{{ $booking->guest_name }}</strong>, certify that all provided details and government documents are authentic. I hereby digitally execute and agree to the <strong>A-F Staycation Rental Agreement</strong> and all house rules of <strong>{{ $building->name }}</strong>, and acknowledge that infractions (such as ₱500 unthrown garbage fee or ₱500 lost key fee) will be deducted from the ₱1,000 security deposit upon checkout inspection.
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-2xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span>Execute Agreement &amp; Request Gate Pass Sign-off</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
