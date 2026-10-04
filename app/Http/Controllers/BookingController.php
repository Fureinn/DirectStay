<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\BookingAddOn;
use App\Models\Building;
use App\Models\Transaction;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display catalog of available units across buildings.
     */
    public function index(Request $request): View
    {
        $buildings = Building::withCount(['units' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $totalUnitsCount = Unit::where('is_active', true)->count();

        $selectedBuilding = $request->query('building');
        $selectedGuests = $request->query('guests');
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');

        $unitsQuery = Unit::with(['building', 'reviews'])->where('is_active', true);

        if ($selectedBuilding) {
            $unitsQuery->whereHas('building', function ($q) use ($selectedBuilding) {
                $q->where('code', $selectedBuilding);
            });
        }

        if ($request->filled('guests') && (int) $selectedGuests > 0) {
            $unitsQuery->where('max_guests', '>=', (int) $selectedGuests);
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            try {
                $cIn = Carbon::parse($checkIn);
                $cOut = Carbon::parse($checkOut);
                if ($cIn->lt($cOut)) {
                    $bookedUnitIds = Booking::whereNotIn('status', ['cancelled'])
                        ->where(function ($q) use ($cIn, $cOut) {
                            $q->where('check_in_date', '<', $cOut->toDateString())
                                ->where('check_out_date', '>', $cIn->toDateString());
                        })
                        ->pluck('unit_id');

                    $blockedUnitIds = BlockedDate::where(function ($q) use ($cIn, $cOut) {
                        $q->where('start_date', '<', $cOut->toDateString())
                            ->where('end_date', '>=', $cIn->toDateString());
                    })->pluck('unit_id');

                    $unavailableUnitIds = $bookedUnitIds->merge($blockedUnitIds)->unique();
                    $unitsQuery->whereNotIn('id', $unavailableUnitIds);
                }
            } catch (\Throwable $e) {
                // Ignore parse errors gracefully
            }
        }

        $units = $unitsQuery->get();

        // All active units for map pins so both buildings remain interactive on map
        $allUnits = Unit::with(['building', 'reviews'])->where('is_active', true)->get();

        return view('units.index', [
            'buildings' => $buildings,
            'units' => $units,
            'allUnits' => $allUnits,
            'totalUnitsCount' => $totalUnitsCount,
            'selectedBuilding' => $selectedBuilding,
            'selectedGuests' => $selectedGuests,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
        ]);
    }

    /**
     * Display a specific unit booking page with live calendar & add-ons.
     */
    public function show(Unit $unit, Request $request): View
    {
        $unit->load(['building', 'reviews.user']);

        // Fetch booked dates for this unit to prevent double-booking
        $existingBookings = Booking::where('unit_id', $unit->id)
            ->whereNotIn('status', ['cancelled'])
            ->get(['check_in_date', 'check_out_date']);

        $manualBlocked = BlockedDate::where('unit_id', $unit->id)
            ->where('end_date', '>=', now()->toDateString())
            ->get(['start_date', 'end_date']);

        $blockedDates = [];
        foreach ($existingBookings as $b) {
            $start = Carbon::parse($b->check_in_date);
            $end = Carbon::parse($b->check_out_date);
            while ($start->lt($end)) {
                $blockedDates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }

        foreach ($manualBlocked as $mb) {
            $start = Carbon::parse($mb->start_date);
            $end = Carbon::parse($mb->end_date);
            while ($start->lte($end)) {
                $blockedDates[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }

        $addOns = AddOn::where('is_active', true)
            ->where(function ($q) use ($unit) {
                $q->whereNull('unit_id')->orWhere('unit_id', $unit->id);
            })
            ->get();

        return view('units.show', [
            'unit' => $unit,
            'addOns' => $addOns,
            'blockedDates' => array_values(array_unique($blockedDates)),
            'defaultCheckIn' => $request->query('check_in'),
            'defaultCheckOut' => $request->query('check_out'),
            'defaultGuests' => $request->query('guests'),
        ]);
    }

    /**
     * Store a new booking reservation and redirect to Security & ID Compliance Portal.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:30'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:10'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'add_ons' => ['nullable', 'array'],
            'add_ons.*' => ['integer', 'min:0'],
        ]);

        $unit = Unit::with('building')->findOrFail($validated['unit_id']);

        if ((int) $validated['guest_count'] > (int) $unit->max_guests) {
            return back()->withErrors([
                'guest_count' => "The selected guest count ({$validated['guest_count']}) exceeds the maximum occupancy ({$unit->max_guests}) for {$unit->title}.",
            ])->withInput();
        }

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);

        if ($checkIn->diffInDays($checkOut) > 90) {
            return back()->withErrors([
                'check_out_date' => 'Reservations are limited to a maximum duration of 90 nights.',
            ])->withInput();
        }

        // Double booking prevention check
        $hasOverlap = Booking::where('unit_id', $unit->id)
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->where('check_in_date', '<', $checkOut)
                        ->where('check_out_date', '>', $checkIn);
                });
            })
            ->exists();

        $isManuallyBlocked = BlockedDate::where('unit_id', $unit->id)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('start_date', '<', $checkOut->toDateString())
                    ->where('end_date', '>=', $checkIn->toDateString());
            })
            ->exists();

        if ($hasOverlap || $isManuallyBlocked) {
            return back()->withErrors([
                'check_in_date' => 'The selected dates are no longer available for this unit. Please select different dates.',
            ])->withInput();
        }

        $nights = $checkIn->diffInDays($checkOut);
        if ($nights < 1) {
            $nights = 1;
        }

        $baseAmount = $nights * (float) $unit->base_price_per_night;
        $advanceDeposit = (float) $unit->advance_deposit_required; // ₱1,000.00
        $platformFee = round($baseAmount * 0.05, 2); // DirectStay 5% guest platform service fee

        // Calculate Add-Ons safely (only active add-ons belonging to this unit or global)
        $addOnsAmount = 0.00;
        $selectedAddOns = [];
        if (! empty($validated['add_ons'])) {
            $availableAddOns = AddOn::where('is_active', true)
                ->where(function ($q) use ($unit) {
                    $q->whereNull('unit_id')->orWhere('unit_id', $unit->id);
                })
                ->whereIn('id', array_keys($validated['add_ons']))
                ->get()
                ->keyBy('id');

            foreach ($validated['add_ons'] as $addOnId => $quantity) {
                if ($quantity > 0 && isset($availableAddOns[$addOnId])) {
                    $item = $availableAddOns[$addOnId];
                    $lineTotal = (float) $item->price * (int) $quantity;
                    $addOnsAmount += $lineTotal;
                    $selectedAddOns[] = [
                        'add_on_id' => $item->id,
                        'quantity' => (int) $quantity,
                        'price_per_unit' => (float) $item->price,
                        'total_price' => $lineTotal,
                    ];
                }
            }
        }

        $totalAmount = $baseAmount + $addOnsAmount + $advanceDeposit + $platformFee;

        // Generate distinctive booking reference
        $bookingCode = 'DS-'.strtoupper(Str::random(4)).'-'.rand(1000, 9999);

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'guest_count' => $validated['guest_count'],
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'nights_count' => $nights,
            'base_amount' => $baseAmount,
            'add_ons_amount' => $addOnsAmount,
            'advance_deposit_amount' => $advanceDeposit,
            'platform_fee' => $platformFee,
            'total_amount' => $totalAmount,
            'status' => 'pending_verification',
            'payment_status' => 'unpaid',
        ]);

        // Save selected add-ons
        foreach ($selectedAddOns as $addonRow) {
            $addonRow['booking_id'] = $booking->id;
            BookingAddOn::create($addonRow);
        }

        // Create pending advance deposit transaction record
        Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'advance_deposit',
            'amount' => $advanceDeposit,
            'status' => 'pending',
            'metadata' => [
                'description' => 'Mandatory advance deposit of ₱1,000.00 for reservation confirmation',
            ],
        ]);

        // Create platform fee ledger transaction
        Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'platform_fee',
            'amount' => $platformFee,
            'status' => 'pending',
            'metadata' => [
                'description' => 'DirectStay flat service fee (Micro-commission)',
            ],
        ]);

        return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code])
            ->with('success', 'Reservation initiated! Please submit your security deposit proof and Government ID to complete confirmation.');
    }
}
