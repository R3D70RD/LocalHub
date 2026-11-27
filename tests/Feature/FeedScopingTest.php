<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Services\FeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class FeedScopingTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_le_fil_respecte_la_portee_quartier_ville_pays(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $emma = $this->utilisateurGeo('Bouchard', $geo['montreal'], $geo['verdun']);
        $thomas = $this->utilisateurGeo('Roy', $geo['laval']);

        $this->publication($sarah, 'Quartier', 'quartier', 'fr');
        $this->publication($sarah, 'Ville', 'ville', 'fr');
        $this->publication($sarah, 'Pays', 'pays', 'fr');

        $feed = app(FeedService::class);

        $this->assertSame(
            ['Quartier'],
            $this->titres($feed->fil($alex, ['scope' => 'quartier'])),
        );

        $this->assertSame(
            ['Quartier', 'Ville'],
            $this->titres($feed->fil($alex, ['scope' => 'ville'])),
        );

        $this->assertSame(
            ['Pays', 'Quartier', 'Ville'],
            $this->titres($feed->fil($alex, ['scope' => 'pays'])),
        );

        // Même ville, autre quartier : la publication de quartier est invisible.
        $this->assertSame(
            ['Ville'],
            $this->titres($feed->fil($emma, ['scope' => 'ville'])),
        );

        // Autre ville : seule la portée pays est visible.
        $this->assertSame(
            ['Pays'],
            $this->titres($feed->fil($thomas, ['scope' => 'pays'])),
        );
    }

    public function test_le_filtre_de_langue_est_applique(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->publication($sarah, 'Bonjour', 'quartier', 'fr');
        $this->publication($sarah, 'Hello', 'quartier', 'en');

        $feed = app(FeedService::class);

        $this->assertSame(
            ['Hello'],
            $this->titres($feed->fil($alex, ['scope' => 'quartier', 'lang' => 'en'])),
        );

        $this->assertCount(
            2,
            $this->titres($feed->fil($alex, ['scope' => 'quartier'])),
        );
    }

    public function test_un_utilisateur_sans_quartier_voit_sa_ville_par_defaut(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $sansQuartier = $this->utilisateurGeo('Roy', $geo['montreal']);

        $this->publication($sarah, 'Quartier', 'quartier', 'fr');
        $this->publication($sarah, 'Ville', 'ville', 'fr');
        $this->publication($sarah, 'Pays', 'pays', 'fr');

        $feed = app(FeedService::class);

        $this->assertSame(['Ville'], $this->titres($feed->fil($sansQuartier)));
        $this->assertSame(
            ['Pays', 'Ville'],
            $this->titres($feed->fil($sansQuartier, ['scope' => 'pays'])),
        );
    }

    public function test_un_visiteur_ne_voit_que_les_publications_pays(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);

        $quartier = $this->publication($sarah, 'Quartier', 'quartier', 'fr');
        $pays = $this->publication($sarah, 'Pays', 'pays', 'fr');

        $feed = app(FeedService::class);

        $this->assertSame(
            ['Pays'],
            $this->titres($feed->fil(null)),
        );

        $this->assertFalse($feed->peutVoir(null, $quartier));
        $this->assertTrue($feed->peutVoir(null, $pays));
    }

    private function publication($auteur, string $titre, string $portee, string $lang): Publication
    {
        return Publication::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville->region->pays->id_pays,
            'titre' => $titre,
            'description' => 'Description de '.$titre,
            'lang' => $lang,
            'portee' => $portee,
            'statut' => 'active',
            'date_creation' => now(),
            'version' => 1,
        ]);
    }

    private function titres($paginateur): array
    {
        $titres = $paginateur->getCollection()->pluck('titre')->all();
        sort($titres);

        return $titres;
    }
}
