<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Visiteur non connecté : sur un site web, Laravel le redirige vers
        // une page "login". Une API n'a pas de page : pas de redirection,
        // on laisse l'erreur 401 (définie plus bas) répondre en JSON.
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('api/*') ? null : '/'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Une API doit toujours répondre en JSON, même en cas d'erreur
        // (404, 422...), sans dépendre du header "Accept" envoyé par le client.
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*')
        );

        // Ressource introuvable (ex : GET /api/tasks/999) : message clair,
        // sans exposer les détails internes de l'application.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Ressource introuvable.',
                ], 404);
            }
        });

        // Pas de jeton, ou jeton invalide → 401 "Qui es-tu ?"
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Non authentifié. Connectez-vous pour obtenir un jeton.',
                ], 401);
            }
        });

        // Connecté, mais pas le droit (TaskPolicy) → 403 "Tu n'as pas le droit"
        // On renvoie le message de la Policy, sans les détails techniques.
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 403);
            }
        });
    })->create();
