# Task Manager — API REST avec Laravel

> **Démonstration #01** de ma série « De la théorie à la pratique » : construire pas à pas une API REST de gestion de tâches.

API permettant de créer, lister, consulter, modifier, terminer et supprimer des tâches. Le code est commenté en français pour servir de support d'apprentissage.

## Technologies

| Technologie | Utilité |
|---|---|
| PHP 8.4 / Laravel 13 | Backend |
| MySQL (XAMPP) | Base de données |
| Eloquent ORM | Accès aux données |
| PHPUnit | Tests automatisés |
| Postman | Tests manuels de l'API |

## Endpoints

| Méthode | URL | Action | Succès |
|---|---|---|---|
| `GET` | `/api/tasks` | Lister les tâches | 200 |
| `POST` | `/api/tasks` | Créer une tâche | 201 |
| `GET` | `/api/tasks/{id}` | Afficher une tâche | 200 |
| `PUT/PATCH` | `/api/tasks/{id}` | Modifier une tâche | 200 |
| `PATCH` | `/api/tasks/{id}/complete` | Marquer comme terminée | 200 |
| `DELETE` | `/api/tasks/{id}` | Supprimer une tâche | 200 |

**Erreurs gérées :** `422` si les données sont invalides (ex. titre manquant), `404` si la tâche n'existe pas. L'API répond toujours en JSON.

### Exemple — créer une tâche

```http
POST /api/tasks
Content-Type: application/json

{
  "title": "Apprendre Laravel",
  "description": "Créer ma première API REST"
}
```

Réponse `201 Created` :

```json
{
  "id": 1,
  "title": "Apprendre Laravel",
  "description": "Créer ma première API REST",
  "completed": false,
  "created_at": "2026-11-09T16:14:08.000000Z",
  "updated_at": "2026-11-09T16:14:08.000000Z"
}
```

## Installation

Prérequis : PHP 8.3+, Composer, MySQL.

```bash
git clone <url-du-depot> task-manager
cd task-manager
composer install
cp .env.example .env
php artisan key:generate
```

Créer une base MySQL `task_manager`, puis renseigner dans `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=
```

Lancer les migrations et le serveur :

```bash
php artisan migrate
php artisan serve
```

L'API est disponible sur `http://127.0.0.1:8000/api/tasks`.

## Tester

**Tests automatisés** (base SQLite en mémoire, la base MySQL n'est pas touchée) :

```bash
php artisan test
```

**Postman :** importer [`postman/task-manager.postman_collection.json`](postman/task-manager.postman_collection.json), qui contient les 8 requêtes prêtes à l'emploi (succès et erreurs).

## Où regarder dans le code

| Fichier | Rôle |
|---|---|
| [`database/migrations/..._create_tasks_table.php`](database/migrations) | Structure de la table `tasks` |
| [`app/Models/Task.php`](app/Models/Task.php) | Le modèle (champs autorisés, conversion des types) |
| [`app/Http/Controllers/Api/TaskController.php`](app/Http/Controllers/Api/TaskController.php) | La logique de chaque endpoint |
| [`routes/api.php`](routes/api.php) | La déclaration des routes |
| [`bootstrap/app.php`](bootstrap/app.php) | Réponses d'erreur en JSON |
| [`tests/Feature/TaskApiTest.php`](tests/Feature/TaskApiTest.php) | Les tests automatisés |

## Pistes d'évolution

- Authentification avec Laravel Sanctum
- Priorités et dates limites
- Pagination et filtres (`?completed=true`)
