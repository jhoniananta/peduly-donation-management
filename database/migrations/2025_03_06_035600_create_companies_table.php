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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->string('link_default')->nullable();
            $table->string('logo')->nullable();
            $table->string('hex_color')->nullable();
     

            // Fundraising yang ditampilkan di halaman utama
            $table->unsignedBigInteger('fundraising_id')->nullable();
            // untuk acc admin
            $table->enum('status', ['menunggu','diterima','ditolak'])->default('menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
