<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Checkout;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display digital checkout inspection checklist and penalty deduction engine.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['unit.building', 'checkout', 'transactions']);

        $defaultInventory = $booking->unit->inventory_items ?? [
            'Inverter Air Conditioner',
            '40-inch Android TV',
            'Personal Refrigerator',
            'Induction Cooker',
            'Electric Water Kettle',
            'Physical Key & Elevator Card',
        ];

        return view('host.checkouts.inspect', [
            'booking' => $booking,
            'unit' => $booking->unit,
            'building' => $booking->unit->building,
            'inventory' => $defaultInventory,
            'checkout' => $booking->checkout,
        ]);
    }

    /**
     * Process inspection results, calculate cleaning and penalties, and settle deposit refund.
     */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'inventory_status' => ['required', 'array'],
            'cleaning_type' => ['required', 'in:none,basic,deep'],
            'penalty_garbage' => ['nullable', 'boolean'],
            'penalty_lost_key' => ['nullable', 'boolean'],
            'penalty_smoking' => ['nullable', 'boolean'],
            'custom_penalty_reason' => ['nullable', 'string', 'max:255'],
            'custom_penalty_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Housekeeping fees
        $cleaningFeesMap = [
            'none' => 0.00,
            'basic' => 500.00,
            'deep' => 1300.00,
        ];
        $cleaningFee = $cleaningFeesMap[$validated['cleaning_type']] ?? 0.00;

        // Penalty calculations against the ₱1,000 security deposit
        $penalties = [];
        $totalPenalties = 0.00;

        if ($request->boolean('penalty_garbage')) {
            $penalties[] = [
                'reason' => 'Unthrown Garbage Violation (Building Rule Fine)',
                'amount' => 500.00,
            ];
            $totalPenalties += 500.00;
        }

        if ($request->boolean('penalty_lost_key')) {
            $penalties[] = [
                'reason' => 'Lost Physical Key / RFID Elevator Pass Replacement Fee',
                'amount' => 500.00,
            ];
            $totalPenalties += 500.00;
        }

        if ($request->boolean('penalty_smoking')) {
            $penalties[] = [
                'reason' => 'Strict In-Unit Smoking Violation Penalty',
                'amount' => 2000.00,
            ];
            $totalPenalties += 2000.00;
        }

        if (! empty($validated['custom_penalty_amount']) && (float) $validated['custom_penalty_amount'] > 0) {
            $customAmt = (float) $validated['custom_penalty_amount'];
            $penalties[] = [
                'reason' => $validated['custom_penalty_reason'] ?: 'Additional Property Damage',
                'amount' => $customAmt,
            ];
            $totalPenalties += $customAmt;
        }

        $depositAmount = (float) $booking->advance_deposit_amount; // ₱1,000.00
        $totalDeductions = $totalPenalties; // Penalties deducted directly from deposit

        $depositRefunded = max(0.00, $depositAmount - $totalDeductions);

        // Record or update checkout
        $checkout = Checkout::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'inspected_by' => auth()->id(),
                'inventory_checklist' => $validated['inventory_status'],
                'cleaning_type' => $validated['cleaning_type'],
                'cleaning_fee' => $cleaningFee,
                'penalties_breakdown' => $penalties,
                'total_penalties' => $totalPenalties,
                'deposit_refunded' => $depositRefunded,
                'notes' => $validated['notes'],
            ]
        );

        // Record transactions for financial audit ledger
        if ($cleaningFee > 0) {
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => 'cleaning_fee',
                'amount' => $cleaningFee,
                'status' => 'completed',
                'metadata' => ['cleaning_type' => $validated['cleaning_type']],
            ]);
        }

        if ($totalPenalties > 0) {
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => 'penalty_deduction',
                'amount' => $totalPenalties,
                'status' => 'completed',
                'metadata' => ['penalties' => $penalties],
            ]);
        }

        Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'deposit_refund',
            'amount' => $depositRefunded,
            'status' => 'completed',
            'metadata' => [
                'original_deposit' => $depositAmount,
                'total_deductions' => $totalDeductions,
                'net_refund' => $depositRefunded,
            ],
        ]);

        $booking->update([
            'status' => 'checked_out',
        ]);

        return redirect()->route('host.checkouts.show', $booking)
            ->with('success', 'Checkout inspection completed! Penalty deductions calculated. Net deposit refund: ₱'.number_format($depositRefunded, 2));
    }
}
