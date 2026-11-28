<?php

namespace App\Models;

use App\Enums\PorteeVisibilite;
use App\Models\Concerns\HasGeoContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evenement extends Model
{
    use HasGeoContext;

    protected $table = 'evenement';

    protected $primaryKey = 'id_evenement';

    protected $fillable = [
        'id_utilisateur',
        'id_categorie',
        'id_quartier',
        'id_ville',
        'id_pays',
        'titre',
        'description',
        'portee',
        'date_debut',
        'date_fin',
        'timezone',
        'lieu',
        'capacite',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'portee' => PorteeVisibilite::class,
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
            'capacite' => 'integer',
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

    public function inscriptions(): HasMany
    {
        return $this->hasMany(InscriptionEvenement::class, 'id_evenement', 'id_evenement');
    }
}
