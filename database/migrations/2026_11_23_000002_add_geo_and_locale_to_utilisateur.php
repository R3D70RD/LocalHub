<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute la localisation structurée et les préférences de l'utilisateur.
 *
 * La colonne texte `ville` est conservée temporairement pour ne pas casser
 * les écrans existants ; elle sera retirée une fois l'inscription/le feed
 * migrés vers `id_ville`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->string('locale', 5)->default('fr');
            $table->unsignedInteger('id_ville')->nullable();
            $table->unsignedInteger('id_quartier')->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->string('geohash', 7)->nullable();
            $table->string('precision_pref', 20)->default('quartier');
            $table->unsignedSmallInteger('fiabilite')->default(100);

            $table->index('id_ville');
            $table->index('id_quartier');
            $table->index('geohash');
        });

        // SQLite ne permet pas d'ajouter une clé étrangère à une table
        // existante ; on l'ajoute uniquement sur MySQL (production).
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('utilisateur', function (Blueprint $table) {
                $table->foreign('id_ville')->references('id_ville')->on('ville')->nullOnDelete();
                $table->foreign('id_quartier')->references('id_quartier')->on('quartier')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('utilisateur', function (Blueprint $table) {
                $table->dropForeign(['id_ville']);
                $table->dropForeign(['id_quartier']);
            });
        }

        Schema::table('utilisateur', function (Blueprint $table) {
            $table->dropIndex(['id_ville']);
            $table->dropIndex(['id_quartier']);
            $table->dropIndex(['geohash']);
            $table->dropColumn([
                'locale',
                'id_ville',
                'id_quartier',
                'latitude',
                'longitude',
                'geohash',
                'precision_pref',
                'fiabilite',
            ]);
        });
    }
};
