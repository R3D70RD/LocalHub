<?php

namespace Tests\Concerns;

use App\Models\Pays;
use App\Models\Quartier;
use App\Models\Region;
use App\Models\User;
use App\Models\Ville;
use App\Support\Geohash;

trait CreeContexteGeo
{
    /**
     * @return array{pays: Pays, region: Region, montreal: Ville, plateau: Quartier, verdun: Quartier, laval: Ville}
     */
    protected function creerContexteGeo(): array
    {
        $pays = Pays::create([
            'code' => 'CA',
            'nom_fr' => 'Canada',
            'nom_en' => 'Canada',
            'devise' => 'CAD',
            'actif' => true,
        ]);

        $region = Region::create([
            'id_pays' => $pays->id_pays,
            'code' => 'QC',
            'nom_fr' => 'Québec',
            'nom_en' => 'Quebec',
            'actif' => true,
        ]);

        $montreal = Ville::create([
            'id_region' => $region->id_region,
            'nom_fr' => 'Montréal',
            'nom_en' => 'Montreal',
            'latitude' => 45.5019,
            'longitude' => -73.5674,
            'actif' => true,
        ]);

        $laval = Ville::create([
            'id_region' => $region->id_region,
            'nom_fr' => 'Laval',
            'nom_en' => 'Laval',
            'latitude' => 45.6066,
            'longitude' => -73.7124,
            'actif' => true,
        ]);

        $plateau = Quartier::create([
            'id_ville' => $montreal->id_ville,
            'nom_fr' => 'Le Plateau-Mont-Royal',
            'nom_en' => 'Le Plateau-Mont-Royal',
            'centroid_lat' => 45.5202,
            'centroid_lng' => -73.5800,
            'geohash' => Geohash::encode(45.5202, -73.5800, 7),
            'actif' => true,
        ]);

        $verdun = Quartier::create([
            'id_ville' => $montreal->id_ville,
            'nom_fr' => 'Verdun',
            'nom_en' => 'Verdun',
            'centroid_lat' => 45.4590,
            'centroid_lng' => -73.5680,
            'geohash' => Geohash::encode(45.4590, -73.5680, 7),
            'actif' => true,
        ]);

        return compact('pays', 'region', 'montreal', 'laval', 'plateau', 'verdun');
    }

    protected function utilisateurGeo(
        string $nom,
        Ville $ville,
        ?Quartier $quartier = null,
    ): User {
        $latitude = $quartier?->centroid_lat ?? $ville->latitude;
        $longitude = $quartier?->centroid_lng ?? $ville->longitude;

        return User::factory()->create([
            'nom' => $nom,
            'id_ville' => $ville->id_ville,
            'id_quartier' => $quartier?->id_quartier,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'geohash' => ($latitude !== null && $longitude !== null)
                ? Geohash::encode((float) $latitude, (float) $longitude, 7)
                : null,
        ]);
    }
}
