<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signalement', function (Blueprint $table) {
            $table->increments('id_signalement');
            $table->unsignedInteger('id_utilisateur');
            $table->string('cible_type', 50);
            $table->unsignedInteger('cible_id');
            $table->string('motif', 50)->default('autre');
            $table->text('description')->nullable();
            $table->string('statut', 20)->default('en_attente');
            $table->unsignedInteger('id_moderateur')->nullable();
            $table->timestamps();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_moderateur')
                ->references('id_utilisateur')->on('utilisateur')->nullOnDelete();
            $table->index(['cible_type', 'cible_id']);
            $table->index('statut');
        });

        Schema::create('moderation_action', function (Blueprint $table) {
            $table->increments('id_moderation_action');
            $table->unsignedInteger('id_signalement')->nullable();
            $table->unsignedInteger('id_moderateur');
            $table->string('action', 30);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('id_signalement')
                ->references('id_signalement')->on('signalement')->nullOnDelete();
            $table->foreign('id_moderateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
        });

        Schema::create('blocage', function (Blueprint $table) {
            $table->increments('id_blocage');
            $table->unsignedInteger('id_bloqueur');
            $table->unsignedInteger('id_bloque');
            $table->timestamps();

            $table->unique(['id_bloqueur', 'id_bloque']);
            $table->foreign('id_bloqueur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_bloque')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
        });

        Schema::create('avis_pret', function (Blueprint $table) {
            $table->increments('id_avis_pret');
            $table->unsignedInteger('id_pret');
            $table->unsignedInteger('id_auteur');
            $table->unsignedInteger('id_cible');
            $table->unsignedTinyInteger('note');
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['id_pret', 'id_auteur']);
            $table->foreign('id_pret')
                ->references('id_pret')->on('pret')->cascadeOnDelete();
            $table->foreign('id_auteur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->foreign('id_cible')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis_pret');
        Schema::dropIfExists('blocage');
        Schema::dropIfExists('moderation_action');
        Schema::dropIfExists('signalement');
    }
};
