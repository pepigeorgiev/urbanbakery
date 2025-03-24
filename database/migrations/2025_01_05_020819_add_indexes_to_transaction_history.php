<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_history', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['transaction_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('transaction_history', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['transaction_id', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};