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
        Schema::create('utilisateur', function (Blueprint $table) {
    $table->increments('id_utilisateur');
    $table->string('nom', 100);
    $table->string('prenom', 100);
    $table->string('courriel', 255)->unique();
    $table->string('mot_de_passe', 255);
    $table->string('ville', 100);
    $table->timestamp('date_inscription')->useCurrent();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
