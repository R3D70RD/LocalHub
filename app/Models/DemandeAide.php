<?php

namespace App\Models;

use App\Enums\PorteeVisibilite;
use App\Models\Concerns\HasGeoContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandeAide extends Model
{
    use HasGeoContext;

    protected $table = 'demande_aide';

    protected $primaryKey = 'id_demande_aide';

    protected $fillable = [
        'id_utilisateur',
        'id_categorie',
        'id_quartier',
        'id_ville',
        'id_pays',
        'titre',
        'description',
        'portee',
        'urgent',
        'date_limite',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'portee' => PorteeVisibilite::class,
            'urgent' => 'boolean',
            'date_limite' => 'date',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_categorie', 'id_categorie');
    }

    public function offres(): HasMany
    {
        return $this->hasMany(OffreAide::class, 'id_demande_aide', 'id_demande_aide');
    }
}
