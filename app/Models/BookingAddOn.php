<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'add_on_id',
    'quantity',
    'price_per_unit',
    'total_price',
])]
class BookingAddOn extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_per_unit' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Booking this add-on selection belongs to.
     *
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The AddOn definition.
     *
     * @return BelongsTo<AddOn, $this>
     */
    public function addOn(): BelongsTo
    {
        return $this->belongsTo(AddOn::class);
    }
}
