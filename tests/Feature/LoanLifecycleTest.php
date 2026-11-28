<?php

namespace Tests\Feature;

use App\Enums\StatutPret;
use App\Exceptions\ActionNonAutoriseeException;
use App\Exceptions\TransitionInvalideException;
use App\Models\Outil;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class LoanLifecycleTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_le_cycle_complet_d_un_pret(): void
    {
        [$sarah, $alex, $outil] = $this->contexte();
        $service = app(LoanService::class);

        $pret = $service->demander($alex, $outil, [
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addDays(3)->toDateString(),
            'message' => 'Disponible samedi ?',
        ]);

        $this->assertSame(StatutPret::DEMANDE, $pret->statut);
        $this->assertSame('outil:'.$outil->id_outil, $pret->cle_active);
        $this->assertFalse($outil->fresh()->disponible);

        $pret = $service->transition($pret, 'approuver', $sarah);
        $this->assertSame(StatutPret::APPROUVE, $pret->statut);

        $pret = $service->transition($pret, 'emprunter', $sarah);
        $this->assertSame(StatutPret::EMPRUNTE, $pret->statut);

        $pret = $service->transition($pret, 'retourner', $alex);
        $this->assertSame(StatutPret::RETOURNE, $pret->statut);
        $this->assertNull($pret->cle_active);
        $this->assertTrue($outil->fresh()->disponible);
        $this->assertNotNull($pret->date_retour_effectif);
    }

    public function test_une_transition_invalide_est_refusee(): void
    {
        [$sarah, $alex, $outil] = $this->contexte();
        $service = app(LoanService::class);

        $pret = $service->demander($alex, $outil, $this->dates());

        $this->expectException(TransitionInvalideException::class);
        $service->transition($pret, 'retourner', $alex);
    }

    public function test_un_tiers_ne_peut_pas_transitionner(): void
    {
        [$sarah, $alex, $outil] = $this->contexte();
        $intrus = $this->utilisateurGeo('Intrus', $outil->ville, $outil->quartier);
        $service = app(LoanService::class);

        $pret = $service->demander($alex, $outil, $this->dates());

        $this->expectException(ActionNonAutoriseeException::class);
        $service->transition($pret, 'approuver', $intrus);
    }

    public function test_refus_annulation_et_litige(): void
    {
        [$sarah, $alex, $outil] = $this->contexte();
        $service = app(LoanService::class);

        $pret = $service->demander($alex, $outil, $this->dates());
        $pret = $service->transition($pret, 'refuser', $sarah);
        $this->assertSame(StatutPret::REFUSE, $pret->statut);
        $this->assertNull($pret->cle_active);
        $this->assertTrue($outil->fresh()->disponible);

        $pret = $service->demander($alex, $outil, $this->dates());
        $pret = $service->transition($pret, 'approuver', $sarah);
        $pret = $service->transition($pret, 'annuler', $alex);
        $this->assertSame(StatutPret::ANNULE, $pret->statut);
        $this->assertTrue($outil->fresh()->disponible);

        $pret = $service->demander($alex, $outil, $this->dates());
        $pret = $service->transition($pret, 'approuver', $sarah);
        $pret = $service->transition($pret, 'emprunter', $alex);
        $pret = $service->transition($pret, 'ouvrir_litige', $alex);
        $this->assertSame(StatutPret::LITIGE, $pret->statut);
        $this->assertFalse($outil->fresh()->disponible);

        $gestionnaire = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $pret = $service->transition($pret, 'resoudre', $gestionnaire);
        $this->assertSame(StatutPret::RETOURNE, $pret->statut);
        $this->assertTrue($outil->fresh()->disponible);
    }

    public function test_le_proprietaire_ne_peut_pas_emprunter_son_propre_outil(): void
    {
        [$sarah, , $outil] = $this->contexte();

        $this->expectException(TransitionInvalideException::class);
        app(LoanService::class)->demander($sarah, $outil, $this->dates());
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
