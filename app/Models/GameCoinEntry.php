<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCoinEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'user_id',
        'entry_date',
        'coin_count',
        'coin_price',
        'total_amount',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
