<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chaque tâche appartient désormais à un utilisateur.
 *
 * On ne modifie pas l'ancienne migration : en équipe, elle a peut-être
 * déjà été exécutée ailleurs. On ajoute une NOUVELLE migration qui fait
 * évoluer la table. C'est l'historique de la base de données.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Clé étrangère vers users.id
            // cascadeOnDelete : si l'utilisateur est supprimé, ses tâches aussi.
            $table->foreignId('user_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
