<?php

namespace App\Services;

use App\Enums\StatutPret;
use App\Exceptions\ActionNonAutoriseeException;
use App\Exceptions\ConflitPretException;
use App\Exceptions\TransitionInvalideException;
use App\Models\Message;
use App\Models\Outil;
use App\Models\Pret;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    /**
     * Crée une demande de prêt. La colonne unique `cle_active` garantit qu'un
     * seul prêt actif peut exister par outil, même en concurrence.
     *
     * @param  array{date_debut: string, date_fin: string, message?: string|null}  $donnees
     */
    public function demander(User $emprunteur, Outil $outil, array $donnees): Pret
    {
        if ((int) $outil->id_proprietaire === (int) $emprunteur->id_utilisateur) {
            throw new TransitionInvalideException('Vous ne pouvez pas emprunter votre propre outil.');
        }

        if ($outil->statut !== 'actif' || ! $outil->disponible) {
            throw new ConflitPretException('Cet outil n’est pas disponible.');
        }

        $debut = Carbon::parse($donnees['date_debut'])->startOfDay();
        $fin = Carbon::parse($donnees['date_fin'])->startOfDay();

        if ($fin->lt($debut)) {
            throw new TransitionInvalideException('La date de fin doit suivre la date de début.');
        }

        try {
            $pret = DB::transaction(function () use ($emprunteur, $outil, $donnees, $debut, $fin) {
                $verrou = Outil::query()->whereKey($outil->id_outil)->lockForUpdate()->firstOrFail();

                if ($verrou->pretActif() !== null) {
                    throw new ConflitPretException('Cet outil est déjà réservé.');
                }

                $pret = Pret::create([
                    'id_outil' => $verrou->id_outil,
                    'id_emprunteur' => $emprunteur->id_utilisateur,
                    'id_proprietaire' => $verrou->id_proprietaire,
                    'statut' => StatutPret::DEMANDE->value,
                    'date_debut' => $debut->toDateString(),
                    'date_fin' => $fin->toDateString(),
                    'date_demande' => now(),
                    'message' => $donnees['message'] ?? null,
                    'version' => 1,
                    'cle_active' => 'outil:'.$verrou->id_outil,
                ]);

                $verrou->update(['disponible' => false]);

                return $pret;
            });
        } catch (QueryException $exception) {
            if ($this->estViolationUnique($exception)) {
                throw new ConflitPretException('Cet outil est déjà réservé.', 0, $exception);
            }

            throw $exception;
        }

        $this->notifier($pret->load('outil'), $emprunteur, StatutPret::DEMANDE);

        return $pret;
    }

    /**
     * Applique une transition du cycle de vie, avec verrou optimiste.
     */
    public function transition(Pret $pret, string $action, ?User $acteur, ?int $versionAttendue = null): Pret
    {
        try {
            $pret = DB::transaction(function () use ($pret, $action, $acteur, $versionAttendue) {
                $verrou = Pret::query()->whereKey($pret->id_pret)->lockForUpdate()->firstOrFail();
                $statut = $verrou->statut;
                $transition = $statut->transitions()[$action] ?? null;

                if ($transition === null) {
                    throw new TransitionInvalideException(
                        sprintf('Transition « %s » invalide depuis « %s ».', $action, $statut->value)
                    );
                }

                if (! in_array($this->role($verrou, $acteur), $transition['roles'], true)) {
                    throw new ActionNonAutoriseeException('Vous ne pouvez pas effectuer cette action.');
                }

                if ($versionAttendue !== null && (int) $versionAttendue !== (int) $verrou->version) {
                    throw new ConflitPretException('Le prêt a été modifié entre-temps.');
                }

                $nouveauStatut = $transition['statut'];
                $miseAJour = Pret::query()
                    ->whereKey($verrou->id_pret)
                    ->where('version', $verrou->version)
                    ->update([
                        'statut' => $nouveauStatut->value,
                        'version' => $verrou->version + 1,
                        'cle_active' => $nouveauStatut->estActif() ? 'outil:'.$verrou->id_outil : null,
                        'date_retour_effectif' => $action === 'retourner' ? now() : $verrou->date_retour_effectif,
                        'updated_at' => now(),
                    ]);

                if ($miseAJour === 0) {
                    throw new ConflitPretException('Le prêt a été modifié entre-temps.');
                }

                if ($action === 'emprunter') {
                    Outil::query()->whereKey($verrou->id_outil)->update(['disponible' => false]);
                }

                if ($nouveauStatut->estTerminal()) {
                    Outil::query()->whereKey($verrou->id_outil)->update(['disponible' => true]);
                }

                return $verrou->refresh();
            });
        } catch (QueryException $exception) {
            if ($this->estViolationUnique($exception)) {
                throw new ConflitPretException('Cet outil est déjà réservé.', 0, $exception);
            }

            throw $exception;
        }

        $this->notifier($pret->load('outil'), $acteur, $pret->statut);

        return $pret;
    }

    public function envoyerMessage(Pret $pret, User $expediteur, string $contenu): Message
    {
        $this->verifierParticipant($pret, $expediteur);

        $message = $pret->messages()->create([
            'id_expediteur' => $expediteur->id_utilisateur,
            'contenu' => $contenu,
        ]);

        $destinataire = (int) $expediteur->id_utilisateur === (int) $pret->id_proprietaire
            ? $pret->emprunteur
            : $pret->proprietaire;

        if ($destinataire !== null && (int) $destinataire->id_utilisateur !== (int) $expediteur->id_utilisateur) {
            $this->notifications->envoyer($destinataire, 'pret.message', [
                'id_pret' => $pret->id_pret,
                'id_outil' => $pret->id_outil,
                'titre_outil' => $pret->outil?->titre,
                'apercu' => mb_substr($contenu, 0, 120),
            ]);
        }

        return $message;
    }

    /**
     * Marque les prêts dépassés comme `en_retard` et notifie les deux parties.
     *
     * @return int Nombre de prêts basculés.
     */
    public function marquerEnRetard(): int
    {
        $prets = Pret::query()
            ->where('statut', StatutPret::EMPRUNTE->value)
            ->whereDate('date_fin', '<', now()->toDateString())
            ->get();

        $compteur = 0;

        foreach ($prets as $pret) {
            try {
                $this->transition($pret, 'signaler_retard', null);
                $compteur++;
            } catch (TransitionInvalideException|ActionNonAutoriseeException|ConflitPretException) {
                // Le prêt a changé entre la lecture et la transition : on ignore.
            }
        }

        return $compteur;
    }

    /**
     * @return array<int, string>
     */
    public function actionsAutorisees(Pret $pret, ?User $acteur): array
    {
        $role = $this->role($pret, $acteur);
        $actions = [];

        foreach ($pret->statut->transitions() as $action => $transition) {
            if (in_array($role, $transition['roles'], true)) {
                $actions[] = $action;
            }
        }

        return $actions;
    }

    public function verifierParticipant(Pret $pret, User $utilisateur): void
    {
        if (! in_array((int) $utilisateur->id_utilisateur, [(int) $pret->id_proprietaire, (int) $pret->id_emprunteur], true)
            && ! in_array($utilisateur->role, [User::ROLE_MANAGER, User::ROLE_MODERATEUR], true)) {
            throw new ActionNonAutoriseeException('Vous ne participez pas à ce prêt.');
        }
    }

    private function role(Pret $pret, ?User $acteur): string
    {
        if ($acteur === null) {
            return 'system';
        }

        if ((int) $acteur->id_utilisateur === (int) $pret->id_proprietaire) {
            return 'proprietaire';
        }

        if ((int) $acteur->id_utilisateur === (int) $pret->id_emprunteur) {
            return 'emprunteur';
        }

        if (in_array($acteur->role, [User::ROLE_MANAGER, User::ROLE_MODERATEUR], true)) {
            return 'gestionnaire';
        }

        return 'autre';
    }

    private function notifier(Pret $pret, ?User $acteur, StatutPret $statut): void
    {
        $destinataires = [];

        if ($acteur === null) {
            $destinataires = [$pret->proprietaire, $pret->emprunteur];
        } elseif ((int) $acteur->id_utilisateur === (int) $pret->id_proprietaire) {
            $destinataires = [$pret->emprunteur];
        } elseif ((int) $acteur->id_utilisateur === (int) $pret->id_emprunteur) {
            $destinataires = [$pret->proprietaire];
        }

        $dejaNotifies = [];

        foreach ($destinataires as $destinataire) {
            if ($destinataire === null
                || (int) $destinataire->id_utilisateur === (int) ($acteur?->id_utilisateur ?? 0)
                || isset($dejaNotifies[$destinataire->id_utilisateur])) {
                continue;
            }

            $dejaNotifies[$destinataire->id_utilisateur] = true;

            $this->notifications->envoyer($destinataire, 'pret.statut', [
                'id_pret' => $pret->id_pret,
                'id_outil' => $pret->id_outil,
                'titre_outil' => $pret->outil?->titre,
                'statut' => $statut->value,
            ]);
        }
    }

    private function estViolationUnique(QueryException $exception): bool
    {
        $code = $exception->errorInfo[1] ?? null;

        return in_array($code, [1062, 19, 2067], true)
            || str_contains(strtolower($exception->getMessage()), 'unique');
    }
}
