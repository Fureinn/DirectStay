<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display the seller's traceable GCash transaction ledger across all bookings.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');

        $query = Transaction::with(['booking.unit.building', 'booking.user'])->latest();

        if (in_array($statusFilter, ['completed', 'pending', 'refunded'], true)) {
            $query->where('status', $statusFilter);
        }

        $transactions = $query->paginate(20)->withQueryString();

        $allCompleted = Transaction::where('status', 'completed')->get();

        $totalReceived = $allCompleted
            ->whereIn('type', ['advance_deposit', 'platform_fee', 'stay_payment'])
            ->sum('amount');

        $escrowHeld = $allCompleted->where('type', 'advance_deposit')->sum('amount');
        $platformFees = $allCompleted->where('type', 'platform_fee')->sum('amount');
        $penaltiesAndRefunds = $allCompleted->whereIn('type', ['penalty_deduction', 'deposit_refund'])->sum('amount');

        return view('host.transactions.index', [
            'transactions' => $transactions,
            'statusFilter' => $statusFilter,
            'totalReceived' => $totalReceived,
            'escrowHeld' => $escrowHeld,
            'platformFees' => $platformFees,
            'penaltiesAndRefunds' => $penaltiesAndRefunds,
        ]);
    }
}
