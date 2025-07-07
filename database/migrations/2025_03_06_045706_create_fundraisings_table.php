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
        Schema::create('fundraisings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('target');
            $table->string('banner');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['menunggu', 'aktif', 'selesai',])->default('menunggu');
            $table->boolean('is_hide_target')->default(false);
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fundrisings');
    }
};
