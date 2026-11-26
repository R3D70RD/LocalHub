<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class PublicationApiTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_un_utilisateur_authentifie_peut_creer_une_publication(): void
    {
        $geo = $this->creerContexteGeo();
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $categorie = Category::create(['nom' => 'Outils', 'slug' => 'outils']);

        Sanctum::actingAs($alex);

        $this->postJson('/api/v1/publications', [
            'titre' => 'Perceuse à prêter',
            'description' => 'Disponible cette fin de semaine.',
            'id_categorie' => $categorie->id_categorie,
            'lang' => 'fr',
            'portee' => 'quartier',
        ])->assertCreated()
            ->assertJsonPath('data.titre', 'Perceuse à prêter')
            ->assertJsonPath('data.auteur.id', $alex->id_utilisateur);

        $this->assertDatabaseHas('publication', [
            'titre' => 'Perceuse à prêter',
            'id_utilisateur' => $alex->id_utilisateur,
            'id_quartier' => $geo['plateau']->id_quartier,
            'id_ville' => $geo['montreal']->id_ville,
        ]);
    }

    public function test_un_visiteur_ne_peut_pas_creer_de_publication(): void
    {
        $this->postJson('/api/v1/publications', [
            'titre' => 'Anonyme',
            'description' => 'Contenu',
        ])->assertUnauthorized();
    }

    public function test_un_autre_utilisateur_ne_peut_pas_modifier_la_publication(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $publication = Publication::create([
            'id_utilisateur' => $sarah->id_utilisateur,
            'id_quartier' => $sarah->id_quartier,
            'id_ville' => $sarah->id_ville,
            'titre' => 'À Sarah',
            'description' => 'Contenu',
            'lang' => 'fr',
            'portee' => 'quartier',
            'statut' => 'active',
            'date_creation' => now(),
            'version' => 1,
        ]);

        Sanctum::actingAs($alex);

        $this->putJson('/api/v1/publications/'.$publication->id_publication, [
            'titre' => 'Détournée',
            'description' => 'Contenu',
        ])->assertForbidden();
    }

    public function test_le_fil_api_est_filtre_par_portee_et_langue(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $this->publication($sarah, 'Dans le quartier', 'quartier', 'fr');
        $this->publication($sarah, 'Dans la ville', 'ville', 'fr');
        $this->publication($sarah, 'In the country', 'pays', 'en');

        Sanctum::actingAs($alex);

        $this->getJson('/api/v1/publications?scope=quartier')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titre', 'Dans le quartier');

        $this->getJson('/api/v1/publications?scope=ville')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/v1/publications?scope=pays&lang=en')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titre', 'In the country');
    }

    public function test_un_commentaire_et_une_reaction_peuvent_etre_ajoutes(): void
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $publication = $this->publication($sarah, 'Perceuse', 'quartier', 'fr');

        Sanctum::actingAs($alex);

        $this->postJson('/api/v1/publications/'.$publication->id_publication.'/commentaires', [
            'contenu' => 'Je suis intéressé.',
        ])->assertCreated()
            ->assertJsonPath('data.contenu', 'Je suis intéressé.');

        $this->assertDatabaseHas('commentaire', [
            'id_publication' => $publication->id_publication,
            'id_utilisateur' => $alex->id_utilisateur,
        ]);

        $this->postJson('/api/v1/publications/'.$publication->id_publication.'/reactions', [
            'type' => 'j_aime',
        ])->assertOk()->assertJsonPath('data.ajoutee', true);

        $this->postJson('/api/v1/publications/'.$publication->id_publication.'/reactions', [
            'type' => 'j_aime',
        ])->assertOk()->assertJsonPath('data.ajoutee', false);

        $this->assertDatabaseCount('reaction', 0);
    }

    public function test_plusieurs_images_sont_attachees_a_la_publication(): void
    {
        Storage::fake('public');

        $geo = $this->creerContexteGeo();
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        Sanctum::actingAs($alex);

        // PNG 1x1 réel : l'extension GD n'est pas disponible dans l'image PHP.
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Z2ZkAAAAASUVORK5CYII=');

        $this->post('/api/v1/publications', [
            'titre' => 'Avec photos',
            'description' => 'Deux images.',
            'images' => [
                UploadedFile::fake()->createWithContent('un.png', $png),
                UploadedFile::fake()->createWithContent('deux.png', $png),
            ],
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonCount(2, 'data.medias');

        $this->assertDatabaseCount('media', 2);
        $this->assertCount(2, Storage::disk('public')->allFiles('publications'));
    }

    private function publication($auteur, string $titre, string $portee, string $lang): Publication
    {
        return Publication::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville->region->pays->id_pays,
            'titre' => $titre,
            'description' => 'Description',
            'lang' => $lang,
            'portee' => $portee,
            'statut' => 'active',
            'date_creation' => now(),
            'version' => 1,
        ]);
    }
}
