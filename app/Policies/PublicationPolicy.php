<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

class PublicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Publication $publication): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Publication $publication): bool
    {
        return (int) $publication->id_utilisateur === (int) $user->id_utilisateur;
    }

    public function delete(User $user, Publication $publication): bool
    {
        return $this->update($user, $publication);
    }

    public function restore(User $user, Publication $publication): bool
    {
        return false;
    }

    public function forceDelete(User $user, Publication $publication): bool
    {
        return false;
    }
}
