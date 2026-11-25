<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flat_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flat_id')->constrained('flats')->cascadeOnDelete();
            $table->string('type'); // 'rent' = czynsz, 'electricity' = prąd, 'gas' = gaz
            $table->string('title'); // np. 'Czynsz', 'Prąd', 'Gaz'
            $table->decimal('amount', 10, 2); // kwota, np. 1200.50
            $table->string('billing_month', 7); // miesiąc rozliczeniowy, np. '2026-11'
            $table->date('due_date'); // termin płatności (np. 10. dzień następnego miesiąca)
            $table->boolean('is_paid')->default(false); // czy opłacone
            $table->timestamps();

            // Zapobiega zdublowaniu np. dwóch rachunków za prąd w tym samym miesiącu dla tego samego mieszkania
            $table->unique(['flat_id', 'type', 'billing_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flat_bills');
    }
};
