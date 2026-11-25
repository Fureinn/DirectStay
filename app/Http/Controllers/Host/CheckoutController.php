<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Checkout;
use App\Models\Transaction;
use App\Services\PayMongoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $refundTransaction = $booking->transactions
            ->where('type', 'deposit_refund')
            ->sortByDesc('id')
            ->first();

        return view('host.checkouts.inspect', [
            'booking' => $booking,
            'unit' => $booking->unit,
            'building' => $booking->unit->building,
            'inventory' => $defaultInventory,
            'checkout' => $booking->checkout,
            'refundTransaction' => $refundTransaction,
        ]);
    }

    /**
     * Process inspection results, calculate cleaning and penalties, and settle deposit refund.
     */
    public function store(Request $request, Booking $booking, PayMongoService $payMongo): RedirectResponse
    {
        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cannot perform checkout inspection on a cancelled reservation.');
        }

        $validated = $request->validate([
            'inventory_status' => ['required', 'array'],
            'cleaning_type' => ['required', 'in:none,basic,deep'],
            'penalty_garbage' => ['nullable', 'boolean'],
            'penalty_lost_key' => ['nullable', 'boolean'],
            'penalty_smoking' => ['nullable', 'boolean'],
            'custom_penalty_reason' => ['nullable', 'string', 'max:255'],
            'custom_penalty_amount' => ['nullable', 'numeric', 'min:0', 'max:50000'],
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
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Record transactions for financial audit ledger
        if ($cleaningFee > 0) {
            Transaction::updateOrCreate(
                ['booking_id' => $booking->id, 'type' => 'cleaning_fee'],
                [
                    'amount' => $cleaningFee,
                    'status' => 'completed',
                    'metadata' => ['cleaning_type' => $validated['cleaning_type']],
                ]
            );
        }

        if ($totalPenalties > 0) {
            Transaction::updateOrCreate(
                ['booking_id' => $booking->id, 'type' => 'penalty_deduction'],
                [
                    'amount' => $totalPenalties,
                    'status' => 'completed',
                    'metadata' => ['penalties' => $penalties],
                ]
            );
        }

        $existingRefundTxn = Transaction::where('booking_id', $booking->id)
            ->where('type', 'deposit_refund')
            ->first();

        $refundMetadata = array_merge($existingRefundTxn?->metadata ?? [], [
            'original_deposit' => $depositAmount,
            'total_deductions' => $totalDeductions,
            'net_refund' => $depositRefunded,
        ]);
        $refundReference = $existingRefundTxn?->reference_number;
        $refundWarning = null;
        $refundNotice = null;

        $alreadyGatewayRefunded = ! empty($refundMetadata['refund_id']);

        if ($depositRefunded > 0 && ! empty($booking->paymongo_payment_id) && ! $alreadyGatewayRefunded) {
            try {
                $refundResult = $payMongo->refundPayment(
                    $booking,
                    $depositRefunded,
                    "Security deposit refund ({$booking->booking_code})"
                );

                $refundReference = $refundResult['refund_id'];
                $refundMetadata = array_merge($refundMetadata, [
                    'gateway' => 'paymongo',
                    'refund_id' => $refundResult['refund_id'],
                    'refund_status' => $refundResult['status'],
                    'transfer_link' => $refundResult['transfer_link'],
                    'refund_mode' => $refundResult['mode'],
                    'refund_error' => null,
                ]);

                $refundNotice = ! empty($refundResult['transfer_link'])
                    ? ' PayMongo QR Ph refund link generated (share with guest to claim).'
                    : ' PayMongo GCash refund initiated (Ref: '.$refundResult['refund_id'].').';
            } catch (\Throwable $e) {
                Log::error('PayMongo deposit refund failed', [
                    'booking_code' => $booking->booking_code,
                    'payment_id' => $booking->paymongo_payment_id,
                    'amount' => $depositRefunded,
                    'error' => $e->getMessage(),
                ]);

                $refundMetadata = array_merge($refundMetadata, [
                    'gateway' => 'paymongo',
                    'refund_status' => 'failed',
                    'refund_error' => $e->getMessage(),
                ]);

                $refundWarning = 'Checkout settled (Net refund: ₱'.number_format($depositRefunded, 2).'), but automatic PayMongo refund failed: '.$e->getMessage().' Please send manually.';
            }
        }

        Transaction::updateOrCreate(
            ['booking_id' => $booking->id, 'type' => 'deposit_refund'],
            [
                'amount' => $depositRefunded,
                'reference_number' => $refundReference,
                'status' => 'completed',
                'metadata' => $refundMetadata,
            ]
        );

        $booking->update([
            'status' => 'checked_out',
        ]);

        if ($refundWarning !== null) {
            return redirect()->route('host.checkouts.show', $booking)
                ->with('error', $refundWarning);
        }

        return redirect()->route('host.checkouts.show', $booking)
            ->with('success', 'Checkout inspection completed! Penalty deductions calculated. Net deposit refund: ₱'.number_format($depositRefunded, 2).($refundNotice ?? ''));
    }
}
