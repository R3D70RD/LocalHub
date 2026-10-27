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
        Schema::create('participation', function (Blueprint $table) {
    $table->unsignedInteger('id_activite');
    $table->unsignedInteger('id_utilisateur');
    $table->timestamp('date_inscription')->useCurrent();
    $table->timestamps();

    $table->primary(['id_activite', 'id_utilisateur']);
    $table->foreign('id_activite')->references('id_activite')->on('activite')->onDelete('cascade');
    $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participation');
    }
};
