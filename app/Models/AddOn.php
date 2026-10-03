<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'unit_id',
    'name',
    'price',
    'is_active',
])]
class AddOn extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Unit this add-on is scoped to, if not global.
     *
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Bookings using this add-on.
     *
     * @return HasMany<BookingAddOn, $this>
     */
    public function bookingAddOns(): HasMany
    {
        return $this->hasMany(BookingAddOn::class);
    }
}
