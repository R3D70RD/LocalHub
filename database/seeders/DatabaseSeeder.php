<?php

namespace Database\Seeders;

use App\Enums\PorteeVisibilite;
use App\Models\Annonce;
use App\Models\Category;
use App\Models\Commentaire;
use App\Models\DemandeAide;
use App\Models\Evenement;
use App\Models\Outil;
use App\Models\ParametreUtilisateur;
use App\Models\Pays;
use App\Models\Pret;
use App\Models\Publication;
use App\Models\Quartier;
use App\Models\Reaction;
use App\Models\Rencontre;
use App\Models\User;
use App\Models\Ville;
use App\Support\Geohash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(GeoSeeder::class);
        $this->call(CategorySeeder::class);

        $pays = Pays::query()->where('code', 'CA')->firstOrFail();

        $admin = User::updateOrCreate(
            ['courriel' => config('admin.email')],
            [
                'nom' => config('admin.nom'),
                'prenom' => config('admin.prenom'),
                'mot_de_passe' => Hash::make(config('admin.password')),
                'role' => User::ROLE_MANAGER,
            ],
        );
        $this->localiser($admin, 'Montréal', 'Le Plateau-Mont-Royal');

        $utilisateurs = [
            ['nom' => 'Tremblay', 'prenom' => 'Alex', 'courriel' => 'alex.tremblay@localhub.test', 'ville' => 'Montréal', 'quartier' => 'Le Plateau-Mont-Royal'],
            ['nom' => 'Gagnon', 'prenom' => 'Sarah', 'courriel' => 'sarah.gagnon@localhub.test', 'ville' => 'Montréal', 'quartier' => 'Le Plateau-Mont-Royal'],
            ['nom' => 'Roy', 'prenom' => 'Thomas', 'courriel' => 'thomas.roy@localhub.test', 'ville' => 'Laval', 'quartier' => null],
            ['nom' => 'Bouchard', 'prenom' => 'Emma', 'courriel' => 'emma.bouchard@localhub.test', 'ville' => 'Montréal', 'quartier' => 'Verdun'],
            ['nom' => 'Côté', 'prenom' => 'Maxime', 'courriel' => 'maxime.cote@localhub.test', 'ville' => 'Québec', 'quartier' => 'La Cité-Limoilou'],
        ];

        $users = [];

        foreach ($utilisateurs as $data) {
            $user = User::updateOrCreate(
                ['courriel' => $data['courriel']],
                [
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'mot_de_passe' => Hash::make('password'),
                ],
            );

            $this->localiser($user, $data['ville'], $data['quartier']);
            $users[$data['prenom']] = $user;
        }

        foreach ($users as $user) {
            ParametreUtilisateur::updateOrCreate(
                ['id_utilisateur' => $user->id_utilisateur],
                [
                    'portee_defaut' => PorteeVisibilite::QUARTIER->value,
                    'afficher_position' => 'quartier',
                    'notifications_actives' => true,
                    'langue' => $user->locale?->value ?? 'fr',
                ],
            );
        }

        $categories = Category::query()->pluck('id_categorie', 'slug');

        $publications = [
            [
                'utilisateur' => $users['Sarah'],
                'titre' => 'Tondeuse à prêter pour le week-end',
                'description' => 'Ma tondeuse est disponible samedi et dimanche. Apportez votre propre essence.',
                'portee' => PorteeVisibilite::QUARTIER,
                'categorie' => 'outils',
            ],
            [
                'utilisateur' => $users['Alex'],
                'titre' => 'Looking for moving boxes',
                'description' => 'Anyone in the neighbourhood has spare cardboard boxes for a move next week?',
                'portee' => PorteeVisibilite::QUARTIER,
                'categorie' => 'autres',
            ],
            [
                'utilisateur' => $users['Emma'],
                'titre' => 'Vente de garage à Verdun',
                'description' => 'Samedi matin, plusieurs meubles et livres à petit prix.',
                'portee' => PorteeVisibilite::VILLE,
                'categorie' => 'meubles',
            ],
            [
                'utilisateur' => $users['Maxime'],
                'titre' => 'Cours de guitare pour débutants',
                'description' => 'Je propose des cours dans le quartier, première séance gratuite.',
                'portee' => PorteeVisibilite::VILLE,
                'categorie' => 'services',
            ],
            [
                'utilisateur' => $users['Sarah'],
                'titre' => 'Atelier de réparation de vélos ouvert à tous',
                'description' => 'Des bénévoles partagent leurs outils partout au pays pour réparer votre vélo ce printemps.',
                'portee' => PorteeVisibilite::PAYS,
                'categorie' => 'services',
            ],
            [
                'utilisateur' => $users['Alex'],
                'titre' => 'Coast-to-coast community garden guide',
                'description' => 'A free English guide to starting a community garden anywhere in the country.',
                'portee' => PorteeVisibilite::PAYS,
                'categorie' => 'autres',
            ],
        ];

        foreach ($publications as $data) {
            $publication = Publication::firstOrCreate(
                [
                    'id_utilisateur' => $data['utilisateur']->id_utilisateur,
                    'titre' => $data['titre'],
                ],
                [
                    'description' => $data['description'],
                    'id_categorie' => $categories[$data['categorie']] ?? null,
                    'id_quartier' => $data['utilisateur']->id_quartier,
                    'id_ville' => $data['utilisateur']->id_ville,
                    'id_pays' => $pays->id_pays,
                    'portee' => $data['portee'],
                    'statut' => 'active',
                    'date_creation' => now(),
                    'version' => 1,
                ],
            );

            if ($data['utilisateur']->is($users['Sarah'])) {
                Commentaire::firstOrCreate(
                    [
                        'id_publication' => $publication->id_publication,
                        'id_utilisateur' => $users['Alex']->id_utilisateur,
                    ],
                    [
                        'contenu' => 'Je suis intéressé, je peux passer samedi matin.',
                    ],
                );

                Reaction::firstOrCreate(
                    [
                        'id_publication' => $publication->id_publication,
                        'id_utilisateur' => $users['Alex']->id_utilisateur,
                        'type' => 'j_aime',
                    ],
                );
            }
        }

        $outils = [
            ['proprietaire' => $users['Sarah'], 'titre' => 'Perceuse sans fil', 'description' => 'Perceuse avec deux batteries et coffret.', 'categorie' => 'outils'],
            ['proprietaire' => $users['Alex'], 'titre' => 'Tondeuse à gazon', 'description' => 'Tondeuse manuelle en bon état.', 'categorie' => 'outils'],
            ['proprietaire' => $users['Emma'], 'titre' => 'Échelle en aluminium', 'description' => 'Échelle de 8 pieds, disponible pour la fin de semaine.', 'categorie' => 'outils'],
            ['proprietaire' => $users['Maxime'], 'titre' => 'Scie circulaire', 'description' => 'Scie circulaire avec lame neuve.', 'categorie' => 'outils'],
        ];

        foreach ($outils as $data) {
            Outil::firstOrCreate(
                [
                    'id_proprietaire' => $data['proprietaire']->id_utilisateur,
                    'titre' => $data['titre'],
                ],
                [
                    'description' => $data['description'],
                    'id_categorie' => $categories[$data['categorie']] ?? null,
                    'id_quartier' => $data['proprietaire']->id_quartier,
                    'id_ville' => $data['proprietaire']->id_ville,
                    'id_pays' => $pays->id_pays,
                    'portee' => PorteeVisibilite::QUARTIER,
                    'etat' => 'bon',
                    'disponible' => true,
                    'statut' => 'actif',
                    'version' => 1,
                ],
            );
        }

        $perceuse = Outil::query()->where('titre', 'Perceuse sans fil')->first();

        if ($perceuse !== null) {
            Pret::firstOrCreate(
                [
                    'id_outil' => $perceuse->id_outil,
                    'id_emprunteur' => $users['Alex']->id_utilisateur,
                ],
                [
                    'id_proprietaire' => $perceuse->id_proprietaire,
                    'statut' => 'demande',
                    'date_debut' => now()->addDay()->toDateString(),
                    'date_fin' => now()->addDays(4)->toDateString(),
                    'date_demande' => now(),
                    'message' => 'Est-ce que je peux l’emprunter samedi ?',
                    'version' => 1,
                    'cle_active' => 'outil:'.$perceuse->id_outil,
                ],
            );
        }

        Annonce::firstOrCreate(
            [
                'id_utilisateur' => $users['Thomas']->id_utilisateur,
                'titre' => 'Divan deux places à donner',
            ],
            [
                'description' => 'Divan en tissu gris, encore confortable. À venir chercher.',
                'id_categorie' => $categories['meubles'] ?? null,
                'id_quartier' => $users['Thomas']->id_quartier,
                'id_ville' => $users['Thomas']->id_ville,
                'id_pays' => $pays->id_pays,
                'portee' => PorteeVisibilite::VILLE,
                'type_annonce' => 'don',
                'prix' => 0,
                'statut' => 'active',
            ],
        );

        DemandeAide::firstOrCreate(
            [
                'id_utilisateur' => $users['Thomas']->id_utilisateur,
                'titre' => 'Coup de main pour un déménagement',
            ],
            [
                'description' => 'Besoin de deux personnes samedi matin pour porter des boîtes.',
                'id_categorie' => $categories['services'] ?? null,
                'id_quartier' => $users['Thomas']->id_quartier,
                'id_ville' => $users['Thomas']->id_ville,
                'id_pays' => $pays->id_pays,
                'portee' => PorteeVisibilite::VILLE,
                'urgent' => true,
                'statut' => 'ouverte',
            ],
        );

        Evenement::firstOrCreate(
            [
                'id_utilisateur' => $users['Emma']->id_utilisateur,
                'titre' => 'Fête de quartier à Verdun',
            ],
            [
                'description' => 'Rencontre conviviale au parc, apportez un plat à partager.',
                'id_categorie' => $categories['autres'] ?? null,
                'id_quartier' => $users['Emma']->id_quartier,
                'id_ville' => $users['Emma']->id_ville,
                'id_pays' => $pays->id_pays,
                'portee' => PorteeVisibilite::QUARTIER,
                'date_debut' => now()->addDays(10)->setTime(15, 0),
                'timezone_id' => 'America/Toronto',
                'lieu' => 'Parc de Verdun',
                'capacite' => 40,
                'statut' => 'actif',
            ],
        );

        Rencontre::firstOrCreate(
            [
                'id_utilisateur' => $users['Maxime']->id_utilisateur,
                'titre' => 'Club de lecture',
            ],
            [
                'description' => 'On se retrouve pour parler du livre du mois.',
                'id_categorie' => $categories['autres'] ?? null,
                'id_quartier' => $users['Maxime']->id_quartier,
                'id_ville' => $users['Maxime']->id_ville,
                'id_pays' => $pays->id_pays,
                'portee' => PorteeVisibilite::VILLE,
                'date_heure' => now()->addDays(7)->setTime(19, 0),
                'lieu' => 'Café du coin',
                'capacite' => 8,
                'interets' => ['lecture', 'discussion'],
                'statut' => 'ouverte',
            ],
        );

        $verdun = Quartier::query()->where('nom_fr', 'Verdun')->first();

        if ($verdun !== null) {
            // Petit polygone de démonstration (GeoJSON) ; les limites réelles
            // s'importent avec `quartiers:importer-polygones`.
            $verdun->forceFill([
                'polygon' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-73.5780, 45.4530],
                        [-73.5580, 45.4530],
                        [-73.5580, 45.4650],
                        [-73.5780, 45.4650],
                        [-73.5780, 45.4530],
                    ]],
                ],
            ])->save();
        }
    }

    private function localiser(User $utilisateur, string $nomVille, ?string $nomQuartier): void
    {
        $ville = Ville::query()->where('nom_fr', $nomVille)->first();

        if ($ville === null) {
            return;
        }

        $quartier = $nomQuartier !== null
            ? Quartier::query()
                ->where('id_ville', $ville->id_ville)
                ->where('nom_fr', $nomQuartier)
                ->first()
            : null;

        $latitude = $quartier?->centroid_lat ?? $ville->latitude;
        $longitude = $quartier?->centroid_lng ?? $ville->longitude;

        $utilisateur->forceFill([
            'id_ville' => $ville->id_ville,
            'id_quartier' => $quartier?->id_quartier,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'geohash' => ($latitude !== null && $longitude !== null)
                ? Geohash::encode((float) $latitude, (float) $longitude, 7)
                : null,
        ])->save();
    }
}
