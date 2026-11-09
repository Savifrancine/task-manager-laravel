<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_on_peut_lister_les_taches(): void
    {
        Task::create(['title' => 'Tâche A']);
        Task::create(['title' => 'Tâche B']);

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
            ]);

        // On vérifie que la tâche est bien enregistrée en base
        $this->assertDatabaseHas('tasks', ['title' => 'Apprendre Laravel']);
    }

    public function test_le_titre_est_obligatoire(): void
    {
        $this->postJson('/api/tasks', ['description' => 'Sans titre'])
            ->assertUnprocessable()            // 422
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseCount('tasks', 0); // rien n'a été enregistré
    }

    public function test_on_peut_afficher_une_tache(): void
    {
        $task = Task::create(['title' => 'Ma tâche']);

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

    public function test_on_peut_modifier_une_tache(): void
    {
        $task = Task::create(['title' => 'Ancien titre']);

        $this->putJson("/api/tasks/{$task->id}", ['title' => 'Nouveau titre'])
            ->assertOk()
            ->assertJson(['title' => 'Nouveau titre']);
    }

    public function test_on_peut_marquer_une_tache_comme_terminee(): void
    {
        $task = Task::create(['title' => 'À finir']);

        $this->patchJson("/api/tasks/{$task->id}/complete")
            ->assertOk()
            ->assertJson(['completed' => true]);
    }

    public function test_on_peut_supprimer_une_tache(): void
    {
        $task = Task::create(['title' => 'À supprimer']);

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJson(['message' => 'Tâche supprimée avec succès.']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
