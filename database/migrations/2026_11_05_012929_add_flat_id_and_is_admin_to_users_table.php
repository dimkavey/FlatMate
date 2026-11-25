<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolumna flat_id jest już tworzona w migracji create_expenses_table.
    }

    public function down(): void
    {
        // Brak zmian do cofnięcia.
    }
};
