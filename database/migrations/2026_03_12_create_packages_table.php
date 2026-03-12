<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Package name
            $table->enum('type', ['elder', 'kid']); // Package type
            $table->decimal('base_price', 10, 2); // Base price
            $table->integer('base_time_minutes'); // Time in minutes for base price
            $table->decimal('extra_charge', 10, 2); // Extra charge per unit time
            $table->integer('extra_charge_per_minutes'); // Minutes for each extra charge
            $table->integer('age_threshold')->default(10); // Age threshold for additional payment
            $table->decimal('additional_payment', 10, 2)->nullable(); // Additional payment for age > threshold
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
