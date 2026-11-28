<?php

namespace App\Services;

use App\Models\Blocage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BlocageService
{
    public function bloquer(User $bloqueur, User $bloque): void
    {
        if ((int) $bloqueur->id_utilisateur === (int) $bloque->id_utilisateur) {
            return;
        }

        Blocage::firstOrCreate([
            'id_bloqueur' => $bloqueur->id_utilisateur,
            'id_bloque' => $bloque->id_utilisateur,
        ]);
    }

    public function debloquer(User $bloqueur, User $bloque): void
    {
        Blocage::query()
            ->where('id_bloqueur', $bloqueur->id_utilisateur)
            ->where('id_bloque', $bloque->id_utilisateur)
            ->delete();
    }

    public function estBloque(User $bloqueur, User $bloque): bool
    {
        return Blocage::query()
            ->where('id_bloqueur', $bloqueur->id_utilisateur)
            ->where('id_bloque', $bloque->id_utilisateur)
            ->exists();
    }

    /**
     * @return array<int, int>
     */
    public function identifiantsBloques(?User $utilisateur): array
    {
        if ($utilisateur === null) {
            return [];
        }

        return Blocage::query()
            ->where('id_bloqueur', $utilisateur->id_utilisateur)
            ->pluck('id_bloque')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function exclure(Builder $requete, ?User $lecteur, string $colonneAuteur = 'id_utilisateur'): Builder
    {
        $identifiants = $this->identifiantsBloques($lecteur);

        if ($identifiants !== []) {
            $requete->whereNotIn($colonneAuteur, $identifiants);
        }

        return $requete;
    }
}
