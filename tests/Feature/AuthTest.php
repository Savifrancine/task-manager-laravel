<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de l'authentification : inscription, connexion, jetons.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_on_peut_creer_un_compte(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Francine',
            'email' => 'francine@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertCreated()                          // 201
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonMissingPath('user.password');  // le mot de passe n'est jamais renvoyé

        // En base, le mot de passe est chiffré, pas en clair
        $this->assertNotSame('password123', User::first()->password);
    }

    public function test_un_email_ne_peut_servir_qu_une_fois(): void
    {
        User::factory()->create(['email' => 'francine@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Copie',
            'email' => 'francine@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => 'La valeur du champ e-mail est déjà utilisée.']);
    }

    public function test_on_peut_se_connecter_et_recevoir_un_jeton(): void
    {
        User::factory()->create(['email' => 'francine@example.com', 'password' => 'password123']);

        $this->postJson('/api/login', [
            'email' => 'francine@example.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_un_mauvais_mot_de_passe_est_refuse(): void
    {
        User::factory()->create(['email' => 'francine@example.com', 'password' => 'password123']);

        $this->postJson('/api/login', [
            'email' => 'francine@example.com',
            'password' => 'mauvais-mot-de-passe',
        ])
            ->assertUnauthorized()                     // 401
            ->assertExactJson(['message' => 'Identifiants incorrects.']);
    }

    public function test_sans_jeton_les_taches_sont_inaccessibles(): void
    {
        $this->getJson('/api/tasks')
            ->assertUnauthorized()                     // 401
            ->assertExactJson(['message' => 'Non authentifié. Connectez-vous pour obtenir un jeton.']);
    }

    public function test_sans_jeton_ni_header_accept_on_recoit_quand_meme_un_401_json(): void
    {
        // Un client qui n'envoie pas "Accept: application/json"
        // ne doit pas provoquer de redirection ni d'erreur 500.
        $this->get('/api/tasks')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Non authentifié. Connectez-vous pour obtenir un jeton.']);
    }

    public function test_parcours_complet_avec_un_vrai_jeton(): void
    {
        User::factory()->create(['email' => 'francine@example.com', 'password' => 'password123']);

        // 1. Connexion : on récupère le jeton
        $token = $this->postJson('/api/login', [
            'email' => 'francine@example.com',
            'password' => 'password123',
        ])->json('token');

        // 2. On l'envoie dans le header Authorization
        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJson(['email' => 'francine@example.com']);

        // 3. Déconnexion : le jeton est supprimé en base
        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Déconnexion réussie.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
