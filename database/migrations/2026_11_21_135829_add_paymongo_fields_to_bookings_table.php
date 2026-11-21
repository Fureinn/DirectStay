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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('paymongo_checkout_id')->nullable()->index()->after('payment_status');
            $table->string('paymongo_payment_id')->nullable()->after('paymongo_checkout_id');
            $table->string('payment_method')->nullable()->after('paymongo_payment_id');
            $table->string('payment_scope')->nullable()->after('payment_method');
            $table->decimal('paid_amount', 10, 2)->nullable()->after('payment_scope');
            $table->timestamp('paid_at')->nullable()->after('paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'paymongo_checkout_id',
                'paymongo_payment_id',
                'payment_method',
                'payment_scope',
                'paid_amount',
                'paid_at',
            ]);
        });
    }
};
