<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Transaction;
use App\Models\Unit;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display host dashboard overview.
     */
    public function index(): View
    {
        $buildings = Building::withCount('units')->get();
        $units = Unit::with('building')->get();

        $pendingVerificationsCount = Booking::where(function ($q) {
            $q->where('status', 'pending_verification')
                ->orWhere('payment_status', 'submitted');
        })->count();

        $activeBookingsCount = Booking::whereIn('status', ['confirmed', 'checked_in'])->count();
        $completedBookingsCount = Booking::where('status', 'checked_out')->count();

        // Calculate total micro-commissions (platform fees) and deposits
        $totalPlatformFees = Transaction::where('type', 'platform_fee')
            ->where('status', 'completed')
            ->sum('amount');

        $totalDepositsHeld = Transaction::where('type', 'advance_deposit')
            ->where('status', 'completed')
            ->sum('amount');

        $recentBookings = Booking::with(['unit.building', 'complianceDocuments'])
            ->latest()
            ->take(10)
            ->get();

        return view('host.dashboard', [
            'buildings' => $buildings,
            'units' => $units,
            'pendingVerificationsCount' => $pendingVerificationsCount,
            'activeBookingsCount' => $activeBookingsCount,
            'completedBookingsCount' => $completedBookingsCount,
            'totalPlatformFees' => $totalPlatformFees,
            'totalDepositsHeld' => $totalDepositsHeld,
            'recentBookings' => $recentBookings,
        ]);
    }
}
