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
        Schema::create('reservation', function (Blueprint $table) {
    $table->increments('id_reservation');
    $table->unsignedInteger('id_publication');
    $table->unsignedInteger('id_demandeur');
    $table->unsignedInteger('id_proprietaire');
    $table->date('date_debut');
    $table->date('date_fin');
    $table->string('statut', 30)->default('en_attente');
    $table->text('message')->nullable();
    $table->timestamp('date_demande')->useCurrent();
    $table->timestamps();

    $table->foreign('id_publication')->references('id_publication')->on('publication')->onDelete('cascade');
    $table->foreign('id_demandeur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
    $table->foreign('id_proprietaire')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
