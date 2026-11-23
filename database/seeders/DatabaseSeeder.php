<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Données de démonstration : php artisan db:seed
 *
 * Deux utilisateurs, chacun avec ses propres tâches,
 * pour tester qu'on ne peut pas accéder aux tâches d'un autre.
 * Mot de passe des deux comptes : password123
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $francine = User::factory()->create([
            'name' => 'Francine',
            'email' => 'francine@example.com',
            'password' => 'password123',
        ]);

        $francine->tasks()->createMany([
            ['title' => 'Maîtriser Laravel', 'description' => 'Créer ma première API REST', 'completed' => true],
            ['title' => 'Ajouter l\'authentification', 'description' => 'Démonstration #02 avec Sanctum'],
            ['title' => 'Préparer mon post LinkedIn', 'description' => 'Expliquer les jetons simplement'],
        ]);

        $alex = User::factory()->create([
            'name' => 'Alex',
            'email' => 'alex@example.com',
            'password' => 'password123',
        ]);

        $alex->tasks()->create([
            'title' => 'Tâche privée d\'Alex',
            'description' => 'Francine ne doit pas pouvoir la voir',
        ]);
    }
}
