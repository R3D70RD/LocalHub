<?php

namespace App\Policies;

use App\Models\Outil;
use App\Models\User;

class OutilPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Outil $outil): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Outil $outil): bool
    {
        return (int) $outil->id_proprietaire === (int) $user->id_utilisateur;
    }

    public function delete(User $user, Outil $outil): bool
    {
        return $this->update($user, $outil);
    }
}
