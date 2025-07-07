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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundraising_id')->constrained()->restrictOnDelete();
            $table->foreignId('donor_id')->constrained()->restrictOnDelete();
            $table->string('wish')->nullable();
            $table->decimal('total',12,2);
            $table->string('order_id')->unique();
            $table->string('payment_link');
            $table->string('method');
            $table->string('bank_name')->nullable();
            $table->dateTime('expiring_time')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['settlement', 'pending', 'expire','cancel','deny']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
