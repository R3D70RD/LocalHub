<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class TraductionTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_un_utilisateur_peut_traduire_une_publication_avec_le_driver_factice(): void
    {
        config(['traduction.driver' => 'factice']);

        [$alex, $publication] = $this->contexte('Bonjour le quartier');

        $this->actingAs($alex)
            ->postJson(route('publications.traduire', $publication), ['cible' => 'en'])
            ->assertOk()
            ->assertJsonPath('traduction', '[EN] Bonjour le quartier');
    }

    public function test_la_traduction_est_indisponible_quand_le_driver_est_desactive(): void
    {
        config(['traduction.driver' => 'off']);

        [$alex, $publication] = $this->contexte('Bonjour');

        $this->actingAs($alex)
            ->postJson(route('publications.traduire', $publication), ['cible' => 'en'])
            ->assertStatus(503);
    }

    public function test_un_visiteur_ne_peut_pas_demander_de_traduction(): void
    {
        config(['traduction.driver' => 'factice']);

        [, $publication] = $this->contexte('Bonjour');

        $this->postJson(route('publications.traduire', $publication), ['cible' => 'en'])
            ->assertUnauthorized();
    }

    public function test_le_bouton_de_traduction_apparait_quand_un_fournisseur_est_configure(): void
    {
        config(['traduction.driver' => 'factice']);

        [$alex, $publication] = $this->contexte('Bonjour le quartier');

        $this->actingAs($alex)
            ->get(route('publications.show', $publication))
            ->assertOk()
            ->assertSee(__('interface.traduire'));
    }

    public function test_le_bouton_de_traduction_est_absent_quand_le_driver_est_desactive(): void
    {
        config(['traduction.driver' => 'off']);

        [$alex, $publication] = $this->contexte('Bonjour le quartier');

        $this->actingAs($alex)
            ->get(route('publications.show', $publication))
            ->assertOk()
            ->assertDontSee(__('interface.traduire'));
    }

    /**
     * @return array{0: User, 1: Publication}
     */
    private function contexte(string $description): array
    {
        $geo = $this->creerContexteGeo();
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $publication = Publication::create([
            'id_utilisateur' => $alex->id_utilisateur,
            'id_quartier' => $alex->id_quartier,
            'id_ville' => $alex->id_ville,
            'id_pays' => $alex->ville->region->pays->id_pays,
            'titre' => 'Bonjour',
            'description' => $description,
            'portee' => 'quartier',
            'statut' => 'active',
            'date_creation' => now(),
            'version' => 1,
        ]);

        return [$alex, $publication];
    }
}
