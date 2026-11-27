<?php

namespace Tests\Feature;

use App\Models\Pays;
use App\Models\Quartier;
use App\Models\Region;
use App\Models\Ville;
use App\Services\LocalisationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalisationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resout_la_ville_la_plus_proche(): void
    {
        $this->villeMontreal();

        $resultat = app(LocalisationService::class)->resoudre(45.5202, -73.5800);

        $this->assertNotNull($resultat['ville']);
        $this->assertSame('Montréal', $resultat['ville']->nom_fr);
        $this->assertSame(7, strlen($resultat['geohash']));
    }

    public function test_retourne_null_hors_du_rayon(): void
    {
        $this->villeMontreal();

        $resultat = app(LocalisationService::class)->resoudre(0.0, 0.0, 50.0);

        $this->assertNull($resultat['ville']);
        $this->assertNull($resultat['quartier']);
    }

    public function test_associe_le_quartier_le_plus_proche(): void
    {
        $ville = $this->villeMontreal();

        Quartier::create([
            'id_ville' => $ville->id_ville,
            'nom_fr' => 'Le Plateau-Mont-Royal',
            'nom_en' => 'Le Plateau-Mont-Royal',
            'centroid_lat' => 45.5202,
            'centroid_lng' => -73.5800,
            'geohash' => 'f25d',
            'actif' => true,
        ]);

        $resultat = app(LocalisationService::class)->resoudre(45.5202, -73.5800);

        $this->assertNotNull($resultat['quartier']);
        $this->assertSame('Le Plateau-Mont-Royal', $resultat['quartier']->nom_fr);
    }

    private function villeMontreal(): Ville
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

        return Ville::create([
            'id_region' => $region->id_region,
            'nom_fr' => 'Montréal',
            'nom_en' => 'Montreal',
            'latitude' => 45.5019,
            'longitude' => -73.5674,
            'actif' => true,
        ]);
    }
}
