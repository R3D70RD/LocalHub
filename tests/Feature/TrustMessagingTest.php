<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Outil;
use App\Models\Pret;
use App\Models\Publication;
use App\Models\Quartier;
use App\Models\Signalement;
use App\Models\User;
use App\Services\FeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class TrustMessagingTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_un_signalement_est_traite_par_un_moderateur(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $moderateur = User::factory()->create(['role' => User::ROLE_MODERATEUR]);

        $publication = $this->publication($sarah, 'Publication signalée');

        $this->actingAs($alex)->post(route('signalements.store'), [
            'cible_type' => 'publication',
            'cible_id' => $publication->id_publication,
            'motif' => 'spam',
            'description' => 'Contenu indésirable',
        ])->assertRedirect();

        $signalement = Signalement::firstOrFail();
        $this->assertSame('en_attente', $signalement->statut);

        $this->actingAs($alex)->get(route('moderation.index'))->assertForbidden();
        $this->actingAs($moderateur)->get(route('moderation.index'))->assertOk()->assertSee('Contenu indésirable');

        $this->actingAs($moderateur)->post(route('moderation.traiter', $signalement), [
            'action' => 'masquer',
            'note' => 'Masqué',
        ])->assertRedirect();

        $this->assertSame('traite', $signalement->fresh()->statut);
        $this->assertSame('inactif', $publication->fresh()->statut);
        $this->assertDatabaseHas('moderation_action', [
            'id_signalement' => $signalement->id_signalement,
            'id_moderateur' => $moderateur->id_utilisateur,
            'action' => 'masquer',
        ]);
    }

    public function test_un_avis_de_pret_met_a_jour_la_fiabilite(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $outil = $this->outil($sarah);
        $pret = Pret::create([
            'id_outil' => $outil->id_outil,
            'id_emprunteur' => $alex->id_utilisateur,
            'id_proprietaire' => $sarah->id_utilisateur,
            'statut' => 'retourne',
            'date_debut' => now()->subDays(5)->toDateString(),
            'date_fin' => now()->subDays(2)->toDateString(),
            'date_demande' => now()->subDays(6),
            'version' => 1,
        ]);

        $this->actingAs($alex)->post(route('prets.avis.store', $pret), [
            'note' => 2,
            'commentaire' => 'Outil en mauvais état.',
        ])->assertRedirect();

        $this->assertDatabaseHas('avis_pret', [
            'id_pret' => $pret->id_pret,
            'id_auteur' => $alex->id_utilisateur,
            'id_cible' => $sarah->id_utilisateur,
            'note' => 2,
        ]);

        // 100 - (5 - 2) * 15 = 55
        $this->assertSame(55, $sarah->fresh()->fiabilite);
    }

    public function test_un_utilisateur_bloque_ne_voit_plus_le_contenu_du_bloqueur(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->publication($sarah, 'Publication de Sarah');
        $this->publication($alex, 'Publication d’Alex');

        $this->actingAs($alex)->post(route('blocages.store', $sarah))->assertRedirect();

        $feed = app(FeedService::class);
        $titres = $feed->fil($alex, ['scope' => 'quartier'])->getCollection()->pluck('titre')->all();

        $this->assertNotContains('Publication de Sarah', $titres);
        $this->assertContains('Publication d’Alex', $titres);

        $unifie = $feed->filUnifie($alex, ['scope' => 'quartier'])->items();
        $titresUnifies = collect($unifie)->pluck('titre')->all();
        $this->assertNotContains('Publication de Sarah', $titresUnifies);
    }

    public function test_deux_utilisateurs_peuvent_discuter_en_prive(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->actingAs($alex)
            ->post(route('conversations.demarrer'), ['destinataire' => $sarah->id_utilisateur])
            ->assertRedirect();

        $conversation = Conversation::firstOrFail();
        $this->assertSame(2, $conversation->membres()->count());

        $this->actingAs($alex)->post(route('conversations.messages.store', $conversation), [
            'contenu' => 'Bonjour Sarah',
        ])->assertRedirect();

        $this->assertDatabaseHas('message', [
            'id_conversation' => $conversation->id_conversation,
            'id_expediteur' => $alex->id_utilisateur,
            'contenu' => 'Bonjour Sarah',
        ]);

        $this->assertDatabaseHas('notification', [
            'notifiable_id' => $sarah->id_utilisateur,
            'type' => 'message.nouveau',
        ]);

        $this->actingAs($sarah)
            ->get(route('conversations.show', $conversation))
            ->assertOk()
            ->assertSee('Bonjour Sarah');
    }

    public function test_un_suiveur_est_notifie_d_un_nouveau_commentaire(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $publication = $this->publication($sarah, 'À suivre');

        $this->actingAs($alex)->post(route('publications.suivre', $publication))->assertRedirect();
        $this->assertDatabaseHas('suivi', [
            'id_utilisateur' => $alex->id_utilisateur,
            'cible_type' => 'publication',
            'cible_id' => $publication->id_publication,
        ]);

        $this->actingAs($sarah)->post(route('publications.commentaires.store', $publication), [
            'contenu' => 'Nouveau commentaire',
        ])->assertRedirect();

        $this->assertDatabaseHas('notification', [
            'notifiable_id' => $alex->id_utilisateur,
            'type' => 'publication.commentaire',
        ]);
    }

    public function test_la_commande_importe_les_polygones_geojson(): void
    {
        $this->creerContexteGeo();

        $fichier = tempnam(sys_get_temp_dir(), 'quartiers').'.geojson';

        file_put_contents($fichier, json_encode([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['nom_fr' => 'Verdun'],
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-73.5780, 45.4530],
                        [-73.5580, 45.4530],
                        [-73.5580, 45.4650],
                        [-73.5780, 45.4650],
                        [-73.5780, 45.4530],
                    ]],
                ],
            ]],
        ]));

        $this->artisan('quartiers:importer-polygones', ['fichier' => $fichier])->assertSuccessful();

        $verdun = Quartier::query()->where('nom_fr', 'Verdun')->firstOrFail();
        $this->assertSame('Polygon', $verdun->polygon['type']);

        @unlink($fichier);
    }

    public function test_les_pages_de_messagerie_et_de_compte_s_affichent(): void
    {
        $geo = $this->creerContexteGeo();
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->actingAs($alex)->get(route('conversations.index'))->assertOk();
        $this->actingAs($alex)->get(route('account.show'))->assertOk();
    }

    private function publication(User $auteur, string $titre): Publication
    {
        return Publication::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville->region->pays->id_pays,
            'titre' => $titre,
            'description' => 'Contenu',
            'portee' => 'quartier',
            'statut' => 'active',
            'date_creation' => now(),
            'version' => 1,
        ]);
    }

    private function outil(User $proprietaire): Outil
    {
        return Outil::create([
            'id_proprietaire' => $proprietaire->id_utilisateur,
            'id_quartier' => $proprietaire->id_quartier,
            'id_ville' => $proprietaire->id_ville,
            'id_pays' => $proprietaire->ville->region->pays->id_pays,
            'titre' => 'Perceuse',
            'description' => 'Perceuse sans fil',
            'portee' => 'quartier',
            'etat' => 'bon',
            'disponible' => true,
            'statut' => 'actif',
            'version' => 1,
        ]);
    }
}
