<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\DemandeAide;
use App\Models\Evenement;
use App\Models\Outil;
use App\Models\Publication;
use App\Models\Rencontre;
use App\Models\User;
use App\Services\AnnonceService;
use App\Services\FeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class Phase2FeaturesTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_les_pages_web_des_nouvelles_entites_s_affichent(): void
    {
        $geo = $this->creerContexteGeo();
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->actingAs($alex);

        foreach (['home', 'demandes-aide.index', 'annonces.index', 'evenements.index', 'rencontres.index'] as $route) {
            $this->assertSame(200, $this->get(route($route))->status(), $route);
        }

        foreach (['demandes-aide.create', 'annonces.create', 'evenements.create', 'rencontres.create'] as $route) {
            $this->assertSame(200, $this->get(route($route))->status(), $route);
        }
    }

    public function test_une_demande_d_aide_peut_etre_creee_et_recevoir_une_offre(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->actingAs($sarah)->post(route('demandes-aide.store'), [
            'titre' => 'Besoin d’un coup de main',
            'description' => 'Aider à déménager',
            'urgent' => 1,
            'portee' => 'quartier',
        ])->assertRedirect();

        $demande = DemandeAide::firstOrFail();
        $this->assertTrue((bool) $demande->urgent);
        $this->assertSame($sarah->id_utilisateur, $demande->id_utilisateur);

        $this->actingAs($alex)->post(route('demandes-aide.offres.store', $demande), [
            'description' => 'Je peux aider samedi.',
        ])->assertRedirect();

        $this->assertDatabaseHas('offre_aide', [
            'id_demande_aide' => $demande->id_demande_aide,
            'id_utilisateur' => $alex->id_utilisateur,
        ]);

        $this->actingAs($alex)
            ->get(route('demandes-aide.show', $demande))
            ->assertOk()
            ->assertSee('Je peux aider samedi.');
    }

    public function test_une_annonce_a_prix_zero_est_gratuite_et_accepte_offre_et_message(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->actingAs($sarah)->post(route('annonces.store'), [
            'titre' => 'Divan à donner',
            'description' => 'Divan en bon état.',
            'prix' => 0,
            'type_annonce' => 'vente',
            'portee' => 'quartier',
        ])->assertRedirect();

        $annonce = Annonce::firstOrFail();
        $this->assertTrue($annonce->estGratuite());
        $this->assertSame('don', $annonce->type_annonce);

        $this->actingAs($alex)->post(route('annonces.offres.store', $annonce), [
            'montant' => 0,
            'message' => 'Je le prends.',
        ])->assertRedirect();

        $this->actingAs($alex)->post(route('annonces.messages.store', $annonce), [
            'contenu' => 'Toujours disponible ?',
        ])->assertRedirect();

        $this->assertDatabaseHas('offre_annonce', ['id_annonce' => $annonce->id_annonce]);
        $this->assertDatabaseHas('message', [
            'id_annonce' => $annonce->id_annonce,
            'contenu' => 'Toujours disponible ?',
        ]);

        $this->actingAs($alex)
            ->get(route('annonces.show', $annonce))
            ->assertOk()
            ->assertSee('Toujours disponible ?');
    }

    public function test_un_evenement_est_stocke_en_utc_selon_le_fuseau_et_limite_les_inscriptions(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $emma = $this->utilisateurGeo('Bouchard', $geo['montreal'], $geo['verdun']);

        // Heure d'été (EDT, UTC-4) : 18:00 locale = 22:00 UTC.
        $this->actingAs($sarah)->post(route('evenements.store'), [
            'titre' => 'Fête d’été',
            'description' => 'Au parc',
            'date_debut' => '2026-07-01T18:00',
            'date_fin' => '2026-07-01T20:00',
            'timezone_id' => 'America/Toronto',
            'capacite' => 1,
            'portee' => 'quartier',
        ])->assertRedirect();

        $ete = Evenement::query()->where('titre', 'Fête d’été')->firstOrFail();
        $this->assertSame('2026-07-01 22:00:00', $ete->date_debut->copy()->utc()->format('Y-m-d H:i:s'));

        // Heure d'hiver (EST, UTC-5) : 18:00 locale = 23:00 UTC.
        $this->actingAs($sarah)->post(route('evenements.store'), [
            'titre' => 'Fête d’hiver',
            'description' => 'Au chaud',
            'date_debut' => '2026-01-15T18:00',
            'timezone_id' => 'America/Toronto',
            'portee' => 'quartier',
        ])->assertRedirect();

        $hiver = Evenement::query()->where('titre', 'Fête d’hiver')->firstOrFail();
        $this->assertSame('2026-01-15 23:00:00', $hiver->date_debut->copy()->utc()->format('Y-m-d H:i:s'));

        $this->actingAs($alex)->post(route('evenements.inscriptions.store', $ete))->assertRedirect();
        $this->assertDatabaseHas('inscription_evenement', [
            'id_evenement' => $ete->id_evenement,
            'id_utilisateur' => $alex->id_utilisateur,
            'statut' => 'inscrit',
        ]);

        $this->actingAs($alex)->get(route('evenements.show', $ete))->assertOk()->assertSee('Fête d’été');

        // Capacité atteinte : la deuxième inscription est refusée.
        $this->actingAs($emma)
            ->post(route('evenements.inscriptions.store', $ete))
            ->assertSessionHasErrors('capacite');

        $this->assertDatabaseMissing('inscription_evenement', [
            'id_evenement' => $ete->id_evenement,
            'id_utilisateur' => $emma->id_utilisateur,
        ]);
    }

    public function test_une_rencontre_gere_les_interets_et_la_capacite(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $emma = $this->utilisateurGeo('Bouchard', $geo['montreal'], $geo['verdun']);

        $this->actingAs($sarah)->post(route('rencontres.store'), [
            'titre' => 'Café vélo',
            'description' => 'Discussion autour du vélo.',
            'interets' => 'vélo, lecture, réparation',
            'capacite' => 1,
            'portee' => 'quartier',
        ])->assertRedirect();

        $rencontre = Rencontre::firstOrFail();
        $this->assertSame(['vélo', 'lecture', 'réparation'], $rencontre->interets);

        $this->actingAs($alex)->post(route('rencontres.membres.store', $rencontre))->assertRedirect();
        $this->assertDatabaseHas('rencontre_membre', [
            'id_rencontre' => $rencontre->id_rencontre,
            'id_utilisateur' => $alex->id_utilisateur,
            'statut' => 'inscrit',
        ]);

        $this->actingAs($alex)->get(route('rencontres.show', $rencontre))->assertOk()->assertSee('Café vélo');

        $this->actingAs($emma)
            ->post(route('rencontres.membres.store', $rencontre))
            ->assertSessionHasErrors('capacite');
    }

    public function test_le_fil_unifie_agrege_les_types_et_respecte_la_portee(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->creerContenuQuartier($sarah);

        $fil = app(FeedService::class)->filUnifie($alex, ['scope' => 'quartier']);
        $types = collect($fil->items())->pluck('type')->all();

        $this->assertContains('publication', $types);
        $this->assertContains('outil', $types);
        $this->assertContains('demande_aide', $types);
        $this->assertContains('annonce', $types);
    }

    public function test_l_api_cree_et_liste_une_annonce_et_expose_le_fil(): void
    {
        $geo = $this->creerContexteGeo();
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        Sanctum::actingAs($alex);

        $this->postJson('/api/v1/annonces', [
            'titre' => 'Vélo à vendre',
            'description' => 'Vélo de ville.',
            'prix' => 50,
            'portee' => 'quartier',
        ])->assertCreated()
            ->assertJsonPath('data.titre', 'Vélo à vendre')
            ->assertJsonPath('data.gratuite', false);

        $this->getJson('/api/v1/annonces?scope=quartier')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/fil?scope=quartier')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'annonce');

        $this->assertDatabaseHas('annonce', [
            'titre' => 'Vélo à vendre',
            'id_utilisateur' => $alex->id_utilisateur,
        ]);
    }

    public function test_la_recherche_filtre_le_texte_de_l_auteur(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->creerAnnonce($sarah, 'Vélo rouge', 'Un vélo de ville.');
        $this->creerAnnonce($sarah, 'Divan gris', 'Un divan confortable.');

        $resultats = app(AnnonceService::class)->liste($alex, [
            'scope' => 'quartier',
            'q' => 'Vélo',
        ]);

        $this->assertSame(['Vélo rouge'], $resultats->getCollection()->pluck('titre')->all());
    }

    private function creerContenuQuartier(User $auteur): void
    {
        Publication::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville->region->pays->id_pays,
            'titre' => 'Une publication',
            'description' => 'Contenu',
            'portee' => 'quartier',
            'statut' => 'active',
            'date_creation' => now(),
            'version' => 1,
        ]);

        Outil::create([
            'id_proprietaire' => $auteur->id_utilisateur,
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville->region->pays->id_pays,
            'titre' => 'Un outil',
            'description' => 'Contenu',
            'portee' => 'quartier',
            'etat' => 'bon',
            'disponible' => true,
            'statut' => 'actif',
            'version' => 1,
        ]);

        DemandeAide::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville->region->pays->id_pays,
            'titre' => 'Une demande',
            'description' => 'Contenu',
            'portee' => 'quartier',
            'statut' => 'ouverte',
        ]);

        $this->creerAnnonce($auteur, 'Une annonce', 'Contenu');
    }

    private function creerAnnonce(User $auteur, string $titre, string $description): Annonce
    {
        return Annonce::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville->region->pays->id_pays,
            'titre' => $titre,
            'description' => $description,
            'portee' => 'quartier',
            'type_annonce' => 'vente',
            'statut' => 'active',
        ]);
    }
}
