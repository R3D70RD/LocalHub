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
            $table->unsignedInteger('id_categorie')->nullable();
            $table->unsignedInteger('id_quartier')->nullable();
            $table->unsignedInteger('id_ville')->nullable();
            $table->unsignedInteger('id_pays')->nullable();
            $table->string('titre', 150);
            $table->text('description');
            $table->decimal('prix', 10, 2)->nullable();
            $table->dateTime('date_heure')->nullable();
            $table->string('portee', 20)->default('quartier');
            $table->string('statut', 30)->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->string('image_path')->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamps();

            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
            $table->foreign('id_categorie')->references('id_categorie')->on('categorie')->nullOnDelete();
            $table->index('id_quartier');
            $table->index('id_ville');
            $table->index('id_pays');
            $table->index('portee');
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
