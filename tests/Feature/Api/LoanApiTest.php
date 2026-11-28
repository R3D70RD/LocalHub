<?php

namespace Tests\Feature\Api;

use App\Enums\StatutPret;
use App\Models\Notification;
use App\Models\Outil;
use App\Models\Pret;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class LoanApiTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_deux_demandes_concurrentes_une_seule_reussit(): void
    {
        [$sarah, $alex, $emma, $outil] = $this->contexte();

        Sanctum::actingAs($alex);
        $this->postJson('/api/v1/outils/'.$outil->id_outil.'/prets', $this->dates())
            ->assertCreated()
            ->assertJsonPath('data.statut', StatutPret::DEMANDE->value);

        Sanctum::actingAs($emma);
        $this->postJson('/api/v1/outils/'.$outil->id_outil.'/prets', $this->dates())
            ->assertStatus(409)
            ->assertJsonPath('code', 'pret_conflit');

        $this->assertDatabaseCount('pret', 1);
    }

    public function test_le_cycle_complet_via_api(): void
    {
        [$sarah, $alex, , $outil] = $this->contexte();
        $service = app(LoanService::class);

        $pret = $service->demander($alex, $outil, $this->dates());

        Sanctum::actingAs($sarah);
        $this->postJson('/api/v1/prets/'.$pret->id_pret.'/transition', [
            'action' => 'approuver',
            'version' => $pret->version,
        ])->assertOk()->assertJsonPath('data.statut', StatutPret::APPROUVE->value);

        Sanctum::actingAs($alex);
        $this->postJson('/api/v1/prets/'.$pret->id_pret.'/transition', [
            'action' => 'emprunter',
            'version' => 2,
        ])->assertOk()->assertJsonPath('data.statut', StatutPret::EMPRUNTE->value);

        $this->postJson('/api/v1/prets/'.$pret->id_pret.'/transition', [
            'action' => 'retourner',
            'version' => 3,
        ])->assertOk()->assertJsonPath('data.statut', StatutPret::RETOURNE->value);

        $this->assertTrue($outil->fresh()->disponible);
    }

    public function test_un_verrou_optimiste_perime_renvoie_409(): void
    {
        [$sarah, $alex, , $outil] = $this->contexte();
        $pret = app(LoanService::class)->demander($alex, $outil, $this->dates());

        Sanctum::actingAs($sarah);
        $this->postJson('/api/v1/prets/'.$pret->id_pret.'/transition', [
            'action' => 'approuver',
            'version' => 1,
        ])->assertOk();

        $this->postJson('/api/v1/prets/'.$pret->id_pret.'/transition', [
            'action' => 'annuler',
            'version' => 1,
        ])->assertStatus(409)
            ->assertJsonPath('code', 'pret_conflit');
    }

    public function test_une_cle_d_idempotence_rejoue_la_meme_reponse(): void
    {
        [$sarah, $alex, , $outil] = $this->contexte();
        $pret = app(LoanService::class)->demander($alex, $outil, $this->dates());

        Sanctum::actingAs($sarah);

        $reponse = $this->postJson('/api/v1/prets/'.$pret->id_pret.'/transition', [
            'action' => 'approuver',
            'version' => 1,
        ], ['Idempotency-Key' => 'cle-transition-1'])
            ->assertOk()
            ->assertHeader('Idempotent-Replayed', 'false');

        $this->postJson('/api/v1/prets/'.$pret->id_pret.'/transition', [
            'action' => 'approuver',
            'version' => 1,
        ], ['Idempotency-Key' => 'cle-transition-1'])
            ->assertOk()
            ->assertHeader('Idempotent-Replayed', 'true')
            ->assertJsonPath('data.id', $reponse->json('data.id'))
            ->assertJsonPath('data.version', $reponse->json('data.version'));

        $this->assertDatabaseCount('pret', 1);
        $this->assertSame(1, Pret::query()->where('statut', StatutPret::APPROUVE->value)->count());
    }

    public function test_le_balayage_marque_les_prets_en_retard_et_notifie(): void
    {
        [$sarah, $alex, , $outil] = $this->contexte();

        $pret = Pret::create([
            'id_outil' => $outil->id_outil,
            'id_emprunteur' => $alex->id_utilisateur,
            'id_proprietaire' => $sarah->id_utilisateur,
            'statut' => StatutPret::EMPRUNTE->value,
            'date_debut' => now()->subDays(10)->toDateString(),
            'date_fin' => now()->subDay()->toDateString(),
            'date_demande' => now()->subDays(10),
            'version' => 1,
            'cle_active' => 'outil:'.$outil->id_outil,
        ]);

        $this->artisan('prets:marquer-en-retard')->assertSuccessful();

        $this->assertSame(StatutPret::EN_RETARD, $pret->fresh()->statut);
        $this->assertSame(2, Notification::query()->where('type', 'pret.statut')->count());
    }

    public function test_les_messages_sont_echanges_entre_les_participants(): void
    {
        [$sarah, $alex, , $outil] = $this->contexte();
        $pret = app(LoanService::class)->demander($alex, $outil, $this->dates());

        Sanctum::actingAs($alex);
        $this->postJson('/api/v1/prets/'.$pret->id_pret.'/messages', [
            'contenu' => 'Je peux passer à 10 h.',
        ])->assertCreated();

        Sanctum::actingAs($sarah);
        $this->getJson('/api/v1/prets/'.$pret->id_pret.'/messages')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.contenu', 'Je peux passer à 10 h.');

        $this->assertSame(1, Notification::query()->where('type', 'pret.message')->count());
    }

    private function contexte(): array
    {
        $geo = $this->creerContexteGeo();
        $sarah = $this->utilisateurGeo('Gagnon', $geo['montreal'], $geo['plateau']);
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $emma = $this->utilisateurGeo('Bouchard', $geo['montreal'], $geo['verdun']);

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

        return [$sarah, $alex, $emma, $outil];
    }

    private function dates(): array
    {
        return [
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->addDays(3)->toDateString(),
        ];
    }
}
