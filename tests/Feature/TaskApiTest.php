<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests automatisés de l'API Task Manager.
 *
 * Chaque test simule une requête HTTP (comme Postman) et vérifie la réponse.
 * RefreshDatabase : chaque test démarre avec une base vide (SQLite en mémoire,
 * voir phpunit.xml), la base MySQL de développement n'est jamais touchée.
 *
 * Lancer les tests : php artisan test
 */
class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /**
     * Exécuté avant chaque test : on crée un utilisateur et on le "connecte".
     * Sanctum::actingAs simule l'envoi d'un jeton valide.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** Crée une tâche appartenant à l'utilisateur connecté. */
    private function maTache(string $title = 'Ma tâche'): Task
    {
        return $this->user->tasks()->create(['title' => $title]);
    }

    public function test_on_peut_lister_ses_taches(): void
    {
        $this->maTache('Tâche A');
        $this->maTache('Tâche B');

        $this->getJson('/api/tasks')
            ->assertOk()          // 200
            ->assertJsonCount(2);
    }

    public function test_on_peut_creer_une_tache(): void
    {
        $this->postJson('/api/tasks', [
            'title' => 'Apprendre Laravel',
            'description' => 'Créer ma première API REST',
        ])
            ->assertCreated()     // 201
            ->assertJson([
                'title' => 'Apprendre Laravel',
                'completed' => false, // booléen, pas 0
                'user_id' => $this->user->id, // propriétaire = utilisateur connecté
            ]);

        $this->assertDatabaseHas('tasks', ['title' => 'Apprendre Laravel']);
    }

    public function test_le_titre_est_obligatoire(): void
    {
        $this->postJson('/api/tasks', ['description' => 'Sans titre'])
            ->assertUnprocessable()            // 422
            ->assertJsonValidationErrors([
                'title' => 'Le champ titre est obligatoire.', // message en français
            ]);

        $this->assertDatabaseCount('tasks', 0); // rien n'a été enregistré
    }

    public function test_on_peut_afficher_sa_tache(): void
    {
        $task = $this->maTache();

        $this->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJson(['id' => $task->id, 'title' => 'Ma tâche']);
    }

    public function test_une_tache_inexistante_renvoie_404(): void
    {
        $this->getJson('/api/tasks/999')
            ->assertNotFound()                 // 404
            ->assertExactJson(['message' => 'Ressource introuvable.']);
    }

    public function test_on_peut_modifier_sa_tache(): void
    {
        $task = $this->maTache('Ancien titre');

        $this->putJson("/api/tasks/{$task->id}", ['title' => 'Nouveau titre'])
            ->assertOk()
            ->assertJson(['title' => 'Nouveau titre']);
    }

    public function test_on_peut_marquer_sa_tache_comme_terminee(): void
    {
        $task = $this->maTache('À finir');

        $this->patchJson("/api/tasks/{$task->id}/complete")
            ->assertOk()
            ->assertJson(['completed' => true]);
    }

    public function test_on_peut_supprimer_sa_tache(): void
    {
        $task = $this->maTache('À supprimer');

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJson(['message' => 'Tâche supprimée avec succès.']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    // ── Démo #02 : sécurité ────────────────────────────────────────

    public function test_on_ne_voit_pas_les_taches_des_autres_dans_la_liste(): void
    {
        $this->maTache('La mienne');
        User::factory()->create()->tasks()->create(['title' => 'Celle d\'un autre']);

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'La mienne'])
            ->assertJsonMissing(['title' => 'Celle d\'un autre']);
    }

    public function test_on_ne_peut_pas_toucher_a_la_tache_d_un_autre(): void
    {
        $tacheDunAutre = User::factory()->create()->tasks()->create(['title' => 'Privée']);
        $message = ['message' => 'Cette tâche ne vous appartient pas.'];

        // Voir, modifier, terminer, supprimer : tout est refusé (403)
        $this->getJson("/api/tasks/{$tacheDunAutre->id}")->assertForbidden()->assertExactJson($message);
        $this->putJson("/api/tasks/{$tacheDunAutre->id}", ['title' => 'Piratée'])->assertForbidden();
        $this->patchJson("/api/tasks/{$tacheDunAutre->id}/complete")->assertForbidden();
        $this->deleteJson("/api/tasks/{$tacheDunAutre->id}")->assertForbidden();

        // La tâche est intacte en base
        $this->assertDatabaseHas('tasks', ['id' => $tacheDunAutre->id, 'title' => 'Privée', 'completed' => false]);
    }
}
