<?php

namespace App\Models\Concerns;

use App\Models\Media;
use App\Models\Pays;
use App\Models\Quartier;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Contexte géographique et médias partagés par les entités de contenu.
 */
trait HasGeoContext
{
    public function quartier(): BelongsTo
    {
        return $this->belongsTo(Quartier::class, 'id_quartier', 'id_quartier');
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville', 'id_ville');
    }

    public function pays(): BelongsTo
    {
        return $this->belongsTo(Pays::class, 'id_pays', 'id_pays');
    }

    public function medias(): MorphMany
    {
        return $this->morphMany(Media::class, 'cible')->orderBy('ordre');
    }
}
