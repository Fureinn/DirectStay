<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'amount_centavos',
    'status',
    'payment_scope',
    'paymongo_payment_id',
    'paid_at',
])]
class Payment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_centavos' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Booking associated with this payment attempt.
     *
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Determine whether this payment has been settled.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Format the centavo amount in Philippine Pesos (e.g. 1,000.01).
     */
    public function formattedAmount(): string
    {
        return number_format($this->amount_centavos / 100, 2);
    }
}
