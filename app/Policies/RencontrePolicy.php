<?php

namespace App\Policies;

use App\Models\Rencontre;
use App\Models\User;

class RencontrePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Rencontre $rencontre): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Rencontre $rencontre): bool
    {
        return (int) $rencontre->id_utilisateur === (int) $user->id_utilisateur;
    }

    public function delete(User $user, Rencontre $rencontre): bool
    {
        return $this->update($user, $rencontre);
    }
}
