<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_coin_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('entry_date');
            $table->unsignedInteger('coin_count')->default(0);
            $table->decimal('coin_price', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_coin_entries');
    }
};
