<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayAreaSession extends Model
{
    use HasFactory;
    use GeneratesUniqueCode;

    protected $fillable = [
        'barcode',
        'package_id',
        'package_quantity',
        'employee_id',
        'user_id',
        'customer_name',
        'customer_contact',
        'customer_email',
        'customer_age',
        'base_price',
        'base_time_minutes',
        'additional_payment',
        'package_total',
        'extra_charge',
        'extra_charge_per_minutes',
        'start_time',
        'expected_end_time',
        'end_time',
        'extra_minutes',
        'extra_amount',
        'products_total',
        'final_total',
        'status',
        'payment_method',
        'cash',
        'note',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'additional_payment' => 'decimal:2',
        'package_total' => 'decimal:2',
        'extra_charge' => 'decimal:2',
        'extra_amount' => 'decimal:2',
        'products_total' => 'decimal:2',
        'final_total' => 'decimal:2',
        'cash' => 'decimal:2',
        'start_time' => 'datetime',
        'expected_end_time' => 'datetime',
        'end_time' => 'datetime',
        'package_quantity' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function items()
    {
        return $this->hasMany(PlayAreaSessionItem::class);
    }
}
