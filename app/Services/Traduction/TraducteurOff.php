<?php

namespace App\Services\Traduction;

/**
 * Driver désactivé : aucun fournisseur n'est configuré, le bouton de
 * traduction n'est pas affiché.
 */
class TraducteurOff implements Traducteur
{
    public function estDisponible(): bool
    {
        return false;
    }

    public function traduire(string $texte, string $source, string $cible): string
    {
        return $texte;
    }
}
