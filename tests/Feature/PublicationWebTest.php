<?php

namespace Tests\Feature;

use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class PublicationWebTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_le_fil_web_respecte_la_portee(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $emma = $this->utilisateurGeo('Bouchard', $geo['montreal'], $geo['verdun']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->publication($sarah, 'Mon quartier', 'quartier');
        $this->publication($emma, 'Verdun', 'quartier');
        $this->publication($sarah, 'Toute la ville', 'ville');

        $this->actingAs($alex)
            ->get('/publications')
            ->assertOk()
            ->assertSee('Mon quartier')
            ->assertDontSee('Verdun');

        $this->actingAs($alex)
            ->get('/publications?scope=ville')
            ->assertOk()
            ->assertSee('Mon quartier')
            ->assertSee('Toute la ville')
            ->assertDontSee('Verdun');
    }

    public function test_une_publication_affiche_ses_commentaires(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $publication = $this->publication($sarah, 'Perceuse', 'quartier');

        $this->actingAs($alex)
            ->post(route('publications.commentaires.store', $publication), [
                'contenu' => 'Je peux passer samedi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('commentaire', [
            'id_publication' => $publication->id_publication,
            'id_utilisateur' => $alex->id_utilisateur,
        ]);

        $this->actingAs($alex)
            ->get(route('publications.show', $publication))
            ->assertOk()
            ->assertSee('Je peux passer samedi.');
    }

    public function test_une_reaction_est_basculee(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $publication = $this->publication($sarah, 'Perceuse', 'quartier');

        $this->actingAs($alex)
            ->post(route('publications.reactions.toggle', $publication), ['type' => 'j_aime'])
            ->assertRedirect();

        $this->assertDatabaseCount('reaction', 1);

        $this->actingAs($alex)
            ->post(route('publications.reactions.toggle', $publication), ['type' => 'j_aime'])
            ->assertRedirect();

        $this->assertDatabaseCount('reaction', 0);
    }

    public function test_un_utilisateur_ne_peut_pas_modifier_la_publication_d_un_autre(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $publication = $this->publication($sarah, 'Perceuse', 'quartier');

        $this->actingAs($alex)
            ->get(route('publications.edit', $publication))
            ->assertForbidden();
    }

    public function test_un_visiteur_ne_voit_que_les_publications_pays_et_pas_les_pages_locales(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);

        $quartier = $this->publication($sarah, 'Publication de quartier', 'quartier');
        $ville = $this->publication($sarah, 'Publication de ville', 'ville');
        $pays = $this->publication($sarah, 'Publication de pays', 'pays');

        $this->get('/')
            ->assertOk()
            ->assertSee('Publication de pays')
            ->assertDontSee('Publication de quartier')
            ->assertDontSee('Publication de ville');

        $this->get(route('publications.show', $pays))->assertOk();

        $this->get(route('publications.show', $quartier))->assertNotFound();
        $this->get(route('publications.show', $ville))->assertNotFound();
    }

    public function test_la_recherche_publique_ne_montre_que_les_publications_pays(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);

        $this->publication($sarah, 'Recherche quartier', 'quartier');
        $this->publication($sarah, 'Recherche ville', 'ville');
        $this->publication($sarah, 'Recherche pays', 'pays');

        $this->getJson(route('recherche.publications', ['q' => 'Recherche']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.label', 'Recherche pays');
    }

    public function test_le_foyer_d_un_visiteur_est_alimente_par_les_publications_pays_du_seed(): void
    {
        $this->seed();

        $this->assertDatabaseHas('publication', [
            'portee' => 'pays',
            'statut' => 'active',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Atelier de réparation de vélos ouvert à tous');
    }

    private function publication($auteur, string $titre, string $portee): Publication
    {
        return Publication::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville->region->pays->id_pays,
            'titre' => $titre,
            'description' => 'Description',
            'portee' => $portee,
            'statut' => 'active',
            'date_creation' => now(),
            'version' => 1,
        ]);
    }
}
