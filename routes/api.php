<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
| Routes de l'API — toutes préfixées automatiquement par /api
|
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
