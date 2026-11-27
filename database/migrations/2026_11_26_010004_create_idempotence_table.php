<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotence', function (Blueprint $table) {
            $table->increments('id_idempotence');
            $table->unsignedInteger('id_utilisateur')->nullable();
            $table->string('cle', 64)->unique();
            $table->string('route', 255);
            $table->string('methode', 10);
            $table->unsignedSmallInteger('statut')->default(200);
            $table->json('reponse')->nullable();
            $table->timestamps();

            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->nullOnDelete();
            $table->index('id_utilisateur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotence');
    }
};
