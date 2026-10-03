<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a new rating and review for a stay.
     */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        // Security check: If authenticated, must own booking or email matches
        if (auth()->check()) {
            if ($booking->user_id && $booking->user_id !== auth()->id() && ! auth()->user()->isHost()) {
                abort(403, 'You are not authorized to review this reservation.');
            }
        }

        // Status check: Only non-cancelled bookings can be reviewed
        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cancelled bookings cannot be reviewed.');
        }

        if ($booking->review()->exists()) {
            return back()->with('error', 'You have already submitted a review for this stay reservation.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'cleanliness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'communication_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'accuracy_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'value_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $guestName = auth()->check() ? auth()->user()->name : $booking->guest_name;

        Review::create([
            'booking_id' => $booking->id,
            'unit_id' => $booking->unit_id,
            'user_id' => auth()->id() ?? $booking->user_id,
            'guest_name' => $guestName,
            'rating' => $validated['rating'],
            'cleanliness_rating' => $validated['cleanliness_rating'] ?? $validated['rating'],
            'communication_rating' => $validated['communication_rating'] ?? $validated['rating'],
            'accuracy_rating' => $validated['accuracy_rating'] ?? $validated['rating'],
            'value_rating' => $validated['value_rating'] ?? $validated['rating'],
            'comment' => ! empty($validated['comment']) ? trim($validated['comment']) : null,
        ]);

        return back()->with('success', 'Thank you! Your staycation rating and review have been published.');
    }
}
