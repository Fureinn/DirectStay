<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionReceiptController extends Controller
{
    /**
     * Display the shared traceable GCash payment confirmation & receipt
     * accessible only to the buyer (guest) or seller (host).
     */
    public function show(Request $request, string $bookingCode): View
    {
        $user = $request->user();

        $booking = Booking::with(['unit.building', 'unit.host', 'user', 'transactions', 'bookingAddOns.addOn'])
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        $isBuyer = $booking->user_id === $user->id
            || strcasecmp($booking->guest_email, $user->email) === 0;
        $isSeller = $user->isHost();

        abort_unless($isBuyer || $isSeller, 403, 'You are not authorized to view this transaction receipt.');

        return view('transactions.receipt', [
            'booking' => $booking,
            'unit' => $booking->unit,
            'building' => $booking->unit->building,
            'seller' => $booking->unit->host,
            'isSeller' => $isSeller,
            'isBuyer' => $isBuyer,
        ]);
    }
}
