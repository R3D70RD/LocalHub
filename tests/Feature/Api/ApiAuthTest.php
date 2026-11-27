<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_sante_de_l_api_repond(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson(['statut' => 'ok', 'version' => 'v1']);
    }

    public function test_un_utilisateur_peut_obtenir_un_jeton(): void
    {
        $utilisateur = User::factory()->create([
            'courriel' => 'jeton@localhub.test',
            'mot_de_passe' => 'motdepasse',
        ]);

        $reponse = $this->postJson('/api/v1/auth/token', [
            'courriel' => 'jeton@localhub.test',
            'mot_de_passe' => 'motdepasse',
        ]);

        $reponse->assertOk()
            ->assertJsonPath('data.utilisateur.id', $utilisateur->id_utilisateur);

        $this->assertNotEmpty($reponse->json('data.token'));
    }

    public function test_des_identifiants_invalides_sont_refuses(): void
    {
        User::factory()->create([
            'courriel' => 'jeton@localhub.test',
            'mot_de_passe' => 'motdepasse',
        ]);

        $this->postJson('/api/v1/auth/token', [
            'courriel' => 'jeton@localhub.test',
            'mot_de_passe' => 'mauvais',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('courriel');
    }

    public function test_le_jeton_permet_d_acceder_a_me_puis_est_revoque(): void
    {
        $utilisateur = User::factory()->create();

        $token = $utilisateur->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $utilisateur->id_utilisateur);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Les tests partagent le conteneur entre requêtes : on vide les guards
        // pour forcer une nouvelle résolution du jeton révoqué.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }
}
