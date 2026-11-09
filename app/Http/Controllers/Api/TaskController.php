<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TaskController — le "C" de MVC.
 *
 * Le contrôleur reçoit la requête HTTP, demande au modèle (Task)
 * de lire ou d'écrire en base, puis renvoie une réponse JSON.
 * Une méthode = une action de l'API.
 */
class TaskController extends Controller
{
    /**
     * GET /api/tasks — Lister toutes les tâches.
     */
    public function index(): JsonResponse
    {
        // latest() = les plus récentes en premier
        $tasks = Task::latest()->get();

        return response()->json($tasks); // 200 OK par défaut
    }

    /**
     * POST /api/tasks — Créer une tâche.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. On valide les données AVANT de toucher à la base.
        //    Si une règle échoue, Laravel répond automatiquement
        //    422 Unprocessable Content avec la liste des erreurs.
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed'   => 'boolean',
        ]);

        // 2. On enregistre (seuls les champs de $fillable sont acceptés).
        $task = Task::create($data);

        // 3. 201 Created : le code HTTP standard pour une création réussie.
        return response()->json($task, 201);
    }

    /**
     * GET /api/tasks/{id} — Afficher une tâche.
     *
     * "Route Model Binding" : Laravel cherche tout seul la tâche
     * correspondant à l'id de l'URL. Si elle n'existe pas → 404 automatique.
     */
    public function show(Task $task): JsonResponse
    {
        return response()->json($task);
    }

    /**
     * PUT/PATCH /api/tasks/{id} — Modifier une tâche.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        // "sometimes" = la règle ne s'applique que si le champ est envoyé.
        // On peut donc modifier un seul champ sans renvoyer tous les autres.
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
     *
     * Une route dédiée rend l'intention claire : pas besoin d'envoyer de corps JSON.
     */
    public function complete(Task $task): JsonResponse
    {
        $task->update(['completed' => true]);

        return response()->json($task);
    }

    /**
     * DELETE /api/tasks/{id} — Supprimer une tâche.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'message' => 'Tâche supprimée avec succès.',
        ]);
    }
}
