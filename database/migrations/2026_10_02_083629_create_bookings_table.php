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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone');
            $table->unsignedInteger('guest_count')->default(1);
            $table->json('guest_roster')->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedInteger('nights_count');
            $table->decimal('base_amount', 10, 2);
            $table->decimal('add_ons_amount', 10, 2)->default(0.00);
            $table->decimal('advance_deposit_amount', 10, 2)->default(1000.00);
            $table->decimal('platform_fee', 10, 2)->default(100.00);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending_verification'); // pending_verification, confirmed, checked_in, checked_out, cancelled
            $table->string('payment_status')->default('unpaid'); // unpaid, submitted, verified, refunded
            $table->timestamp('waiver_accepted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
