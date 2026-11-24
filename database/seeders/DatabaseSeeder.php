<?php

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Ville;
use App\Support\Geohash;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GeoSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: the geographic reference data must exist before we can
     * link seeded users to a real city. `updateOrCreate()` keeps everything
     * idempotent, so this seeder can repair demo rows on re-run.
     */
    public function run(): void
    {
        $this->call(GeoSeeder::class);
        $this->call(CategorySeeder::class);

        $admin = User::updateOrCreate(
            ['courriel' => config('admin.email')],
            [
                'nom' => config('admin.nom'),
                'prenom' => config('admin.prenom'),
                'ville' => config('admin.ville'),
                'mot_de_passe' => Hash::make(config('admin.password')),
                'role' => User::ROLE_MANAGER,
            ],
        );
        $this->localiser($admin);

        $utilisateurs = [
            ['nom' => 'Tremblay', 'prenom' => 'Alex', 'courriel' => 'alex.tremblay@localhub.test', 'ville' => 'Montréal'],
            ['nom' => 'Gagnon', 'prenom' => 'Sarah', 'courriel' => 'sarah.gagnon@localhub.test', 'ville' => 'Montréal'],
            ['nom' => 'Roy', 'prenom' => 'Thomas', 'courriel' => 'thomas.roy@localhub.test', 'ville' => 'Laval'],
            ['nom' => 'Bouchard', 'prenom' => 'Emma', 'courriel' => 'emma.bouchard@localhub.test', 'ville' => 'Longueuil'],
            ['nom' => 'Côté', 'prenom' => 'Maxime', 'courriel' => 'maxime.cote@localhub.test', 'ville' => 'Montréal'],
        ];

        foreach ($utilisateurs as $utilisateurData) {
            $utilisateur = User::updateOrCreate(
                ['courriel' => $utilisateurData['courriel']],
                [
                    'nom' => $utilisateurData['nom'],
                    'prenom' => $utilisateurData['prenom'],
                    'ville' => $utilisateurData['ville'],
                    'mot_de_passe' => Hash::make('password'),
                ],
            );

            $this->localiser($utilisateur);

            Publication::factory()
                ->count(3)
                ->has(Reservation::factory()->count(2), 'reservations')
                ->create(['id_utilisateur' => $utilisateur->id_utilisateur]);

            Publication::factory()
                ->nonPublie()
                ->create(['id_utilisateur' => $utilisateur->id_utilisateur]);
        }
    }

    /**
     * Relie un utilisateur semé à sa ville structurée et calcule son geohash.
     */
    private function localiser(User $utilisateur): void
    {
        $ville = Ville::query()->where('nom_fr', $utilisateur->ville)->first();

        if ($ville === null) {
            return;
        }

        $utilisateur->forceFill([
            'id_ville' => $ville->id_ville,
            'latitude' => $ville->latitude,
            'longitude' => $ville->longitude,
            'geohash' => ($ville->latitude !== null && $ville->longitude !== null)
                ? Geohash::encode((float) $ville->latitude, (float) $ville->longitude, 7)
                : null,
        ])->save();
    }
}
