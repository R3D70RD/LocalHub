<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hiérarchie géographique globale : pays -> région -> ville -> quartier.
 *
 * Le Québec / Canada est semé en premier, mais la structure est neutre :
 * ajouter un pays ou une région plus tard ne demande que des données.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pays', function (Blueprint $table) {
            $table->increments('id_pays');
            $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2 (CA)
            $table->string('nom_fr', 100);
            $table->string('nom_en', 100);
            $table->char('devise', 3)->default('CAD'); // ISO 4217
            $table->string('indicatif_tel', 5)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('region', function (Blueprint $table) {
            $table->increments('id_region');
            $table->unsignedInteger('id_pays');
            $table->string('code', 10); // QC, ON, BC...
            $table->string('nom_fr', 100);
            $table->string('nom_en', 100);
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['id_pays', 'code']);
            $table->foreign('id_pays')->references('id_pays')->on('pays')->cascadeOnDelete();
        });

        Schema::create('ville', function (Blueprint $table) {
            $table->increments('id_ville');
            $table->unsignedInteger('id_region');
            $table->string('nom_fr', 120);
            $table->string('nom_en', 120);
            $table->string('prefixe_postal', 3)->nullable(); // Préfixe postal canadien (ex. G1)
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['id_region', 'nom_fr']);
            $table->foreign('id_region')->references('id_region')->on('region')->cascadeOnDelete();
        });

        Schema::create('quartier', function (Blueprint $table) {
            $table->increments('id_quartier');
            $table->unsignedInteger('id_ville');
            $table->string('nom_fr', 120);
            $table->string('nom_en', 120);
            $table->decimal('centroid_lat', 9, 6)->nullable();
            $table->decimal('centroid_lng', 9, 6)->nullable();
            $table->string('geohash', 7)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['id_ville', 'nom_fr']);
            $table->index('geohash');
            $table->foreign('id_ville')->references('id_ville')->on('ville')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quartier');
        Schema::dropIfExists('ville');
        Schema::dropIfExists('region');
        Schema::dropIfExists('pays');
    }
};
