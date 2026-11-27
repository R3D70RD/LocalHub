<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_aide', function (Blueprint $table) {
            $table->increments('id_demande_aide');
            $table->unsignedInteger('id_utilisateur');
            $table->unsignedInteger('id_categorie')->nullable();
            $table->unsignedInteger('id_quartier')->nullable();
            $table->unsignedInteger('id_ville')->nullable();
            $table->unsignedInteger('id_pays')->nullable();
            $table->string('titre', 150);
            $table->text('description');
            $table->string('lang', 2)->default('fr');
            $table->string('portee', 20)->default('quartier');
            $table->boolean('urgent')->default(false);
            $table->date('date_limite')->nullable();
            $table->string('statut', 20)->default('ouverte');
            $table->timestamps();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_categorie')
                ->references('id_categorie')->on('categorie')->nullOnDelete();
            $table->index(['id_quartier', 'id_ville', 'id_pays']);
        });

        Schema::create('offre_aide', function (Blueprint $table) {
            $table->increments('id_offre_aide');
            $table->unsignedInteger('id_demande_aide')->nullable();
            $table->unsignedInteger('id_utilisateur');
            $table->unsignedInteger('id_quartier')->nullable();
            $table->unsignedInteger('id_ville')->nullable();
            $table->unsignedInteger('id_pays')->nullable();
            $table->string('titre', 150);
            $table->text('description');
            $table->string('lang', 2)->default('fr');
            $table->string('portee', 20)->default('quartier');
            $table->string('statut', 20)->default('proposee');
            $table->timestamps();

            $table->foreign('id_demande_aide')
                ->references('id_demande_aide')->on('demande_aide')->nullOnDelete();
            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
        });

        Schema::create('evenement', function (Blueprint $table) {
            $table->increments('id_evenement');
            $table->unsignedInteger('id_utilisateur');
            $table->unsignedInteger('id_categorie')->nullable();
            $table->unsignedInteger('id_quartier')->nullable();
            $table->unsignedInteger('id_ville')->nullable();
            $table->unsignedInteger('id_pays')->nullable();
            $table->string('titre', 150);
            $table->text('description');
            $table->string('lang', 2)->default('fr');
            $table->string('portee', 20)->default('quartier');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin')->nullable();
            $table->string('timezone', 64)->default('America/Toronto');
            $table->string('lieu', 255)->nullable();
            $table->unsignedSmallInteger('capacite')->nullable();
            $table->string('statut', 20)->default('actif');
            $table->timestamps();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_categorie')
                ->references('id_categorie')->on('categorie')->nullOnDelete();
            $table->index(['date_debut']);
        });

        Schema::create('inscription_evenement', function (Blueprint $table) {
            $table->increments('id_inscription_evenement');
            $table->unsignedInteger('id_evenement');
            $table->unsignedInteger('id_utilisateur');
            $table->string('statut', 20)->default('inscrit');
            $table->timestamps();

            $table->foreign('id_evenement')
                ->references('id_evenement')->on('evenement')->cascadeOnDelete();
            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->unique(['id_evenement', 'id_utilisateur']);
        });

        Schema::create('annonce', function (Blueprint $table) {
            $table->increments('id_annonce');
            $table->unsignedInteger('id_utilisateur');
            $table->unsignedInteger('id_categorie')->nullable();
            $table->unsignedInteger('id_quartier')->nullable();
            $table->unsignedInteger('id_ville')->nullable();
            $table->unsignedInteger('id_pays')->nullable();
            $table->string('titre', 150);
            $table->text('description');
            $table->string('lang', 2)->default('fr');
            $table->string('portee', 20)->default('quartier');
            $table->string('type_annonce', 20)->default('vente');
            $table->decimal('prix', 10, 2)->nullable();
            $table->string('statut', 20)->default('active');
            $table->timestamps();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_categorie')
                ->references('id_categorie')->on('categorie')->nullOnDelete();
            $table->index(['id_quartier', 'id_ville', 'id_pays']);
        });

        Schema::create('offre_annonce', function (Blueprint $table) {
            $table->increments('id_offre_annonce');
            $table->unsignedInteger('id_annonce');
            $table->unsignedInteger('id_utilisateur');
            $table->decimal('montant', 10, 2)->nullable();
            $table->text('message')->nullable();
            $table->string('statut', 20)->default('proposee');
            $table->timestamps();

            $table->foreign('id_annonce')
                ->references('id_annonce')->on('annonce')->cascadeOnDelete();
            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
        });

        Schema::create('outil', function (Blueprint $table) {
            $table->increments('id_outil');
            $table->unsignedInteger('id_proprietaire');
            $table->unsignedInteger('id_categorie')->nullable();
            $table->unsignedInteger('id_quartier')->nullable();
            $table->unsignedInteger('id_ville')->nullable();
            $table->unsignedInteger('id_pays')->nullable();
            $table->string('titre', 150);
            $table->text('description');
            $table->string('lang', 2)->default('fr');
            $table->string('portee', 20)->default('quartier');
            $table->string('etat', 20)->default('bon');
            $table->decimal('caution', 10, 2)->nullable();
            $table->boolean('disponible')->default(true);
            $table->string('statut', 20)->default('actif');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->foreign('id_proprietaire')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_categorie')
                ->references('id_categorie')->on('categorie')->nullOnDelete();
            $table->index(['id_quartier', 'id_ville', 'id_pays']);
            $table->index('disponible');
        });

        Schema::create('pret', function (Blueprint $table) {
            $table->increments('id_pret');
            $table->unsignedInteger('id_outil');
            $table->unsignedInteger('id_emprunteur');
            $table->unsignedInteger('id_proprietaire');
            $table->string('statut', 20)->default('demande');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->timestamp('date_demande')->useCurrent();
            $table->timestamp('date_retour_effectif')->nullable();
            $table->text('message')->nullable();
            $table->unsignedInteger('version')->default(1);
            // Colonne unique nullable : équivalent portable d'un index partiel
            // « un seul prêt actif par outil » (MySQL 8.4 n'a pas d'index partiel).
            $table->string('cle_active', 40)->nullable()->unique();
            $table->timestamps();

            $table->foreign('id_outil')
                ->references('id_outil')->on('outil')->cascadeOnDelete();
            $table->foreign('id_emprunteur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_proprietaire')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->index('statut');
            $table->index('date_fin');
        });

        Schema::create('rencontre', function (Blueprint $table) {
            $table->increments('id_rencontre');
            $table->unsignedInteger('id_utilisateur');
            $table->unsignedInteger('id_categorie')->nullable();
            $table->unsignedInteger('id_quartier')->nullable();
            $table->unsignedInteger('id_ville')->nullable();
            $table->unsignedInteger('id_pays')->nullable();
            $table->string('titre', 150);
            $table->text('description');
            $table->string('lang', 2)->default('fr');
            $table->string('portee', 20)->default('quartier');
            $table->dateTime('date_heure')->nullable();
            $table->string('lieu', 255)->nullable();
            $table->unsignedSmallInteger('capacite')->nullable();
            $table->json('interets')->nullable();
            $table->string('statut', 20)->default('ouverte');
            $table->timestamps();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_categorie')
                ->references('id_categorie')->on('categorie')->nullOnDelete();
        });

        Schema::create('rencontre_membre', function (Blueprint $table) {
            $table->increments('id_rencontre_membre');
            $table->unsignedInteger('id_rencontre');
            $table->unsignedInteger('id_utilisateur');
            $table->string('statut', 20)->default('inscrit');
            $table->timestamps();

            $table->foreign('id_rencontre')
                ->references('id_rencontre')->on('rencontre')->cascadeOnDelete();
            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->unique(['id_rencontre', 'id_utilisateur']);
        });

        Schema::create('message', function (Blueprint $table) {
            $table->increments('id_message');
            $table->unsignedInteger('id_pret')->nullable();
            $table->unsignedInteger('id_annonce')->nullable();
            $table->unsignedInteger('id_expediteur');
            $table->text('contenu');
            $table->string('lang', 2)->default('fr');
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();

            $table->foreign('id_pret')
                ->references('id_pret')->on('pret')->cascadeOnDelete();
            $table->foreign('id_annonce')
                ->references('id_annonce')->on('annonce')->cascadeOnDelete();
            $table->foreign('id_expediteur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->index('id_pret');
            $table->index('id_annonce');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message');
        Schema::dropIfExists('rencontre_membre');
        Schema::dropIfExists('rencontre');
        Schema::dropIfExists('pret');
        Schema::dropIfExists('outil');
        Schema::dropIfExists('offre_annonce');
        Schema::dropIfExists('annonce');
        Schema::dropIfExists('inscription_evenement');
        Schema::dropIfExists('evenement');
        Schema::dropIfExists('offre_aide');
        Schema::dropIfExists('demande_aide');
    }
};
