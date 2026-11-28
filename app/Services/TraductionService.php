<?php

namespace App\Services;

use App\Services\Traduction\Traducteur;

/**
 * Point d'entrée de la traduction à la demande.
 *
 * Le service est agnostique du fournisseur : le driver est choisi dans
 * `config/traduction.php`. Le texte est traduit à la volée puis renvoyé au
 * client ; il n'est jamais stocké en base.
 */
class TraductionService
{
    public const LANGUES = ['fr', 'en'];

    public function __construct(private readonly Traducteur $traducteur) {}

    public function estDisponible(): bool
    {
        return $this->traducteur->estDisponible();
    }

    public function traduire(string $texte, ?string $source, string $cible): string
    {
        $texte = trim($texte);

        if ($texte === '') {
            return '';
        }

        $source = in_array($source, self::LANGUES, true) ? $source : 'auto';
        $cible = in_array($cible, self::LANGUES, true) ? $cible : 'fr';

        return $this->traducteur->traduire($texte, $source, $cible);
    }
}
