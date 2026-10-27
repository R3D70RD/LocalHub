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
        Schema::create('evaluation', function (Blueprint $table) {
    $table->increments('id_evaluation');
    $table->unsignedInteger('id_evaluateur');
    $table->unsignedInteger('id_evalue');
    $table->unsignedInteger('id_publication');
    $table->unsignedTinyInteger('note');
    $table->text('commentaire')->nullable();
    $table->timestamp('date_evaluation')->useCurrent();
    $table->timestamps();

    $table->foreign('id_evaluateur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
    $table->foreign('id_evalue')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
    $table->foreign('id_publication')->references('id_publication')->on('publication')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
