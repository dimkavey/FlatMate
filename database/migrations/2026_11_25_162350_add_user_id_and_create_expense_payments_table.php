<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('flat_id')->constrained('users')->nullOnDelete();
        });

        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // 'pending' = do zapłaty, 'pending_approval' = do sprawdzenia przez admina / autora, 'paid' = opłacone
            $table->string('status')->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['expense_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
