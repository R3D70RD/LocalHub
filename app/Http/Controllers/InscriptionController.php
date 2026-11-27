<?php

namespace App\Http\Controllers;

use App\Models\ParametreUtilisateur;
use App\Models\Quartier;
use App\Models\User;
use App\Models\Ville;
use App\Support\Geohash;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InscriptionController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'villes' => Ville::query()
                ->with(['quartiers' => fn ($query) => $query->where('actif', true)->orderBy('nom_fr')])
                ->where('actif', true)
                ->orderBy('nom_fr')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'courriel' => ['required', 'email', 'max:255', 'unique:utilisateur,courriel'],
            'id_ville' => ['required', 'integer', 'exists:ville,id_ville'],
            'id_quartier' => ['nullable', 'integer', 'exists:quartier,id_quartier'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'mot_de_passe' => ['required', 'confirmed', Password::min(8)],
        ]);

        $ville = Ville::query()->findOrFail($data['id_ville']);
        $quartier = null;

        if (! empty($data['id_quartier'])) {
            $quartier = Quartier::query()
                ->where('id_ville', $ville->id_ville)
                ->find($data['id_quartier']);

            if ($quartier === null) {
                throw ValidationException::withMessages([
                    'id_quartier' => 'Ce quartier n’appartient pas à la ville choisie.',
                ]);
            }
        }

        $token = $request->validate([
            'g-recaptcha-response' => ['required', 'string'],
        ])['g-recaptcha-response'];

        try {
            $verification = Http::asForm()->timeout(5)->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret' => config('services.recaptcha.secret_key'),
                    'response' => $token,
                ]
            );
        } catch (ConnectionException $e) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Vérification indisponible. Réessayez.',
            ]);
        }

        if (! $verification->successful() || $verification->json('success') !== true) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Veuillez refaire la vérification.',
            ]);
        }

        $latitude = $quartier?->centroid_lat ?? $ville->latitude;
        $longitude = $quartier?->centroid_lng ?? $ville->longitude;

        $user = User::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'courriel' => $data['courriel'],
            'telephone' => $data['telephone'] ?? null,
            'bio' => $data['bio'] ?? null,
            'mot_de_passe' => $data['mot_de_passe'],
            'id_ville' => $ville->id_ville,
            'id_quartier' => $quartier?->id_quartier,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'geohash' => ($latitude !== null && $longitude !== null)
                ? Geohash::encode((float) $latitude, (float) $longitude, 7)
                : null,
            'locale' => app()->getLocale(),
        ]);

        ParametreUtilisateur::create([
            'id_utilisateur' => $user->id_utilisateur,
            'portee_defaut' => 'quartier',
            'afficher_position' => 'quartier',
            'notifications_actives' => true,
            'langue' => app()->getLocale(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('publications.index');
    }
}
