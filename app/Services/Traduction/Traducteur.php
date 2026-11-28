<?php

namespace App\Services\Traduction;

/**
 * Contrat d'un moteur de traduction à la demande.
 *
 * La traduction n'est jamais persistée en base : MySQL reste la source de
 * vérité du contenu. Le résultat est renvoyé au client, qui le met en cache
 * dans son navigateur.
 */
interface Traducteur
{
    /**
     * Indique si un fournisseur de traduction est réellement configuré.
     */
    public function estDisponible(): bool;

    /**
     * Traduit un texte. `$source` peut valoir « auto » pour laisser le
     * fournisseur détecter la langue d'origine.
     */
    public function traduire(string $texte, string $source, string $cible): string;
}
