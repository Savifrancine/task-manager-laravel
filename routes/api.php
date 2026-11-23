<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
| Routes de l'API — toutes préfixées automatiquement par /api
*/

// ── Routes publiques : accessibles sans être connecté ──────────────
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// ── Routes protégées : jeton obligatoire (sinon 401) ───────────────
// Le middleware "auth:sanctum" vérifie le header
//   Authorization: Bearer <jeton>
// avant d'exécuter la route.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    /*
    | apiResource() génère en une ligne les 5 routes CRUD :
    |   GET     /api/tasks          → index    (lister)
    |   POST    /api/tasks          → store    (créer)
    |   GET     /api/tasks/{task}   → show     (afficher)
    |   PUT     /api/tasks/{task}   → update   (modifier)
    |   DELETE  /api/tasks/{task}   → destroy  (supprimer)
    */
    Route::apiResource('tasks', TaskController::class);

    // Route supplémentaire : marquer une tâche comme terminée
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);
});
