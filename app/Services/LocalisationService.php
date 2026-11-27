<?php

namespace App\Services;

use App\Models\Quartier;
use App\Models\Ville;
use App\Support\Geohash;

/**
 * Résolution d'un point (latitude, longitude) vers la ville et le quartier
 * les plus proches, et calcul du geohash associé.
 *
 * À cette échelle (quelques centaines de villes), le calcul en PHP est
 * largement suffisant. Pour un passage à l'échelle mondiale, on remplacera
 * la recherche linéaire par un pré-filtre par geohash / index spatial MySQL.
 */
class LocalisationService
{
    /**
     * @return array{latitude: float, longitude: float, geohash: string, ville: ?Ville, quartier: ?Quartier}
     */
    public function resoudre(
        float $latitude,
        float $longitude,
        float $rayonVilleKm = 50.0,
        float $rayonQuartierKm = 15.0,
    ): array {
        $ville = $this->villeLaPlusProche($latitude, $longitude, $rayonVilleKm);
        $quartier = $ville !== null
            ? $this->quartierLePlusProche($ville, $latitude, $longitude, $rayonQuartierKm)
            : null;

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'geohash' => Geohash::encode($latitude, $longitude, 7),
            'ville' => $ville,
            'quartier' => $quartier,
        ];
    }

    public function villeLaPlusProche(float $latitude, float $longitude, float $rayonKm = 50.0): ?Ville
    {
        $villes = Ville::query()
            ->where('actif', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $plusProche = null;
        $distanceMin = INF;

        foreach ($villes as $ville) {
            $distance = $this->distanceKm(
                $latitude,
                $longitude,
                (float) $ville->latitude,
                (float) $ville->longitude,
            );

            if ($distance < $distanceMin) {
                $distanceMin = $distance;
                $plusProche = $ville;
            }
        }

        return $plusProche !== null && $distanceMin <= $rayonKm ? $plusProche : null;
    }

    public function quartierLePlusProche(
        Ville $ville,
        float $latitude,
        float $longitude,
        float $rayonKm = 15.0,
    ): ?Quartier {
        $quartiers = Quartier::query()
            ->where('id_ville', $ville->id_ville)
            ->where('actif', true)
            ->whereNotNull('centroid_lat')
            ->whereNotNull('centroid_lng')
            ->get();

        $plusProche = null;
        $distanceMin = INF;

        foreach ($quartiers as $quartier) {
            $distance = $this->distanceKm(
                $latitude,
                $longitude,
                (float) $quartier->centroid_lat,
                (float) $quartier->centroid_lng,
            );

            if ($distance < $distanceMin) {
                $distanceMin = $distance;
                $plusProche = $quartier;
            }
        }

        return $plusProche !== null && $distanceMin <= $rayonKm ? $plusProche : null;
    }

    public function villeParNom(string $nom): ?Ville
    {
        return Ville::query()
            ->where('nom_fr', $nom)
            ->orWhere('nom_en', $nom)
            ->first();
    }

    /**
     * Distance haversine en kilomètres.
     */
    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $rayonTerre = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $rayonTerre * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
