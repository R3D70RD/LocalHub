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
        Schema::create('publication', function (Blueprint $table) {
    $table->increments('id_publication');
    $table->unsignedInteger('id_utilisateur');
    $table->unsignedInteger('id_categorie');
    $table->string('titre', 150);
    $table->text('description');
    $table->string('type', 30);
    $table->decimal('prix', 10, 2)->nullable();
    $table->string('ville', 100);
    $table->string('statut', 30)->default('active');
    $table->timestamp('date_creation')->useCurrent();
    $table->timestamps();

    $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
    $table->foreign('id_categorie')->references('id_categorie')->on('categorie')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
