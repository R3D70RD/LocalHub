<?php

namespace App\Console\Commands;

use App\Models\Quartier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImporterPolygonesQuartiers extends Command
{
    protected $signature = 'quartiers:importer-polygones
                            {fichier : Chemin vers un fichier GeoJSON (FeatureCollection)}';

    protected $description = 'Importe les limites officielles de quartier (StatCan / municipalité) dans quartier.polygon';

    public function handle(): int
    {
        $chemin = (string) $this->argument('fichier');

        if (! File::exists($chemin)) {
            $this->error("Fichier introuvable : {$chemin}");

            return self::FAILURE;
        }

        $contenu = json_decode(File::get($chemin), true);

        if (! is_array($contenu) || ($contenu['type'] ?? null) !== 'FeatureCollection') {
            $this->error('Le fichier doit être une FeatureCollection GeoJSON.');

            return self::FAILURE;
        }

        $importes = 0;
        $ignores = 0;

        foreach ($contenu['features'] ?? [] as $feature) {
            $proprietes = $feature['properties'] ?? [];
            $nom = $proprietes['nom_fr']
                ?? $proprietes['nom']
                ?? $proprietes['NOM']
                ?? $proprietes['name']
                ?? null;
            $geometrie = $feature['geometry'] ?? null;

            if (! is_string($nom) || $geometrie === null) {
                $ignores++;

                continue;
            }

            $quartier = Quartier::query()
                ->where('nom_fr', $nom)
                ->orWhere('nom_en', $nom)
                ->first();

            if ($quartier === null) {
                $ignores++;

                continue;
            }

            $quartier->forceFill(['polygon' => $geometrie])->save();
            $importes++;
        }

        $this->info("Polygones importés : {$importes} ; ignorés : {$ignores}.");

        return self::SUCCESS;
    }
}
