<?php

namespace App\Policies;

use App\Models\DemandeAide;
use App\Models\User;

class DemandeAidePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DemandeAide $demande): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DemandeAide $demande): bool
    {
        return (int) $demande->id_utilisateur === (int) $user->id_utilisateur;
    }

    public function delete(User $user, DemandeAide $demande): bool
    {
        return $this->update($user, $demande);
    }
}
