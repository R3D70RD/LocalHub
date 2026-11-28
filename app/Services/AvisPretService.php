<?php

namespace App\Services;

use App\Enums\StatutPret;
use App\Exceptions\ActionNonAutoriseeException;
use App\Exceptions\TransitionInvalideException;
use App\Models\AvisPret;
use App\Models\Pret;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AvisPretService
{
    public function __construct(
        private readonly FiabiliteService $fiabilite,
        private readonly NotificationService $notifications,
    ) {}

    public function noter(Pret $pret, User $auteur, int $note, ?string $commentaire): AvisPret
    {
        if (! in_array((int) $auteur->id_utilisateur, [(int) $pret->id_emprunteur, (int) $pret->id_proprietaire], true)) {
            throw new ActionNonAutoriseeException('Vous ne participez pas à ce prêt.');
        }

        if ($pret->statut !== StatutPret::RETOURNE) {
            throw new TransitionInvalideException('Vous pouvez noter un prêt une fois terminé.');
        }

        $existe = AvisPret::query()
            ->where('id_pret', $pret->id_pret)
            ->where('id_auteur', $auteur->id_utilisateur)
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages(['note' => __('interface.already_reviewed')]);
        }

        $idCible = (int) $auteur->id_utilisateur === (int) $pret->id_emprunteur
            ? (int) $pret->id_proprietaire
            : (int) $pret->id_emprunteur;

        $avis = AvisPret::create([
            'id_pret' => $pret->id_pret,
            'id_auteur' => $auteur->id_utilisateur,
            'id_cible' => $idCible,
            'note' => max(1, min(5, $note)),
            'commentaire' => $commentaire,
        ]);

        $cible = User::query()->find($idCible);

        if ($cible !== null) {
            $this->fiabilite->recalculer($cible);
            $this->notifications->envoyer($cible, 'avis_pret.nouveau', [
                'id_pret' => $pret->id_pret,
                'id_outil' => $pret->id_outil,
                'note' => $avis->note,
            ]);
        }

        return $avis;
    }
}
