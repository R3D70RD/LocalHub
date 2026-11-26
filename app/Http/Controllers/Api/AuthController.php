<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'courriel' => ['required', 'email'],
            'mot_de_passe' => ['required', 'string'],
            'appareil' => ['nullable', 'string', 'max:100'],
        ]);

        $utilisateur = User::query()->where('courriel', $data['courriel'])->first();

        if ($utilisateur === null || ! Hash::check($data['mot_de_passe'], $utilisateur->mot_de_passe)) {
            throw ValidationException::withMessages([
                'courriel' => ['Identifiants invalides.'],
            ]);
        }

        $token = $utilisateur->createToken($data['appareil'] ?? 'api-v1');

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'utilisateur' => [
                    'id' => $utilisateur->id_utilisateur,
                    'nom' => $utilisateur->nom,
                    'prenom' => $utilisateur->prenom,
                    'courriel' => $utilisateur->courriel,
                    'locale' => $utilisateur->locale?->value,
                ],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $utilisateur = $request->user();

        return response()->json([
            'data' => [
                'id' => $utilisateur->id_utilisateur,
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'courriel' => $utilisateur->courriel,
                'locale' => $utilisateur->locale?->value,
                'id_ville' => $utilisateur->id_ville,
                'id_quartier' => $utilisateur->id_quartier,
                'geohash' => $utilisateur->geohash,
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['statut' => 'deconnecte']);
    }
}
