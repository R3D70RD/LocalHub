<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Villes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InscriptionController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'courriel' => ['required', 'email', 'max:255', 'unique:utilisateur,courriel'],
            'ville' => ['required', 'string', Rule::in(Villes::QUEBEC)],
            'mot_de_passe' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'courriel' => $data['courriel'],
            'ville' => $data['ville'],
            'mot_de_passe' => $data['mot_de_passe'],
        ]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('publications.index');
    }
}
