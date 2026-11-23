<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * AuthController — inscription, connexion, déconnexion.
 *
 * Principe des jetons (tokens) avec Sanctum :
 * 1. L'utilisateur se connecte avec son e-mail et son mot de passe.
 * 2. L'API lui renvoie un jeton, une longue chaîne secrète.
 * 3. À chaque requête suivante, le client envoie ce jeton dans le header :
 *      Authorization: Bearer <jeton>
 * 4. L'API reconnaît ainsi l'utilisateur, sans redemander le mot de passe.
 */
class AuthController extends Controller
{
    /**
     * POST /api/register — Créer un compte.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users', // un seul compte par e-mail
            'password' => 'required|string|min:8|confirmed',      // exige "password_confirmation"
        ]);

        // Le mot de passe est chiffré automatiquement (cast "hashed" du modèle User)
        $user = User::create($data);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * POST /api/login — Se connecter.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        // Hash::check compare le mot de passe saisi avec la version chiffrée.
        // Même message si l'e-mail OU le mot de passe est faux : on ne révèle
        // pas à un attaquant quels e-mails existent dans la base.
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects.',
            ], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * GET /api/me — Qui suis-je ? (route protégée)
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * POST /api/logout — Se déconnecter.
     * On supprime le jeton utilisé : il ne fonctionnera plus jamais.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }
}
