<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suivi', function (Blueprint $table) {
            $table->increments('id_suivi');
            $table->unsignedInteger('id_utilisateur');
            $table->string('cible_type', 50);
            $table->unsignedInteger('cible_id');
            $table->timestamps();

            $table->unique(['id_utilisateur', 'cible_type', 'cible_id']);
            $table->index(['cible_type', 'cible_id']);
            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suivi');
    }
};
