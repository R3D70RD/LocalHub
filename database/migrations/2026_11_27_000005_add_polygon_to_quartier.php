<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quartier', function (Blueprint $table) {
            // GeoJSON de la limite officielle (StatCan / municipalité).
            $table->json('polygon')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quartier', function (Blueprint $table) {
            $table->dropColumn('polygon');
        });
    }
};
