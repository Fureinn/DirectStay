<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerTransactionController extends Controller
{
    /**
     * Display the signed-in buyer's GCash transaction ledger and receipts.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->isGuest(), 403);

        $transactions = Transaction::with(['booking.unit.building', 'booking.unit.host'])
            ->whereHas('booking', function ($query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhere('guest_email', $user->email);
            })
            ->latest()
            ->get();

        $totalPaid = $transactions
            ->where('status', 'completed')
            ->whereIn('type', ['advance_deposit', 'platform_fee', 'stay_payment'])
            ->sum('amount');

        $depositsInEscrow = $transactions
            ->where('status', 'completed')
            ->where('type', 'advance_deposit')
            ->sum('amount');

        $totalRefunded = $transactions
            ->where('status', 'completed')
            ->where('type', 'deposit_refund')
            ->sum('amount');

        $verifiedCount = $transactions->where('status', 'completed')->count();

        return view('customer.transactions.index', [
            'user' => $user,
            'transactions' => $transactions,
            'totalPaid' => $totalPaid,
            'depositsInEscrow' => $depositsInEscrow,
            'totalRefunded' => $totalRefunded,
            'verifiedCount' => $verifiedCount,
        ]);
    }
}
