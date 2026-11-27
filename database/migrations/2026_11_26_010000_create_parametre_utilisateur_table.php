<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametre_utilisateur', function (Blueprint $table) {
            $table->increments('id_parametre_utilisateur');
            $table->unsignedInteger('id_utilisateur')->unique();
            $table->string('portee_defaut', 20)->default('quartier');
            $table->string('afficher_position', 20)->default('quartier');
            $table->boolean('notifications_actives')->default(true);
            $table->string('langue', 2)->nullable();
            $table->timestamps();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')
                ->on('utilisateur')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametre_utilisateur');
    }
};
