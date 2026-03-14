<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'base_price',
        'base_time_minutes',
        'extra_charge',
        'extra_charge_per_minutes',
        'age_threshold',
        'additional_payment',
        'description',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'extra_charge' => 'decimal:2',
        'additional_payment' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate total price based on duration.
     */
    public function calculatePrice($durationMinutes, $age = null)
    {
        $price = $this->base_price;

        if ($this->additional_payment) {
            $price += $this->additional_payment;
        }

        // Calculate extra charges if duration exceeds base time
        if ($durationMinutes > $this->base_time_minutes) {
            $extraMinutes = $durationMinutes - $this->base_time_minutes;
            $extraChargeUnits = ceil($extraMinutes / $this->extra_charge_per_minutes);
            $price += $extraChargeUnits * $this->extra_charge;
        }

        return round($price, 2);
    }

    /**
     * Get package type label
     */
    public function getTypeLabel()
    {
        return ucfirst($this->type);
    }
}
