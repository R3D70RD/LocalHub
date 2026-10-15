<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This replaces the default Laravel seeder, which targeted the `users`
     * table (name/email/password). That table does not exist in
     * database/init/01-localhubScript.sql, so the old User::factory() call
     * would fail. We seed the `utilisateur` table instead.
     *
     * updateOrCreate() keeps this idempotent: it will also REPAIR the demo
     * rows if 01-localhubScript.sql already inserted them with the plaintext
     * password 'TEMPORAIRE', replacing it with a proper bcrypt hash.
     */
    public function run(): void
    {
        $utilisateurs = [
            ['nom' => 'Tremblay', 'prenom' => 'Alex', 'courriel' => 'alex.tremblay@localhub.test', 'ville' => 'Montréal'],
            ['nom' => 'Gagnon', 'prenom' => 'Sarah', 'courriel' => 'sarah.gagnon@localhub.test', 'ville' => 'Montréal'],
            ['nom' => 'Roy', 'prenom' => 'Thomas', 'courriel' => 'thomas.roy@localhub.test', 'ville' => 'Laval'],
            ['nom' => 'Bouchard', 'prenom' => 'Emma', 'courriel' => 'emma.bouchard@localhub.test', 'ville' => 'Longueuil'],
            ['nom' => 'Côté', 'prenom' => 'Maxime', 'courriel' => 'maxime.cote@localhub.test', 'ville' => 'Montréal'],
        ];

        foreach ($utilisateurs as $utilisateur) {
            Utilisateur::updateOrCreate(
                ['courriel' => $utilisateur['courriel']],
                [
                    'nom' => $utilisateur['nom'],
                    'prenom' => $utilisateur['prenom'],
                    'ville' => $utilisateur['ville'],
                    // All demo accounts share this password for local testing.
                    // The `hashed` cast on Utilisateur keeps this a single hash.
                    'mot_de_passe' => Hash::make('password'),
                ],
            );
        }
    }
}
