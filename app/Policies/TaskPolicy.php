<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * TaskPolicy : les règles d'autorisation pour les tâches.
 *
 * Authentification = "Qui es-tu ?"        → géré par Sanctum (401 si inconnu)
 * Autorisation     = "As-tu le droit ?"   → géré ici (403 si refusé)
 *
 * Toutes les règles sont regroupées dans ce fichier au lieu d'être
 * répétées dans chaque méthode du contrôleur.
 */
class TaskPolicy
{
    /**
     * Règle unique : seul le propriétaire peut voir, modifier,
     * terminer ou supprimer sa tâche.
     */
    public function manage(User $user, Task $task): Response
    {
        return $user->id === $task->user_id
            ? Response::allow()
            : Response::deny('Cette tâche ne vous appartient pas.');
    }
}
