<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
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

        $totalPenalties = Transaction::where('type', 'penalty_deduction')
            ->where('status', 'completed')
            ->sum('amount');

        $recentPenalties = Transaction::with('booking.unit')
            ->where('type', 'penalty_deduction')
            ->latest()
            ->take(5)
            ->get();

        $recentBookings = Booking::with(['unit.building', 'complianceDocuments'])
            ->latest()
            ->take(10)
            ->get();

        $blockedDates = BlockedDate::with('unit.building')
            ->orderBy('start_date')
            ->get();

        $calendarBookings = Booking::with('unit.building')
            ->whereIn('status', ['confirmed', 'checked_in', 'pending_verification'])
            ->get();

        $blockedDatesData = $blockedDates->map(fn ($b) => [
            'id' => $b->id,
            'unit_id' => $b->unit_id,
            'unit_number' => $b->unit?->unit_number ?? 'N/A',
            'building_code' => $b->unit?->building?->code ?? 'UDH',
            'start_date' => $b->start_date->format('Y-m-d'),
            'end_date' => $b->end_date->format('Y-m-d'),
            'reason' => $b->reason,
            'delete_url' => route('host.blocked-dates.destroy', $b),
        ]);

        $calendarBookingsData = $calendarBookings->map(fn ($b) => [
            'id' => $b->id,
            'unit_id' => $b->unit_id,
            'unit_number' => $b->unit?->unit_number ?? 'N/A',
            'building_code' => $b->unit?->building?->code ?? 'UDH',
            'guest_name' => $b->guest_name,
            'check_in_date' => $b->check_in_date->format('Y-m-d'),
            'check_out_date' => $b->check_out_date->format('Y-m-d'),
            'status' => $b->status,
        ]);

        return view('host.dashboard', [
            'buildings' => $buildings,
            'units' => $units,
            'pendingVerificationsCount' => $pendingVerificationsCount,
            'activeBookingsCount' => $activeBookingsCount,
            'completedBookingsCount' => $completedBookingsCount,
            'totalPlatformFees' => $totalPlatformFees,
            'totalDepositsHeld' => $totalDepositsHeld,
            'totalPenalties' => $totalPenalties,
            'recentPenalties' => $recentPenalties,
            'recentBookings' => $recentBookings,
            'blockedDates' => $blockedDates,
            'blockedDatesData' => $blockedDatesData,
            'calendarBookingsData' => $calendarBookingsData,
        ]);
    }
}
