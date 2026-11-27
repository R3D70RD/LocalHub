<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_authentifie_peut_creer_une_publication(): void
    {
        $utilisateur = User::factory()->create();
        $categorie = Category::create(['nom' => 'Outils', 'slug' => 'outils']);

        $reponse = $this->actingAs($utilisateur)->post(route('publications.store'), [
            'titre' => 'Tondeuse à prêter',
            'description' => 'Disponible pour le voisinage.',
            'prix' => 0,
            'id_categorie' => $categorie->id_categorie,
            'lang' => 'fr',
            'portee' => 'quartier',
            'statut' => 'active',
        ]);

        $reponse->assertRedirect();

        $this->assertDatabaseHas('publication', [
            'titre' => 'Tondeuse à prêter',
            'id_utilisateur' => $utilisateur->id_utilisateur,
            'lang' => 'fr',
            'portee' => 'quartier',
        ]);
    }

    public function test_la_creation_est_refusee_sans_titre(): void
    {
        $utilisateur = User::factory()->create();

        $this->actingAs($utilisateur)
            ->post(route('publications.store'), [
                'description' => 'Contenu',
                'statut' => 'active',
            ])
            ->assertSessionHasErrors('titre');

        $this->assertSame(0, Publication::count());
    }
}
