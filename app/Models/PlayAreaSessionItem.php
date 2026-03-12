<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayAreaSessionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'play_area_session_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(PlayAreaSession::class, 'play_area_session_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
