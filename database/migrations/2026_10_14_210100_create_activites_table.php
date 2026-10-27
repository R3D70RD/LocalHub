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
        Schema::create('activite', function (Blueprint $table) {
    $table->increments('id_activite');
    $table->unsignedInteger('id_publication')->unique();
    $table->dateTime('date_heure');
    $table->string('lieu', 255);
    $table->unsignedInteger('max_participants');
    $table->timestamps();

    $table->foreign('id_publication')->references('id_publication')->on('publication')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};
