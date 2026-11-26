<?php

namespace App\Enums;

/**
 * Cycle de vie d'un prêt d'outil.
 *
 * Les transitions autorisées sont centralisées ici afin que le service de
 * prêt (web + API) reste la seule source de vérité.
 */
enum StatutPret: string
{
    case DEMANDE = 'demande';
    case APPROUVE = 'approuve';
    case EMPRUNTE = 'emprunte';
    case RETOURNE = 'retourne';
    case REFUSE = 'refuse';
    case ANNULE = 'annule';
    case EN_RETARD = 'en_retard';
    case LITIGE = 'litige';

    /**
     * @return array<string, array{statut: self, roles: array<int, string>}>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::DEMANDE => [
                'approuver' => ['statut' => self::APPROUVE, 'roles' => ['proprietaire']],
                'refuser' => ['statut' => self::REFUSE, 'roles' => ['proprietaire']],
                'annuler' => ['statut' => self::ANNULE, 'roles' => ['emprunteur']],
            ],
            self::APPROUVE => [
                'emprunter' => ['statut' => self::EMPRUNTE, 'roles' => ['proprietaire', 'emprunteur']],
                'annuler' => ['statut' => self::ANNULE, 'roles' => ['proprietaire', 'emprunteur']],
            ],
            self::EMPRUNTE => [
                'retourner' => ['statut' => self::RETOURNE, 'roles' => ['proprietaire', 'emprunteur']],
                'signaler_retard' => ['statut' => self::EN_RETARD, 'roles' => ['system']],
                'ouvrir_litige' => ['statut' => self::LITIGE, 'roles' => ['proprietaire', 'emprunteur']],
            ],
            self::EN_RETARD => [
                'retourner' => ['statut' => self::RETOURNE, 'roles' => ['proprietaire', 'emprunteur']],
                'ouvrir_litige' => ['statut' => self::LITIGE, 'roles' => ['proprietaire', 'emprunteur']],
            ],
            self::LITIGE => [
                'resoudre' => ['statut' => self::RETOURNE, 'roles' => ['gestionnaire']],
            ],
            self::RETOURNE, self::REFUSE, self::ANNULE => [],
        };
    }

    public function peutTransitionner(string $action): bool
    {
        return array_key_exists($action, $this->transitions());
    }

    public function estActif(): bool
    {
        return match ($this) {
            self::DEMANDE, self::APPROUVE, self::EMPRUNTE, self::EN_RETARD, self::LITIGE => true,
            self::RETOURNE, self::REFUSE, self::ANNULE => false,
        };
    }

    public function estTerminal(): bool
    {
        return ! $this->estActif();
    }

    public function label(): string
    {
        return match ($this) {
            self::DEMANDE => 'Demande',
            self::APPROUVE => 'Approuvé',
            self::EMPRUNTE => 'Emprunté',
            self::RETOURNE => 'Retourné',
            self::REFUSE => 'Refusé',
            self::ANNULE => 'Annulé',
            self::EN_RETARD => 'En retard',
            self::LITIGE => 'Litige',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
