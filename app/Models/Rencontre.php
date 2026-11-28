<?php

namespace App\Models;

use App\Enums\PorteeVisibilite;
use App\Models\Concerns\HasGeoContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rencontre extends Model
{
    use HasGeoContext;

    protected $table = 'rencontre';

    protected $primaryKey = 'id_rencontre';

    protected $fillable = [
        'id_utilisateur',
        'id_categorie',
        'id_quartier',
        'id_ville',
        'id_pays',
        'titre',
        'description',
        'portee',
        'date_heure',
        'lieu',
        'capacite',
        'interets',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'portee' => PorteeVisibilite::class,
            'date_heure' => 'datetime',
            'capacite' => 'integer',
            'interets' => 'array',
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

    public function membres(): HasMany
    {
        return $this->hasMany(RencontreMembre::class, 'id_rencontre', 'id_rencontre');
    }
}
