<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * Champs modifiables via create() / update().
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
     * Conversion des types : MySQL stocke 0/1, l'API renverra true/false.
     */
    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }
}
