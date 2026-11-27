<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // role is now part of the base utilisateurs migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: role is handled by the base utilisateurs migration.
    }
};
