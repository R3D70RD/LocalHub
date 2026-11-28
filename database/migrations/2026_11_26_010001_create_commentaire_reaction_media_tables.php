<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commentaire', function (Blueprint $table) {
            $table->increments('id_commentaire');
            $table->unsignedInteger('id_publication');
            $table->unsignedInteger('id_utilisateur');
            $table->text('contenu');
            $table->timestamps();

            $table->foreign('id_publication')
                ->references('id_publication')->on('publication')->cascadeOnDelete();
            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->index('id_publication');
        });

        Schema::create('reaction', function (Blueprint $table) {
            $table->increments('id_reaction');
            $table->unsignedInteger('id_publication');
            $table->unsignedInteger('id_utilisateur');
            $table->string('type', 30)->default('j_aime');
            $table->timestamps();

            $table->foreign('id_publication')
                ->references('id_publication')->on('publication')->cascadeOnDelete();
            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
            $table->unique(['id_publication', 'id_utilisateur', 'type']);
        });

        Schema::create('media', function (Blueprint $table) {
            $table->increments('id_media');
            $table->string('cible_type', 100);
            $table->unsignedInteger('cible_id');
            $table->string('chemin');
            $table->string('type', 20)->default('image');
            $table->string('alt', 255)->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();

            $table->index(['cible_type', 'cible_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('reaction');
        Schema::dropIfExists('commentaire');
    }
};
