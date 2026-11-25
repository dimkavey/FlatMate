<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flat_bill_payments', function (Blueprint $table) {$table->id();
            $table->foreignId('flat_bill_id')->constrained('flat_bills')->cascadeOnDelete();$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // 'pending' = do zapłaty, 'pending_approval' = do sprawdzenia przez admina, 'paid' = opłacone
            $table->string('status')->default('pending');$table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();$table->timestamps();

            $table->unique(['flat_bill_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flat_bill_payments');
    }
};
