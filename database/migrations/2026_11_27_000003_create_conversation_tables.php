<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation', function (Blueprint $table) {
            $table->increments('id_conversation');
            $table->string('cle', 64)->nullable()->unique();
            $table->string('titre', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_membre', function (Blueprint $table) {
            $table->increments('id_conversation_membre');
            $table->unsignedInteger('id_conversation');
            $table->unsignedInteger('id_utilisateur');
            $table->timestamp('dernier_lu_at')->nullable();
            $table->timestamps();

            $table->unique(['id_conversation', 'id_utilisateur']);
            $table->foreign('id_conversation')
                ->references('id_conversation')->on('conversation')->cascadeOnDelete();
            $table->foreign('id_utilisateur')
                ->references('id_utilisateur')->on('utilisateur')->cascadeOnDelete();
        });

        Schema::table('message', function (Blueprint $table) {
            $table->unsignedInteger('id_conversation')->nullable()->after('id_annonce');
            $table->index('id_conversation');
        });

        // SQLite ne permet pas d'ajouter une clé étrangère à une table
        // existante ; on l'ajoute uniquement sur MySQL.
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('message', function (Blueprint $table) {
                $table->foreign('id_conversation')
                    ->references('id_conversation')->on('conversation')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('message', function (Blueprint $table) {
                $table->dropForeign(['id_conversation']);
            });
        }

        Schema::table('message', function (Blueprint $table) {
            $table->dropIndex(['id_conversation']);
            $table->dropColumn('id_conversation');
        });

        Schema::dropIfExists('conversation_membre');
        Schema::dropIfExists('conversation');
    }
};
