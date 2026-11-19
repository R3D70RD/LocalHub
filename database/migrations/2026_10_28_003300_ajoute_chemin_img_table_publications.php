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
        // image_path is now part of the base publications migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: image_path is handled by the base publications migration.
    }
};
