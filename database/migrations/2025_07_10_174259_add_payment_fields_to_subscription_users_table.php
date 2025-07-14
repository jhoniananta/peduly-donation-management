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
        Schema::table('subscription_users', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('user_id'); // pending, active, expired, cancelled
            $table->string('order_id')->nullable()->after('status');
            $table->decimal('amount', 15, 2)->default(0)->after('order_id');
            $table->string('payment_method')->nullable()->after('amount');
            $table->text('payment_link')->nullable()->after('payment_method');
            $table->datetime('start_date')->nullable()->after('payment_link');
            $table->datetime('expiring_time')->nullable()->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'order_id',
                'amount',
                'payment_method',
                'payment_link',
                'start_date',
                'expiring_time'
            ]);
        });
    }
};
