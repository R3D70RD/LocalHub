<?php

namespace Tests\Feature;

use App\Enums\StatutPret;
use App\Models\Outil;
use App\Models\Pret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class LoanWebTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_le_cycle_complet_d_un_pret_via_le_web(): void
    {
        [$sarah, $alex, $outil] = $this->contexte();

        $this->actingAs($alex)
            ->get('/outils')
            ->assertOk()
            ->assertSee('Perceuse');

        $this->actingAs($alex)
            ->post(route('outils.prets.store', $outil), $this->dates())
            ->assertRedirect();

        $pret = Pret::query()->firstOrFail();
        $this->assertSame(StatutPret::DEMANDE, $pret->statut);

        $this->actingAs($sarah)
            ->post(route('prets.transition', $pret), [
                'action' => 'approuver',
                'version' => $pret->version,
            ])->assertRedirect();

        $this->assertSame(StatutPret::APPROUVE, $pret->fresh()->statut);

        $this->actingAs($sarah)
            ->post(route('prets.transition', $pret), [
                'action' => 'emprunter',
                'version' => $pret->fresh()->version,
            ])->assertRedirect();

        $this->assertSame(StatutPret::EMPRUNTE, $pret->fresh()->statut);

        $this->actingAs($alex)
            ->post(route('prets.transition', $pret), [
                'action' => 'retourner',
                'version' => $pret->fresh()->version,
            ])->assertRedirect();

        $this->assertSame(StatutPret::RETOURNE, $pret->fresh()->statut);
        $this->assertTrue($outil->fresh()->disponible);

        $this->actingAs($alex)
            ->get(route('prets.show', $pret))
            ->assertOk()
            ->assertSee('Perceuse')
            ->assertSee(__('interface.statut_retourne'));
    }

    public function test_une_deuxieme_demande_web_est_refusee_proprement(): void
    {
        [$sarah, $alex, $outil] = $this->contexte();
        $emma = $this->utilisateurGeo('Bouchard', $outil->ville, $outil->quartier);

        $this->actingAs($alex)
            ->post(route('outils.prets.store', $outil), $this->dates())
            ->assertRedirect();

        $this->actingAs($emma)
            ->from(route('outils.show', $outil))
            ->post(route('outils.prets.store', $outil), $this->dates())
            ->assertRedirect(route('outils.show', $outil))
            ->assertSessionHasErrors('pret');

        $this->assertDatabaseCount('pret', 1);
    }

    private function contexte(): array
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $outil = Outil::create([
            'id_proprietaire' => $sarah->id_utilisateur,
            'id_quartier' => $sarah->id_quartier,
            'id_ville' => $sarah->id_ville,
            'id_pays' => $geo['pays']->id_pays,
            'titre' => 'Perceuse',
            'description' => 'Perceuse sans fil',
            'portee' => 'quartier',
            'etat' => 'bon',
            'disponible' => true,
            'statut' => 'actif',
            'version' => 1,
        ]);

        return [$sarah, $alex, $outil];
    }

    private function dates(): array
    {
        return [
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addDays(3)->toDateString(),
        ];
    }
}
