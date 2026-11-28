<?php

namespace App\Services\Traduction;

/**
 * Driver de démonstration : renvoie le texte préfixé par la langue cible.
 * Utilisé par les tests et les environnements sans fournisseur réel.
 */
class TraducteurFactice implements Traducteur
{
    public function estDisponible(): bool
    {
        return true;
    }

    public function traduire(string $texte, string $source, string $cible): string
    {
        return sprintf('[%s] %s', mb_strtoupper($cible), $texte);
    }
}
