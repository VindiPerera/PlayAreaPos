<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make barcode nullable for pending sessions (no barcode yet)
        DB::statement('ALTER TABLE play_area_sessions MODIFY barcode VARCHAR(191) NULL');

        // Make start_time nullable for pending sessions
        DB::statement('ALTER TABLE play_area_sessions MODIFY start_time DATETIME NULL');

        // Add 'pending' to the status enum
        DB::statement("ALTER TABLE play_area_sessions MODIFY status ENUM('pending','active','closed') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE play_area_sessions MODIFY status ENUM('active','closed') NOT NULL DEFAULT 'active'");
        DB::statement('ALTER TABLE play_area_sessions MODIFY start_time DATETIME NOT NULL');
        DB::statement('ALTER TABLE play_area_sessions MODIFY barcode VARCHAR(191) NOT NULL');
    }
};
