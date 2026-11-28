<?php

namespace App\Services\Traduction;

use Illuminate\Support\Facades\Http;

/**
 * Driver HTTP compatible avec une instance LibreTranslate auto-hébergée.
 *
 * Aucune clé n'est obligatoire sur une instance locale ; une clé peut être
 * fournie pour les instances protégées.
 */
class TraducteurLibreTranslate implements Traducteur
{
    public function __construct(
        private readonly ?string $url,
        private readonly ?string $apiKey = null,
    ) {}

    public function estDisponible(): bool
    {
        return filled($this->url);
    }

    public function traduire(string $texte, string $source, string $cible): string
    {
        $reponse = Http::timeout(10)
            ->acceptJson()
            ->post(rtrim((string) $this->url, '/').'/translate', array_filter([
                'q' => $texte,
                'source' => $source,
                'target' => $cible,
                'format' => 'text',
                'api_key' => $this->apiKey,
            ], fn ($valeur) => $valeur !== null && $valeur !== ''));

        $reponse->throw();

        return (string) ($reponse->json('translatedText') ?? $texte);
    }
}
