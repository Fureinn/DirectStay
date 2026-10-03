<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'building_id',
    'user_id',
    'unit_number',
    'title',
    'description',
    'cover_image',
    'images',
    'base_price_per_night',
    'advance_deposit_required',
    'max_guests',
    'inventory_items',
    'is_active',
])]
class Unit extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'base_price_per_night' => 'decimal:2',
            'advance_deposit_required' => 'decimal:2',
            'max_guests' => 'integer',
            'inventory_items' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Building the unit belongs to.
     *
     * @return BelongsTo<Building, $this>
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Host/Owner of the unit.
     *
     * @return BelongsTo<User, $this>
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Bookings for this unit.
     *
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Add-ons available for this unit.
     *
     * @return HasMany<AddOn, $this>
     */
    public function addOns(): HasMany
    {
        return $this->hasMany(AddOn::class);
    }

    /**
     * Reviews for this unit.
     *
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    /**
     * Calculate average star rating.
     */
    public function averageRating(): float
    {
        $avg = $this->reviews()->avg('rating');

        return $avg ? round((float) $avg, 1) : 5.0;
    }

    /**
     * Get review count.
     */
    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }
}
