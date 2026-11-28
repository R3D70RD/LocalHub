<?php

namespace App\Policies;

use App\Models\Evenement;
use App\Models\User;

class EvenementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Evenement $evenement): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Evenement $evenement): bool
    {
        return (int) $evenement->id_utilisateur === (int) $user->id_utilisateur;
    }

    public function delete(User $user, Evenement $evenement): bool
    {
        return $this->update($user, $evenement);
    }
}
