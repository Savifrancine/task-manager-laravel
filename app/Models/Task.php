<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    /**
     * Champs modifiables via create() / update().
     *
     * "user_id" n'y figure PAS volontairement : le propriétaire est
     * toujours l'utilisateur connecté, jamais une valeur envoyée par le client.
     * Sinon, n'importe qui pourrait créer une tâche au nom d'un autre.
     */
    protected $fillable = [
        'title',
        'description',
        'completed',
    ];

    /**
     * Valeurs par défaut (cohérentes avec la migration).
     */
    protected $attributes = [
        'completed' => false,
    ];

    /**
     * Relation : une tâche appartient à un utilisateur.
     * Exemple : $task->user  → son propriétaire.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Conversion des types : MySQL stocke 0/1, l'API renverra true/false.
     */
    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }
}
