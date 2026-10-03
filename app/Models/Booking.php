<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'booking_code',
    'unit_id',
    'user_id',
    'guest_name',
    'guest_email',
    'guest_phone',
    'guest_count',
    'guest_roster',
    'check_in_date',
    'check_out_date',
    'nights_count',
    'base_amount',
    'add_ons_amount',
    'advance_deposit_amount',
    'platform_fee',
    'total_amount',
    'status',
    'payment_status',
    'waiver_accepted_at',
    'verified_at',
    'verified_by',
    'notes',
])]
class Booking extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'guest_roster' => 'array',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'nights_count' => 'integer',
            'guest_count' => 'integer',
            'base_amount' => 'decimal:2',
            'add_ons_amount' => 'decimal:2',
            'advance_deposit_amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'waiver_accepted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Customer who made the booking (if registered/logged in).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Unit being booked.
     *
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Host user who verified this booking.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Selected add-on items.
     *
     * @return HasMany<BookingAddOn, $this>
     */
    public function bookingAddOns(): HasMany
    {
        return $this->hasMany(BookingAddOn::class);
    }

    /**
     * Compliance documents (IDs, selfie, GCash receipt).
     *
     * @return HasMany<ComplianceDocument, $this>
     */
    public function complianceDocuments(): HasMany
    {
        return $this->hasMany(ComplianceDocument::class);
    }

    /**
     * Post-checkout inspection record.
     *
     * @return HasOne<Checkout, $this>
     */
    public function checkout(): HasOne
    {
        return $this->hasOne(Checkout::class);
    }

    /**
     * Ledger transactions associated with this booking.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if payment receipt is uploaded.
     */
    public function hasPaymentReceipt(): bool
    {
        return $this->complianceDocuments()->where('document_type', 'payment_receipt')->exists();
    }

    /**
     * Check if government ID is uploaded.
     */
    public function hasGovernmentId(): bool
    {
        return $this->complianceDocuments()->where('document_type', 'like', 'gov_id_%')->exists();
    }

    /**
     * Check if verification selfie is uploaded.
     */
    public function hasVerificationSelfie(): bool
    {
        return $this->complianceDocuments()->where('document_type', 'selfie')->exists();
    }
}
