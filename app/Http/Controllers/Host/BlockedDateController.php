<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockedDateController extends Controller
{
    /**
     * Store a new manually blocked date range for a unit.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['unit_id'] === 'all') {
            $units = Unit::all();
            $blockedCount = 0;

            foreach ($units as $unit) {
                $existingBlock = BlockedDate::where('unit_id', $unit->id)
                    ->where(function ($query) use ($validated) {
                        $query->where('start_date', '<=', $validated['end_date'])
                            ->where('end_date', '>=', $validated['start_date']);
                    })
                    ->exists();

                if (! $existingBlock) {
                    BlockedDate::create([
                        'unit_id' => $unit->id,
                        'user_id' => auth()->id(),
                        'start_date' => $validated['start_date'],
                        'end_date' => $validated['end_date'],
                        'reason' => $validated['reason'] ?: 'Host Manual Block (All Units)',
                    ]);
                    $blockedCount++;
                }
            }

            return back()->with('success', "Dates ({$validated['start_date']} to {$validated['end_date']}) successfully blocked across {$blockedCount} units!");
        }

        $request->validate([
            'unit_id' => ['exists:units,id'],
        ]);

        $unit = Unit::findOrFail($validated['unit_id']);

        // Check for existing overlapping manual block
        $existingBlock = BlockedDate::where('unit_id', $unit->id)
            ->where(function ($query) use ($validated) {
                $query->where('start_date', '<=', $validated['end_date'])
                    ->where('end_date', '>=', $validated['start_date']);
            })
            ->exists();

        if ($existingBlock) {
            return back()->with('error', 'This unit already has a blocked date range overlapping these dates.');
        }

        BlockedDate::create([
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'] ?: 'Host Manual Block (Maintenance / Personal Stay)',
        ]);

        return back()->with('success', "Dates ({$validated['start_date']} to {$validated['end_date']}) successfully blocked for Unit {$unit->unit_number}!");
    }

    /**
     * Remove a manually blocked date.
     */
    public function destroy(BlockedDate $blockedDate): RedirectResponse
    {
        $unitNumber = $blockedDate->unit?->unit_number ?? 'Unit';
        $blockedDate->delete();

        return back()->with('success', "Blocked date range unblocked successfully for {$unitNumber}!");
    }
}
