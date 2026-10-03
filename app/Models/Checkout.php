<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'inspected_by',
    'inventory_checklist',
    'cleaning_type',
    'cleaning_fee',
    'penalties_breakdown',
    'total_penalties',
    'deposit_refunded',
    'notes',
])]
class Checkout extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'inventory_checklist' => 'array',
            'penalties_breakdown' => 'array',
            'cleaning_fee' => 'decimal:2',
            'total_penalties' => 'decimal:2',
            'deposit_refunded' => 'decimal:2',
        ];
    }

    /**
     * Booking this checkout belongs to.
     *
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Host/Staff who conducted the inspection.
     *
     * @return BelongsTo<User, $this>
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }
}
