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
        Schema::create('bread_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('bread_type_id')->constrained();
            $table->date('delivery_date');
            $table->integer('quantity')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate orders
            $table->unique(['user_id', 'bread_type_id', 'delivery_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bread_orders');
    }
};