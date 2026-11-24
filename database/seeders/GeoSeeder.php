<?php

namespace Database\Seeders;

use App\Models\Pays;
use App\Models\Quartier;
use App\Models\Region;
use App\Models\Ville;
use App\Support\Geohash;
use Illuminate\Database\Seeder;

/**
 * Semence la hiérarchie géographique de lancement : Canada -> Québec
 * (plus quelques régions/villes pour préparer l'expansion canadienne).
 *
 * Idempotent : peut être relancé sans dupliquer les lignes.
 */
class GeoSeeder extends Seeder
{
    public function run(): void
    {
        $canada = Pays::updateOrCreate(
            ['code' => 'CA'],
            [
                'nom_fr' => 'Canada',
                'nom_en' => 'Canada',
                'devise' => 'CAD',
                'indicatif_tel' => '+1',
                'actif' => true,
            ],
        );

        $regions = [
            'QC' => ['Québec', 'Quebec'],
            'ON' => ['Ontario', 'Ontario'],
            'BC' => ['Colombie-Britannique', 'British Columbia'],
            'AB' => ['Alberta', 'Alberta'],
        ];

        $regionParCode = [];

        foreach ($regions as $code => [$nomFr, $nomEn]) {
            $regionParCode[$code] = Region::updateOrCreate(
                ['id_pays' => $canada->id_pays, 'code' => $code],
                ['nom_fr' => $nomFr, 'nom_en' => $nomEn, 'actif' => true],
            );
        }

        // [code région, nom_fr, nom_en, latitude, longitude, préfixe postal]
        $villes = [
            ['QC', 'Montréal', 'Montreal', 45.501900, -73.567400, 'H'],
            ['QC', 'Québec', 'Quebec City', 46.813900, -71.208000, 'G'],
            ['QC', 'Laval', 'Laval', 45.606600, -73.712400, 'H'],
            ['QC', 'Longueuil', 'Longueuil', 45.531200, -73.518100, 'J'],
            ['QC', 'Gatineau', 'Gatineau', 45.476500, -75.701300, 'J'],
            ['QC', 'Sherbrooke', 'Sherbrooke', 45.404200, -71.892900, 'J'],
            ['QC', 'Trois-Rivières', 'Trois-Rivieres', 46.343200, -72.547700, 'G'],
            ['QC', 'Saguenay', 'Saguenay', 48.428100, -71.068500, 'G'],
            ['QC', 'Lévis', 'Levis', 46.803300, -71.178000, 'G'],
            ['QC', 'Terrebonne', 'Terrebonne', 45.700000, -73.633300, 'J'],
            ['ON', 'Toronto', 'Toronto', 43.653200, -79.383200, 'M'],
            ['ON', 'Ottawa', 'Ottawa', 45.421500, -75.697200, 'K'],
            ['BC', 'Vancouver', 'Vancouver', 49.282700, -123.120700, 'V'],
            ['AB', 'Calgary', 'Calgary', 51.044700, -114.071900, 'T'],
        ];

        $villeParNom = [];

        foreach ($villes as [$codeRegion, $nomFr, $nomEn, $latitude, $longitude, $prefixe]) {
            $villeParNom[$nomFr] = Ville::updateOrCreate(
                ['id_region' => $regionParCode[$codeRegion]->id_region, 'nom_fr' => $nomFr],
                [
                    'nom_en' => $nomEn,
                    'prefixe_postal' => $prefixe,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'actif' => true,
                ],
            );
        }

        // [nom_fr, nom_en, latitude du centroïde, longitude du centroïde]
        $quartiers = [
            'Montréal' => [
                ['Le Plateau-Mont-Royal', 'Le Plateau-Mont-Royal', 45.520200, -73.580000],
                ['Rosemont–La Petite-Patrie', 'Rosemont–La Petite-Patrie', 45.550000, -73.583300],
                ['Villeray–Saint-Michel–Parc-Extension', 'Villeray–Saint-Michel–Parc-Extension', 45.550000, -73.616700],
                ['Verdun', 'Verdun', 45.459000, -73.568000],
                ['Le Sud-Ouest', 'Le Sud-Ouest', 45.483300, -73.583300],
                ['Ahuntsic-Cartierville', 'Ahuntsic-Cartierville', 45.550000, -73.666700],
            ],
            'Québec' => [
                ['La Cité-Limoilou', 'La Cité-Limoilou', 46.816700, -71.216700],
                ['Beauport', 'Beauport', 46.866700, -71.183300],
                ['Charlesbourg', 'Charlesbourg', 46.866700, -71.266700],
                ['Sainte-Foy–Sillery–Cap-Rouge', 'Sainte-Foy–Sillery–Cap-Rouge', 46.783300, -71.283300],
                ['Les Rivières', 'Les Rivières', 46.800000, -71.283300],
                ['La Haute-Saint-Charles', 'La Haute-Saint-Charles', 46.883300, -71.316700],
            ],
        ];

        foreach ($quartiers as $nomVille => $liste) {
            $ville = $villeParNom[$nomVille] ?? null;

            if ($ville === null) {
                continue;
            }

            foreach ($liste as [$nomFr, $nomEn, $latitude, $longitude]) {
                Quartier::updateOrCreate(
                    ['id_ville' => $ville->id_ville, 'nom_fr' => $nomFr],
                    [
                        'nom_en' => $nomEn,
                        'centroid_lat' => $latitude,
                        'centroid_lng' => $longitude,
                        'geohash' => Geohash::encode($latitude, $longitude, 7),
                        'actif' => true,
                    ],
                );
            }
        }
    }
}
