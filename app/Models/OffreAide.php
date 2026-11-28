<?php

namespace App\Models;

use App\Enums\PorteeVisibilite;
use App\Models\Concerns\HasGeoContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffreAide extends Model
{
    use HasGeoContext;

    protected $table = 'offre_aide';

    protected $primaryKey = 'id_offre_aide';

    protected $fillable = [
        'id_demande_aide',
        'id_utilisateur',
        'id_quartier',
        'id_ville',
        'id_pays',
        'titre',
        'description',
        'portee',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'portee' => PorteeVisibilite::class,
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeAide::class, 'id_demande_aide', 'id_demande_aide');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
