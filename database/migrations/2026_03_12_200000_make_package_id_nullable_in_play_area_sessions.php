<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the non-nullable foreign key constraint, then re-add as nullable
        Schema::table('play_area_sessions', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        DB::statement('ALTER TABLE play_area_sessions MODIFY package_id BIGINT UNSIGNED NULL');

        Schema::table('play_area_sessions', function (Blueprint $table) {
            $table->foreign('package_id')->references('id')->on('packages')->nullOnDelete();
            $table->dateTime('expected_end_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('play_area_sessions', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        DB::statement('ALTER TABLE play_area_sessions MODIFY package_id BIGINT UNSIGNED NOT NULL');

        Schema::table('play_area_sessions', function (Blueprint $table) {
            $table->foreign('package_id')->references('id')->on('packages')->cascadeOnDelete();
            $table->dateTime('expected_end_time')->nullable(false)->change();
        });
    }
};
