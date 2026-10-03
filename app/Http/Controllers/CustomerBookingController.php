<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerBookingController extends Controller
{
    /**
     * Display customer's booking history and upcoming stays.
     */
    public function index(): View
    {
        $user = Auth::user();

        $bookings = Booking::with(['unit.building', 'complianceDocuments'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('guest_email', $user->email);
            })
            ->latest()
            ->get();

        return view('customer.bookings.index', [
            'user' => $user,
            'bookings' => $bookings,
        ]);
    }
}
