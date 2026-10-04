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
     * Dates manually blocked by the host.
     *
     * @return HasMany<BlockedDate, $this>
     */
    public function blockedDates(): HasMany
    {
        return $this->hasMany(BlockedDate::class);
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

    /**
     * GPS Latitude for map display.
     */
    public function getLatitudeAttribute(): float
    {
        return $this->building?->latitude ?? 14.59239;
    }

    /**
     * GPS Longitude for map display.
     */
    public function getLongitudeAttribute(): float
    {
        return $this->building?->longitude ?? 121.10229;
    }

    /**
     * Get list of all photos for galleries and sliders.
     *
     * @return array<int, string>
     */
    public function galleryImages(): array
    {
        $images = $this->images ?? [];
        if (empty($images) && $this->cover_image) {
            $images = [$this->cover_image];
        }

        if (empty($images)) {
            $images = ['images/units/unit_n412.jpg'];
        }

        return array_values($images);
    }

    /**
     * Get categorized top featured amenities for card display.
     *
     * @return array<int, array{icon: string, label: string, desc: string}>
     */
    public function featuredAmenities(): array
    {
        $items = collect($this->inventory_items ?? []);
        $amenities = [];

        if ($items->contains(fn ($i) => stripos($i, 'Air') !== false || stripos($i, 'Aircon') !== false)) {
            $amenities[] = ['icon' => 'snowflake', 'label' => 'Inverter AC', 'desc' => 'Dual split-type air conditioning'];
        }

        $amenities[] = ['icon' => 'wifi', 'label' => 'High-Speed WiFi', 'desc' => 'Fast fiber internet included'];

        if ($items->contains(fn ($i) => stripos($i, 'Television') !== false || stripos($i, 'TV') !== false || stripos($i, 'Android') !== false)) {
            $amenities[] = ['icon' => 'tv', 'label' => 'Smart TV & Netflix', 'desc' => 'Android TV streaming ready'];
        }

        if ($items->contains(fn ($i) => stripos($i, 'Induction') !== false || stripos($i, 'Cooker') !== false || stripos($i, 'Kitchen') !== false)) {
            $amenities[] = ['icon' => 'kitchen', 'label' => 'Full Kitchenette', 'desc' => 'Induction cooker & cookware'];
        }

        if ($items->contains(fn ($i) => stripos($i, 'Refrigerator') !== false || stripos($i, 'Microwave') !== false)) {
            $amenities[] = ['icon' => 'fridge', 'label' => 'Ref & Microwave', 'desc' => 'Food storage and microwave'];
        }

        if ($items->contains(fn ($i) => stripos($i, 'Washing') !== false)) {
            $amenities[] = ['icon' => 'washer', 'label' => 'Washing Machine', 'desc' => 'In-unit laundry washing machine'];
        }

        if ($items->contains(fn ($i) => stripos($i, 'Bed') !== false || stripos($i, 'Mattress') !== false)) {
            $amenities[] = ['icon' => 'bed', 'label' => 'Queen & Loft Bed', 'desc' => 'Double loft & queen bed setup'];
        }

        $amenities[] = ['icon' => 'pool', 'label' => 'Deca Pool Access', 'desc' => 'Condo complex swimming pool access'];

        return array_slice($amenities, 0, 6);
    }
}
