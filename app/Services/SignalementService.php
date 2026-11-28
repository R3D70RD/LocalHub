<?php

namespace App\Services;

use App\Models\ModerationAction;
use App\Models\Signalement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SignalementService
{
    /**
     * Statut appliqué à la cible lorsqu'un modérateur choisit « masquer ».
     */
    private const STATUT_MASQUE = [
        'publication' => 'inactif',
        'outil' => 'inactif',
        'annonce' => 'inactif',
        'demande_aide' => 'fermee',
        'evenement' => 'annule',
        'rencontre' => 'annulee',
    ];

    public function __construct(private readonly NotificationService $notifications) {}

    public function signaler(User $auteur, string $type, int $idCible, string $motif, ?string $description): Signalement
    {
        $signalement = Signalement::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'cible_type' => $type,
            'cible_id' => $idCible,
            'motif' => $motif,
            'description' => $description,
            'statut' => 'en_attente',
        ]);

        foreach ($this->moderateurs() as $moderateur) {
            $this->notifications->envoyer($moderateur, 'signalement.nouveau', [
                'id_signalement' => $signalement->id_signalement,
                'cible_type' => $type,
                'motif' => $motif,
            ]);
        }

        return $signalement;
    }

    public function file(int $parPage = 20): LengthAwarePaginator
    {
        return Signalement::query()
            ->with(['utilisateur', 'moderateur'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at')
            ->paginate($parPage);
    }

    public function traiter(Signalement $signalement, User $moderateur, string $action, ?string $note): ModerationAction
    {
        return DB::transaction(function () use ($signalement, $moderateur, $action, $note) {
            $enregistrement = ModerationAction::create([
                'id_signalement' => $signalement->id_signalement,
                'id_moderateur' => $moderateur->id_utilisateur,
                'action' => $action,
                'note' => $note,
            ]);

            $signalement->update([
                'statut' => $action === 'rejeter' ? 'rejete' : 'traite',
                'id_moderateur' => $moderateur->id_utilisateur,
            ]);

            if ($action === 'masquer') {
                $this->masquerCible($signalement);
            } elseif ($action === 'supprimer') {
                $signalement->cible()?->delete();
            }

            return $enregistrement;
        });
    }

    private function masquerCible(Signalement $signalement): void
    {
        $cible = $signalement->cible();

        if ($cible === null) {
            return;
        }

        $statut = self::STATUT_MASQUE[$signalement->cible_type] ?? null;

        if ($statut !== null) {
            $cible->update(['statut' => $statut]);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function moderateurs()
    {
        return User::query()
            ->whereIn('role', [User::ROLE_MANAGER, User::ROLE_MODERATEUR])
            ->get();
    }
}
