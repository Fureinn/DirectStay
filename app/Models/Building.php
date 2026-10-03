<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'code',
    'address',
    'admin_email',
    'gate_pass_template',
    'house_rules',
])]
class Building extends Model
{
    /**
     * Units inside this building.
     *
     * @return HasMany<Unit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Exact GPS Latitude inside Urban Deca Homes Ortigas, Pasig.
     */
    public function getLatitudeAttribute(): float
    {
        return $this->code === 'UDH-P' ? 14.591915 : 14.592870;
    }

    /**
     * Exact GPS Longitude inside Urban Deca Homes Ortigas, Pasig.
     */
    public function getLongitudeAttribute(): float
    {
        return $this->code === 'UDH-P' ? 121.102448 : 121.102135;
    }
}
