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
        Schema::create('play_area_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
            $table->string('customer_email')->nullable();
            $table->unsignedInteger('customer_age')->nullable();

            $table->decimal('base_price', 12, 2)->default(0);
            $table->unsignedInteger('base_time_minutes')->default(0);
            $table->decimal('additional_payment', 12, 2)->default(0);
            $table->decimal('package_total', 12, 2)->default(0);

            $table->decimal('extra_charge', 12, 2)->default(0);
            $table->unsignedInteger('extra_charge_per_minutes')->default(1);

            $table->dateTime('start_time');
            $table->dateTime('expected_end_time');
            $table->dateTime('end_time')->nullable();

            $table->unsignedInteger('extra_minutes')->default(0);
            $table->decimal('extra_amount', 12, 2)->default(0);
            $table->decimal('products_total', 12, 2)->default(0);
            $table->decimal('final_total', 12, 2)->default(0);

            $table->enum('status', ['active', 'closed'])->default('active');
            $table->string('payment_method')->nullable();
            $table->decimal('cash', 12, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('play_area_sessions');
    }
};
