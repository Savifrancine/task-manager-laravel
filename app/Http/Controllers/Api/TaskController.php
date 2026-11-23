<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * TaskController — le "C" de MVC.
 *
 * Le contrôleur reçoit la requête HTTP, demande au modèle (Task)
 * de lire ou d'écrire en base, puis renvoie une réponse JSON.
 * Une méthode = une action de l'API.
 *
 * Démo #02 : toutes ces routes sont protégées (middleware auth:sanctum).
 * $request->user() est donc toujours l'utilisateur connecté.
 */
class TaskController extends Controller
{
    /**
     * GET /api/tasks — Lister MES tâches.
     */
    public function index(Request $request): JsonResponse
    {
        // On part de l'utilisateur connecté : impossible de voir
        // les tâches des autres, même par erreur.
        $tasks = $request->user()->tasks()->latest()->get();

        return response()->json($tasks);
    }

    /**
     * POST /api/tasks — Créer une tâche.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. On valide les données AVANT de toucher à la base.
        //    Si une règle échoue, Laravel répond automatiquement 422.
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed'   => 'boolean',
        ]);

        // 2. On crée la tâche VIA la relation : Laravel remplit
        //    user_id automatiquement avec l'id de l'utilisateur connecté.
        $task = $request->user()->tasks()->create($data);

        // 3. 201 Created : le code HTTP standard pour une création réussie.
        return response()->json($task, 201);
    }

    /**
     * GET /api/tasks/{id} — Afficher une tâche.
     */
    public function show(Task $task): JsonResponse
    {
        // Vérifie la règle "manage" de TaskPolicy : 403 si ce n'est pas ma tâche.
        Gate::authorize('manage', $task);

        return response()->json($task);
    }

    /**
     * PUT/PATCH /api/tasks/{id} — Modifier une tâche.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        Gate::authorize('manage', $task);

        // "sometimes" = la règle ne s'applique que si le champ est envoyé.
        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed'   => 'sometimes|boolean',
        ]);

        $task->update($data);

        return response()->json($task);
    }

    /**
     * PATCH /api/tasks/{id}/complete — Marquer une tâche comme terminée.
     */
    public function complete(Task $task): JsonResponse
    {
        Gate::authorize('manage', $task);

        $task->update(['completed' => true]);

        return response()->json($task);
    }

    /**
     * DELETE /api/tasks/{id} — Supprimer une tâche.
     */
    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('manage', $task);

        $task->delete();

        return response()->json([
            'message' => 'Tâche supprimée avec succès.',
        ]);
    }
}
