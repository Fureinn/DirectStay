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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('inventory_checklist')->nullable();
            $table->string('cleaning_type')->default('none'); // none, basic (500), deep (1300)
            $table->decimal('cleaning_fee', 10, 2)->default(0.00);
            $table->json('penalties_breakdown')->nullable();
            $table->decimal('total_penalties', 10, 2)->default(0.00);
            $table->decimal('deposit_refunded', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
