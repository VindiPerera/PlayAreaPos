<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'coin_product_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function coinProduct()
    {
        return $this->belongsTo(Product::class, 'coin_product_id');
    }

    public function entries()
    {
        return $this->hasMany(GameCoinEntry::class);
    }
}
