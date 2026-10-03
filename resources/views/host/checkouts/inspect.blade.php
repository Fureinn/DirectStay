@extends('layouts.app')

@section('title', 'Checkout Inspection & Penalty Engine: ' . $booking->booking_code . ' - DirectStay')

@section('content')
<div class="page-enter max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('host.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                    &larr; Host Dashboard
                </a>
                <span class="text-slate-300">|</span>
                <span class="text-xs font-mono font-bold text-blue-600">{{ $booking->booking_code }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Post-Checkout Inspection &amp; Penalty Deductions
            </h1>
            <p class="text-xs text-slate-500">
                {{ $building->name }} &bull; {{ $unit->unit_number }} &bull; Guest: <strong>{{ $booking->guest_name }}</strong>
            </p>
        </div>

        @if($checkout)
            <span class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                Inspection Settled ✓
            </span>
        @endif
    </div>

    <!-- Settlement Overview if already inspected -->
    @if($checkout)
        <div class="glass-surface rounded-3xl p-6 mb-8 relative overflow-hidden">
            <div class="ambient-glow w-40 h-40 bg-emerald-400/10 -top-12 -right-12 glow-animate"></div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 relative">Inspection Settlement Summary</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs relative stagger-enter">
                <div class="p-4 bg-white/35 rounded-2xl border border-white/40 backdrop-blur-sm">
                    <span class="text-slate-400 block mb-0.5">Initial Advance Deposit</span>
                    <span class="text-lg font-bold text-slate-900">₱{{ number_format($booking->advance_deposit_amount, 2) }}</span>
                </div>
                <div class="p-4 bg-white/35 rounded-2xl border border-white/40 backdrop-blur-sm">
                    <span class="text-slate-400 block mb-0.5">Cleaning Dispatched</span>
                    <span class="text-lg font-bold text-slate-900">
                        {{ ucfirst($checkout->cleaning_type) }} (₱{{ number_format($checkout->cleaning_fee, 2) }})
                    </span>
                </div>
                <div class="p-4 bg-rose-50/40 rounded-2xl border border-rose-200/40 backdrop-blur-sm">
                    <span class="text-rose-600 block mb-0.5 font-semibold">Total Penalty Deductions</span>
                    <span class="text-lg font-bold text-rose-700">-₱{{ number_format($checkout->total_penalties, 2) }}</span>
                </div>
                <div class="p-4 bg-emerald-50/40 rounded-2xl border border-emerald-200/40 backdrop-blur-sm">
                    <span class="text-emerald-700 block mb-0.5 font-semibold">Net Deposit Refunded</span>
                    <span class="text-lg font-bold text-emerald-800">₱{{ number_format($checkout->deposit_refunded, 2) }}</span>
                </div>
            </div>

            @if(!empty($checkout->penalties_breakdown))
                <div class="mt-4 pt-4 border-t border-white/30 text-xs relative">
                    <div class="font-bold text-slate-800 mb-2">Itemized Penalties Logged:</div>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        @foreach($checkout->penalties_breakdown as $penalty)
                            <li>{{ $penalty['reason'] }}: <strong>₱{{ number_format($penalty['amount'], 2) }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- Digital Inspection & Penalty Calculation Form -->
    <form action="{{ route('host.checkouts.store', $booking) }}" method="POST" id="checkoutForm" class="space-y-8">
        @csrf

        <!-- Section 1: Digital Inventory Checklist -->
        <div class="glass-surface rounded-3xl p-8 relative overflow-hidden">
            <div class="ambient-glow w-36 h-36 bg-blue-400/10 -top-10 -left-10 glow-animate"></div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 relative">
                        <span class="w-7 h-7 rounded-lg bg-blue-100/80 text-blue-700 flex items-center justify-center font-bold text-xs shadow-sm shadow-blue-200/40">1</span>
                        High-Value Asset Inventory Verification
                    </h2>
                    <p class="text-xs text-slate-500">Digital checklist to verify property items upon guest checkout</p>
                </div>
                <button type="button" onclick="checkAllInventory()" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                    Mark All Good
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                @foreach($inventory as $item)
                    @php
                        $slug = Str::slug($item, '_');
                        $currentStatus = $checkout->inventory_checklist[$slug] ?? 'intact';
                    @endphp
                    <div class="p-3.5 bg-white/30 rounded-2xl border border-white/40 backdrop-blur-sm flex items-center justify-between hover:bg-white/45 transition-all">
                        <div class="flex items-center gap-2 font-semibold text-slate-800">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>{{ $item }}</span>
                        </div>
                        <select name="inventory_status[{{ $slug }}]" class="inventory-select text-xs font-semibold py-1 px-2.5 rounded-lg border border-slate-300 bg-white">
                            <option value="intact" {{ $currentStatus === 'intact' ? 'selected' : '' }}>✓ Intact & Clean</option>
                            <option value="damaged" {{ $currentStatus === 'damaged' ? 'selected' : '' }}>⚠ Damaged</option>
                            <option value="missing" {{ $currentStatus === 'missing' ? 'selected' : '' }}>✕ Missing</option>
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section 2: Housekeeping Dispatch -->
        <div class="glass-surface rounded-3xl p-8 relative overflow-hidden">
            <div class="ambient-glow w-32 h-32 bg-indigo-400/8 -bottom-8 -right-8 glow-animate"></div>
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 mb-1 relative">
                <span class="w-7 h-7 rounded-lg bg-blue-100/80 text-blue-700 flex items-center justify-center font-bold text-xs shadow-sm shadow-blue-200/40">2</span>
                Housekeeping Dispatch &amp; Cleaning Status
            </h2>
            <p class="text-xs text-slate-500 mb-6">Log on-call cleaning staff dispatch for turnover</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <!-- Option 1: None -->
                <label class="p-4 rounded-2xl border cursor-pointer transition-all border-white/40 bg-white/25 backdrop-blur-sm hover:border-blue-300/60 has-[:checked]:border-blue-600/60 has-[:checked]:bg-blue-50/40">
                    <input type="radio" name="cleaning_type" value="none" {{ ($checkout?->cleaning_type ?? 'none') === 'none' ? 'checked' : '' }}
                           onchange="recalculateSettlement()" class="cleaning-radio sr-only">
                    <div class="font-bold text-slate-900 mb-1">Self-Turnover / None</div>
                    <div class="text-slate-400 text-[11px] mb-2">Host conducts routine wipe down</div>
                    <div class="text-base font-extrabold text-slate-900">₱0.00</div>
                </label>

                <!-- Option 2: Basic Cleaning -->
                <label class="p-4 rounded-2xl border cursor-pointer transition-all border-white/40 bg-white/25 backdrop-blur-sm hover:border-blue-300/60 has-[:checked]:border-blue-600/60 has-[:checked]:bg-blue-50/40">
                    <input type="radio" name="cleaning_type" value="basic" data-fee="500" {{ $checkout?->cleaning_type === 'basic' ? 'checked' : '' }}
                           onchange="recalculateSettlement()" class="cleaning-radio sr-only">
                    <div class="font-bold text-slate-900 mb-1">On-Call Basic Cleaning</div>
                    <div class="text-slate-400 text-[11px] mb-2">Standard linen swap & floor sweep</div>
                    <div class="text-base font-extrabold text-blue-600">₱500.00</div>
                </label>

                <!-- Option 3: Deep Cleaning -->
                <label class="p-4 rounded-2xl border cursor-pointer transition-all border-white/40 bg-white/25 backdrop-blur-sm hover:border-blue-300/60 has-[:checked]:border-blue-600/60 has-[:checked]:bg-blue-50/40">
                    <input type="radio" name="cleaning_type" value="deep" data-fee="1300" {{ $checkout?->cleaning_type === 'deep' ? 'checked' : '' }}
                           onchange="recalculateSettlement()" class="cleaning-radio sr-only">
                    <div class="font-bold text-slate-900 mb-1">Deep Cleaning & Sanitization</div>
                    <div class="text-slate-400 text-[11px] mb-2">Heavy stain removal & kitchen degreasing</div>
                    <div class="text-base font-extrabold text-blue-600">₱1,300.00</div>
                </label>
            </div>
        </div>

        <!-- Section 3: Automated Penalty Engine -->
        <div class="glass-surface rounded-3xl p-8 relative overflow-hidden">
            <div class="ambient-glow w-44 h-44 bg-rose-400/8 -top-14 -right-14 glow-animate"></div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-2">
                <div>
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2 relative">
                        <span class="w-7 h-7 rounded-xl bg-blue-100/80 text-blue-700 flex items-center justify-center font-black text-xs shadow-sm shadow-blue-200/40">3</span>
                        Automated Security Deposit Penalty Engine
                    </h2>
                    <p class="text-xs text-slate-500">
                        Log building rule infractions. Deductions update the refund ledger in real time.
                    </p>
                </div>
                <!-- Quick Toggle Chips Bar -->
                <div class="flex flex-wrap items-center gap-1.5 relative">
                    <button type="button" onclick="togglePenaltyChip('penGarbage')"
                            class="px-3 py-1 rounded-full text-xs font-bold bg-white/40 hover:bg-rose-50/60 hover:text-rose-700 hover:border-rose-200/60 border border-white/40 text-slate-700 backdrop-blur-sm transition-all btn-press">
                        + ₱500 Garbage
                    </button>
                    <button type="button" onclick="togglePenaltyChip('penLostKey')"
                            class="px-3 py-1 rounded-full text-xs font-bold bg-white/40 hover:bg-rose-50/60 hover:text-rose-700 hover:border-rose-200/60 border border-white/40 text-slate-700 backdrop-blur-sm transition-all btn-press">
                        + ₱500 RFID Pass
                    </button>
                    <button type="button" onclick="togglePenaltyChip('penSmoking')"
                            class="px-3 py-1 rounded-full text-xs font-bold bg-white/40 hover:bg-rose-50/60 hover:text-rose-700 hover:border-rose-200/60 border border-white/40 text-slate-700 backdrop-blur-sm transition-all btn-press">
                        + ₱2,000 Smoking
                    </button>
                </div>
            </div>

            <div class="space-y-3 text-xs mb-6 mt-4">
                <!-- Penalty 1: Garbage -->
                <label id="card-penGarbage" class="flex items-start justify-between p-4 bg-white/25 rounded-2xl border border-white/35 backdrop-blur-sm cursor-pointer hover:bg-white/40 transition-all">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="penalty_garbage" id="penGarbage" value="1" data-amount="500" data-label="Garbage Infraction"
                               onchange="recalculateSettlement()"
                               class="penalty-check w-4 h-4 text-rose-600 rounded border-slate-300 focus:ring-rose-500 mt-0.5 cursor-pointer">
                        <div>
                            <span class="font-bold text-slate-900">Unthrown Garbage Infraction (CLAYGO)</span>
                            <span class="text-slate-500 block text-[11px]">Left trash in unit instead of taking to designated garbage chute</span>
                        </div>
                    </div>
                    <span class="font-bold text-rose-600 text-sm tabular-nums font-mono">₱500.00 deduction</span>
                </label>

                <!-- Penalty 2: Lost Key / Pass -->
                <label id="card-penLostKey" class="flex items-start justify-between p-4 bg-white/25 rounded-2xl border border-white/35 backdrop-blur-sm cursor-pointer hover:bg-white/40 transition-all">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="penalty_lost_key" id="penLostKey" value="1" data-amount="500" data-label="Lost Key / RFID"
                               onchange="recalculateSettlement()"
                               class="penalty-check w-4 h-4 text-rose-600 rounded border-slate-300 focus:ring-rose-500 mt-0.5 cursor-pointer">
                        <div>
                            <span class="font-bold text-slate-900">Lost Key or RFID Elevator Pass</span>
                            <span class="text-slate-500 block text-[11px]">Building replacement &amp; locksmith programming fee</span>
                        </div>
                    </div>
                    <span class="font-bold text-rose-600 text-sm tabular-nums font-mono">₱500.00 deduction</span>
                </label>

                <!-- Penalty 3: Smoking -->
                <label id="card-penSmoking" class="flex items-start justify-between p-4 bg-white/25 rounded-2xl border border-white/35 backdrop-blur-sm cursor-pointer hover:bg-white/40 transition-all">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="penalty_smoking" id="penSmoking" value="1" data-amount="2000" data-label="Strict Smoking Violation"
                               onchange="recalculateSettlement()"
                               class="penalty-check w-4 h-4 text-rose-600 rounded border-slate-300 focus:ring-rose-500 mt-0.5 cursor-pointer">
                        <div>
                            <span class="font-bold text-slate-900">Strict In-Unit Smoking Violation</span>
                            <span class="text-slate-500 block text-[11px]">Full building penalty + ozone odor treatment</span>
                        </div>
                    </div>
                    <span class="font-bold text-rose-600 text-sm tabular-nums font-mono">₱2,000.00 deduction</span>
                </label>
            </div>

            <!-- Custom Penalty -->
            <div class="p-4 bg-white/25 rounded-2xl border border-white/35 backdrop-blur-sm grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs relative">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Other Violation / Damage Reason</label>
                    <input type="text" name="custom_penalty_reason" placeholder="e.g. Broken water kettle glass, stained bedsheet"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Custom Deduction Amount (₱)</label>
                    <input type="number" name="custom_penalty_amount" id="customPenAmount" min="0" step="50" placeholder="0.00"
                           oninput="recalculateSettlement()"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 tabular-nums font-mono">
                </div>
            </div>

            <!-- Inspection Notes -->
            <div class="mt-4">
                <label class="block text-xs font-bold text-slate-700 mb-1">Inspector Notes</label>
                <textarea name="notes" rows="2" placeholder="Provide any additional checkout remarks..."
                          class="w-full text-xs p-3.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600"></textarea>
            </div>
        </div>

        <!-- Section 4: Live Deposit Settlement Preview with Animated Deduction Pills -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-850 to-blue-950 text-white rounded-3xl p-8 shadow-xl border border-white/10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-blue-300">Security Deposit Settlement Ledger</h3>
                <span class="text-xs text-slate-400 font-mono">Urban Deca Homes Escrow</span>
            </div>

            <!-- Animated Deduction Pills Container -->
            <div id="deductionPillsContainer" class="flex flex-wrap gap-2 mb-6">
                <!-- Dynamically populated by JS when chips checked -->
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                <div>
                    <span class="text-xs text-slate-400 block mb-1">Advance Deposit Held</span>
                    <span class="text-2xl font-black text-white tabular-nums font-mono">₱{{ number_format($booking->advance_deposit_amount, 2) }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block mb-1">Total Infraction Penalties</span>
                    <span class="text-2xl font-black text-rose-400 tabular-nums font-mono transition-all" id="previewTotalPenalties">-₱0.00</span>
                </div>
                <div class="border-t sm:border-t-0 sm:border-l border-white/10 sm:pl-6">
                    <span class="text-xs text-slate-400 block mb-1">Net Deposit Refund to Guest</span>
                    <span class="text-3xl font-black text-emerald-400 tabular-nums font-mono tracking-tight transition-all" id="previewNetRefund">₱{{ number_format($booking->advance_deposit_amount, 2) }}</span>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-4 px-6 rounded-2xl text-xs font-black text-slate-950 bg-white hover:bg-slate-100 shadow-md transition-all btn-press flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Commit Checkout Inspection &amp; Settle Ledger</span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
    const depositAmount = {{ (float) $booking->advance_deposit_amount }};

    function checkAllInventory() {
        document.querySelectorAll('.inventory-select').forEach(sel => sel.value = 'intact');
    }

    function togglePenaltyChip(checkboxId) {
        const chk = document.getElementById(checkboxId);
        if (chk) {
            chk.checked = !chk.checked;
            const card = document.getElementById('card-' + checkboxId);
            if (card) {
                if (chk.checked) {
                    card.classList.add('border-rose-300', 'bg-rose-50/40', 'ring-2', 'ring-rose-400/20');
                } else {
                    card.classList.remove('border-rose-300', 'bg-rose-50/40', 'ring-2', 'ring-rose-400/20');
                }
            }
            recalculateSettlement();
        }
    }

    function recalculateSettlement() {
        let totalPenalties = 0.00;
        let activePillsHtml = '';

        document.querySelectorAll('.penalty-check:checked').forEach(chk => {
            const amt = parseFloat(chk.dataset.amount) || 0;
            const label = chk.dataset.label || 'Penalty';
            totalPenalties += amt;

            activePillsHtml += `
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 page-enter shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                    <span>${label}:</span>
                    <strong class="font-mono tabular-nums font-black">-₱${amt.toFixed(2)}</strong>
                </span>
            `;
        });

        const customAmt = parseFloat(document.getElementById('customPenAmount').value) || 0;
        if (customAmt > 0) {
            totalPenalties += customAmt;
            activePillsHtml += `
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 page-enter shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                    <span>Custom Deduction:</span>
                    <strong class="font-mono tabular-nums font-black">-₱${customAmt.toFixed(2)}</strong>
                </span>
            `;
        }

        const pillsContainer = document.getElementById('deductionPillsContainer');
        if (pillsContainer) {
            if (totalPenalties > 0) {
                pillsContainer.innerHTML = activePillsHtml;
                pillsContainer.classList.remove('hidden');
            } else {
                pillsContainer.innerHTML = `<span class="text-xs text-slate-400 italic">No infractions logged. Full deposit eligible for refund.</span>`;
            }
        }

        const netRefund = Math.max(0.00, depositAmount - totalPenalties);

        const totalPenEl = document.getElementById('previewTotalPenalties');
        const netRefundEl = document.getElementById('previewNetRefund');

        if (totalPenEl) {
            totalPenEl.classList.add('scale-105');
            totalPenEl.innerText = '-₱' + totalPenalties.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            setTimeout(() => totalPenEl.classList.remove('scale-105'), 200);
        }

        if (netRefundEl) {
            netRefundEl.classList.add('scale-105');
            netRefundEl.innerText = '₱' + netRefund.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            setTimeout(() => netRefundEl.classList.remove('scale-105'), 200);
        }
    }

    recalculateSettlement();
</script>
@endpush
@endsection
