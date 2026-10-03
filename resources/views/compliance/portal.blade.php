@extends('layouts.app')

@section('title', 'Security Compliance & ID Portal - ' . $booking->booking_code)

@section('content')
<div class="page-enter max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
        calculateProgress() {
            let p = 25; // Dates chosen = 25%
            @if($paymentDoc) p += 25; @endif
            @if($governmentIds->count() === $booking->guest_count && $selfieDoc) p += 25; @endif
            @if($booking->waiver_accepted_at) p += 25; @endif
            return p;
        }
     }">

    <!-- Header & Status Tracker Card with Glassmorphic Ambient Glow -->
    <div class="glass-surface relative overflow-hidden rounded-3xl p-6 sm:p-8 mb-8 border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.06)]">
        <!-- Ambient decorative blobs -->
        <div class="ambient-glow w-64 h-64 bg-blue-500/15 -top-20 -right-20 glow-animate blob-animate"></div>
        <div class="ambient-glow w-48 h-48 bg-emerald-500/10 -bottom-16 -left-16 glow-animate"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-white/30 pb-6 mb-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-700 border border-blue-400/30 mb-2 backdrop-blur-md shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-blue-600 status-beacon"></span>
                    DirectStay Security Compliance Vault
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Guest Compliance &amp; Document Portal
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Booking Ref: <strong class="text-slate-800 font-mono font-bold">{{ $booking->booking_code }}</strong> &bull;
                    {{ $building->name }} ({{ $unit->unit_number }})
                </p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[11px] text-slate-400 font-semibold block mb-1">Clearance Status</span>
                @if($booking->status === 'confirmed' || $booking->status === 'checked_in')
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-800 border border-emerald-400/30 backdrop-blur-md shadow-xs">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Gate Pass Cleared
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-500/15 text-amber-900 border border-amber-400/30 backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Pending Host Verification
                    </span>
                @endif
            </div>
        </div>

        <!-- 4-Step Animated Progress Stepper -->
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Security Clearance Workflow</span>
                <span class="text-xs font-mono font-bold text-blue-600 tabular-nums" x-text="calculateProgress() + '% Completed'"></span>
            </div>

            <!-- Active Animated Progress Bar -->
            <div class="w-full bg-slate-200/50 rounded-full h-2.5 overflow-hidden mb-6 p-0.5 border border-white/50 backdrop-blur-sm">
                <div class="stepper-progress-bar h-full rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-500"
                     :style="'width: ' + calculateProgress() + '%'"></div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-semibold">
                <!-- Step 1: Dates -->
                <div class="p-3.5 rounded-2xl border bg-emerald-500/10 border-emerald-400/30 text-emerald-900 backdrop-blur-md transition-all">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[10px] bg-emerald-600 text-white shadow-xs">✓</span>
                        <span class="font-extrabold">1. Dates</span>
                    </div>
                    <p class="text-[10px] text-emerald-700">Reserved &amp; Locked</p>
                </div>

                <!-- Step 2: ID & Selfie -->
                <div class="p-3.5 rounded-2xl border backdrop-blur-md transition-all {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'bg-emerald-500/10 border-emerald-400/30 text-emerald-900' : 'bg-white/40 border-white/60 text-slate-700' }}">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[10px] shadow-xs {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white' }}">
                            {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? '✓' : '2' }}
                        </span>
                        <span class="font-extrabold">2. ID &amp; Selfie</span>
                    </div>
                    <p class="text-[10px] {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'Secured in Vault' : 'Photo ID + Selfie' }}
                    </p>
                </div>

                <!-- Step 3: House Rules -->
                <div class="p-3.5 rounded-2xl border backdrop-blur-md transition-all {{ $booking->waiver_accepted_at ? 'bg-emerald-500/10 border-emerald-400/30 text-emerald-900' : 'bg-white/40 border-white/60 text-slate-700' }}">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[10px] shadow-xs {{ $booking->waiver_accepted_at ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }}">
                            {{ $booking->waiver_accepted_at ? '✓' : '3' }}
                        </span>
                        <span class="font-extrabold">3. House Rules</span>
                    </div>
                    <p class="text-[10px] {{ $booking->waiver_accepted_at ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $booking->waiver_accepted_at ? 'Agreement Signed' : 'Sign Lease & Roster' }}
                    </p>
                </div>

                <!-- Step 4: Advance Deposit -->
                <div class="p-3.5 rounded-2xl border backdrop-blur-md transition-all {{ $paymentDoc ? 'bg-emerald-500/10 border-emerald-400/30 text-emerald-900' : 'bg-white/40 border-white/60 text-slate-700' }}">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[10px] shadow-xs {{ $paymentDoc ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }}">
                            {{ $paymentDoc ? '✓' : '4' }}
                        </span>
                        <span class="font-extrabold">4. Deposit</span>
                    </div>
                    <p class="text-[10px] {{ $paymentDoc ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $paymentDoc ? 'Receipt Received' : '₱1,000 Advance Deposit' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms Grid: Payment Proof & Identity Verification -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">

        <!-- Form 1: Advance Deposit Payment Upload Dropzone -->
        <div class="glass-surface relative overflow-hidden rounded-3xl p-6 border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.06)] flex flex-col justify-between"
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
            <div class="ambient-glow w-48 h-48 bg-blue-500/10 -top-16 -right-16 glow-animate"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-blue-500/15 text-blue-700 border border-blue-400/30 flex items-center justify-center font-black text-xs">1</span>
                        Advance Deposit Payment
                    </h2>
                    @if($paymentDoc)
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/15 text-emerald-800 border border-emerald-400/30">Uploaded ✓</span>
                    @endif
                </div>

                <p class="text-xs text-slate-600 mb-4 leading-relaxed font-medium">
                    Please transfer the mandatory <strong class="text-slate-800 tabular-nums">₱{{ number_format($booking->advance_deposit_amount, 2) }} Advance Deposit</strong> via GCash or Online Bank Transfer and drop your receipt below:
                </p>

                <!-- Host GCash Account Info Glass Card -->
                <div class="p-4 bg-white/50 backdrop-blur-md rounded-2xl border border-white/60 text-xs mb-4 space-y-1 shadow-xs">
                    <div class="font-bold text-slate-900 mb-1">Host GCash Transfer Details:</div>
                    <div class="text-slate-600 flex justify-between">
                        <span>Account Name:</span>
                        <strong class="text-slate-800">{{ $booking->unit->host->name ?? 'Aurelio J. Budol Sr. (A-F Staycation)' }}</strong>
                    </div>
                    <div class="text-slate-600 flex justify-between">
                        <span>GCash Mobile:</span>
                        <strong class="text-blue-600 font-mono">{{ $booking->unit->host->phone ?? '09478847463' }}</strong>
                    </div>
                    <div class="text-slate-600 flex justify-between border-t border-slate-200/50 pt-1 mt-1">
                        <span>Required Amount:</span>
                        <strong class="text-slate-900 font-mono tabular-nums">₱{{ number_format($booking->advance_deposit_amount, 2) }}</strong>
                    </div>
                </div>

                @if($paymentDoc)
                    <div class="p-3 bg-emerald-500/15 backdrop-blur-md rounded-xl border border-emerald-400/30 text-xs text-emerald-900 flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span class="truncate">Receipt verified: <strong>{{ $paymentDoc->original_filename }}</strong></span>
                    </div>
                @endif
            </div>

            <form action="{{ route('compliance.payment', ['bookingCode' => $booking->booking_code]) }}" method="POST" enctype="multipart/form-data" class="relative z-10 space-y-4 pt-3 border-t border-white/30">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">GCash Reference No. (Optional)</label>
                    <input type="text" name="reference_number" placeholder="e.g. 1002 9384 1928"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-white/60 bg-white/60 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono text-slate-800 placeholder-slate-400 shadow-xs">
                </div>

                <!-- Drag-and-drop Dropzone Component -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Proof of Payment Screenshot (JPEG, PNG)</label>
                    <div class="dropzone-container rounded-2xl p-5 text-center cursor-pointer relative bg-white/40 backdrop-blur-md border border-white/60 shadow-xs"
                         :class="{ 'is-dragover': isDragging }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; handleFile($event)">
                        
                        <input type="file" name="payment_receipt" id="paymentReceiptInput" accept="image/*" required
                                @change="handleFile($event)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                        <template x-if="!previewUrl">
                            <div>
                                <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center mx-auto mb-2 border border-blue-400/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <span class="text-xs font-bold text-slate-700 block">Drag &amp; drop payment screenshot here</span>
                                <span class="text-[11px] text-slate-400">or click to browse from device</span>
                            </div>
                        </template>

                        <!-- Real-time Preview Container with Checkmark Burst -->
                        <template x-if="previewUrl">
                            <div class="flex items-center gap-3 text-left">
                                <img :src="previewUrl" alt="Receipt preview" class="w-16 h-16 rounded-xl object-cover border border-white/60 shadow-sm shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold checkmark-burst">✓</span>
                                        <span class="text-xs font-bold text-slate-900 truncate" x-text="fileName"></span>
                                    </div>
                                    <span class="text-[11px] text-emerald-600 font-semibold block">Ready to upload</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-all btn-press shadow-md shadow-slate-900/10 cursor-pointer">
                    {{ $paymentDoc ? 'Re-upload Payment Screenshot' : 'Upload Payment Receipt' }}
                </button>
            </form>
        </div>

        <!-- Form 2: Identity & Selfie Verification Dropzone -->
        <div class="glass-surface relative overflow-hidden rounded-3xl p-6 border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.06)] flex flex-col justify-between">
            <div class="ambient-glow w-48 h-48 bg-indigo-500/10 -top-16 -left-16 glow-animate"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-blue-500/15 text-blue-700 border border-blue-400/30 flex items-center justify-center font-black text-xs">2</span>
                        Identity Verification
                    </h2>
                    @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/15 text-emerald-800 border border-emerald-400/30">Secured ✓</span>
                    @endif
                </div>

                <p class="text-xs text-slate-600 mb-4 leading-relaxed font-medium">
                    Building Administration ({{ $building->code }}) requires one valid government photo ID for every registered occupant. The lead guest must also provide a live verification selfie.
                </p>

                <div class="p-3 bg-blue-500/10 backdrop-blur-md rounded-2xl border border-blue-400/30 text-[11px] text-blue-900 space-y-1 mb-4 shadow-xs">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Private Encrypted Vault Guarantee
                    </div>
                    <div>Identity files are encrypted in Laravel's non-public vault and automatically wiped following building security move-out checkout.</div>
                </div>

                @if($governmentIds->count() === $booking->guest_count && $selfieDoc)
                    <div class="p-3 bg-emerald-500/15 backdrop-blur-md rounded-xl border border-emerald-400/30 text-xs text-emerald-900 space-y-1 mb-4">
                        <div>✓ Government IDs: <strong>{{ $governmentIds->count() }} of {{ $booking->guest_count }}</strong> uploaded</div>
                        <div>✓ Live Selfie: <strong>{{ $selfieDoc->original_filename }}</strong></div>
                    </div>
                @endif
            </div>

            <form action="{{ route('compliance.identity', ['bookingCode' => $booking->booking_code]) }}" method="POST" enctype="multipart/form-data" class="relative z-10 space-y-4 pt-3 border-t border-white/30">
                @csrf
                <div class="space-y-3">
                    <p class="text-xs font-extrabold text-slate-800">Government IDs — {{ $booking->guest_count }} required</p>
                    
                    @for($occupantIndex = 0; $occupantIndex < $booking->guest_count; $occupantIndex++)
                        @php
                            $occupant = $occupantIndex === 0
                                ? $booking->guest_name.' (Lead Guest)'
                                : ($booking->guest_roster[$occupantIndex - 1]['name'] ?? 'Guest '.($occupantIndex + 1));
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
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Guest {{ $occupantIndex + 1 }}: {{ $occupant }}</label>
                            
                            <div class="dropzone-container rounded-xl p-3 text-center cursor-pointer relative bg-white/40 backdrop-blur-md border border-white/60 shadow-xs"
                                 :class="{ 'is-dragover': isDragging }"
                                 @dragover.prevent="isDragging = true"
                                 @dragleave.prevent="isDragging = false"
                                 @drop.prevent="isDragging = false; handleFile($event)">
                                
                                <input type="file" name="gov_ids[{{ $occupantIndex }}]" accept="image/*" required
                                       @change="handleFile($event)"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                
                                <template x-if="!previewUrl">
                                    <div class="flex items-center justify-center gap-2 text-xs text-slate-600">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Click or drag Government ID</span>
                                    </div>
                                </template>

                                <template x-if="previewUrl">
                                    <div class="flex items-center gap-2 text-left">
                                        <img :src="previewUrl" alt="ID preview" class="w-9 h-9 rounded-lg object-cover border border-white/60 shadow-xs shrink-0">
                                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold checkmark-burst">✓</span>
                                        <span class="text-xs font-semibold text-slate-900 truncate" x-text="fileName"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- Lead Guest Selfie Dropzone -->
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Real-time Verification Selfie (Lead Guest)</label>
                    <div class="dropzone-container rounded-2xl p-4 text-center cursor-pointer relative bg-white/40 backdrop-blur-md border border-white/60 shadow-xs"
                         :class="{ 'is-dragover': isDragging }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; handleFile($event)">
                        
                        <input type="file" name="selfie" accept="image/*" capture="user" required
                               @change="handleFile($event)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                        <template x-if="!previewUrl">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block">Take or drop live selfie</span>
                                <span class="text-[11px] text-slate-400">Neutral background, holding ID next to face recommended</span>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="flex items-center gap-3 text-left">
                                <img :src="previewUrl" alt="Selfie preview" class="w-12 h-12 rounded-xl object-cover border border-white/60 shadow-sm shrink-0">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold checkmark-burst">✓</span>
                                        <span class="text-xs font-bold text-slate-900 truncate" x-text="fileName"></span>
                                    </div>
                                    <span class="text-[10px] text-emerald-600 font-semibold block">Selfie validated</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-all btn-press shadow-md shadow-slate-900/10 cursor-pointer">
                    {{ ($governmentIds->count() === $booking->guest_count && $selfieDoc) ? 'Replace All IDs & Selfie' : 'Upload All IDs & Selfie' }}
                </button>
            </form>
        </div>

    </div>

    <!-- Form 3: Staycation Rental Agreement & Companion Roster -->
    <div class="glass-surface relative overflow-hidden rounded-3xl p-6 sm:p-8 border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.06)]">
        <div class="ambient-glow w-64 h-64 bg-emerald-500/10 -top-20 -right-20 glow-animate"></div>

        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-blue-500/15 text-blue-700 border border-blue-400/30 flex items-center justify-center font-black text-xs">3</span>
                        A-F Staycation Rental Agreement &amp; Guest Roster
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        Official lease contract for {{ $unit->unit_number }} between Tenant and Landlord (Ferlyn Miranda &amp; Aurelio J. Budol Sr.)
                    </p>
                </div>
                @if($booking->waiver_accepted_at)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-800 border border-emerald-400/30">Contract Signed ✓</span>
                @endif
            </div>

            <form action="{{ route('compliance.waiver', ['bookingCode' => $booking->booking_code]) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Rental Agreement Summary Box -->
                <div class="p-5 rounded-2xl bg-white/50 backdrop-blur-md border border-white/60 text-xs text-slate-800 space-y-3 shadow-xs">
                    <div class="flex items-center justify-between font-bold text-slate-900 border-b border-slate-200/50 pb-2">
                        <span class="uppercase tracking-wider text-[11px] text-blue-600 font-extrabold">Contract Terms Summary (Sections I - XII)</span>
                        <span class="text-slate-500 font-mono text-[11px] font-bold">{{ $unit->title }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[11px] text-slate-700">
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
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">
                        Accompanying Guests Roster (Max {{ $unit->max_guests }} Total Occupants):
                    </label>
                    <p class="text-[11px] text-slate-400 mb-3">
                        The lead guest, <strong>{{ $booking->guest_name }}</strong>, is automatically included. Add every other companion who will stay. This list is certified to the Deca gate pass.
                    </p>

                    <div class="space-y-2">
                        @for($i = 1; $i < $booking->guest_count; $i++)
                            @php
                                $existingCompanion = $booking->guest_roster[$i - 1] ?? null;
                            @endphp
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-white/40 backdrop-blur-md rounded-2xl border border-white/60 shadow-xs">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Companion {{ $i + 1 }} Legal Name</label>
                                    <input type="text" name="companion_names[]" value="{{ $existingCompanion['name'] ?? '' }}" placeholder="Full Name"
                                           class="w-full text-xs px-3.5 py-2 rounded-xl border border-white/60 bg-white/60 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">Relationship / Designation</label>
                                    <input type="text" name="companion_relationships[]" value="{{ $existingCompanion['relationship'] ?? 'Companion' }}" placeholder="e.g. Spouse, Friend, Child"
                                           class="w-full text-xs px-3.5 py-2 rounded-xl border border-white/60 bg-white/60 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Digital Rental Agreement Checkbox -->
                <div class="p-4 rounded-2xl bg-blue-500/10 backdrop-blur-md border border-blue-400/30 flex items-start gap-3">
                    <input type="checkbox" name="agree_rules" id="agreeRules" value="1" {{ $booking->waiver_accepted_at ? 'checked' : '' }} required
                           class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 mt-0.5 cursor-pointer">
                    <label for="agreeRules" class="text-xs text-slate-700 leading-relaxed cursor-pointer font-medium">
                        I, <strong>{{ $booking->guest_name }}</strong>, certify that all provided details and government documents are authentic. I hereby digitally execute and agree to the <strong>A-F Staycation Rental Agreement</strong> and all house rules of <strong>{{ $building->name }}</strong>, and acknowledge that infractions (such as ₱500 unthrown garbage fee or ₱500 lost key fee) will be deducted from the ₱1,000 security deposit upon checkout inspection.
                    </label>
                </div>

                <!-- Submit Final Step -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 transition-all btn-press flex items-center justify-center gap-2 cursor-pointer">
                    <span>Sign Rental Agreement &amp; Complete Submission</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
