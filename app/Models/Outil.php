<?php

namespace App\Models;

use App\Enums\Langue;
use App\Enums\PorteeVisibilite;
use App\Enums\StatutPret;
use App\Models\Concerns\HasGeoContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outil extends Model
{
    use HasGeoContext;

    protected $table = 'outil';

    protected $primaryKey = 'id_outil';

    protected $fillable = [
        'id_proprietaire',
        'id_categorie',
        'id_quartier',
        'id_ville',
        'id_pays',
        'titre',
        'description',
        'lang',
        'portee',
        'etat',
        'caution',
        'disponible',
        'statut',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'lang' => Langue::class,
            'portee' => PorteeVisibilite::class,
            'caution' => 'decimal:2',
            'disponible' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_proprietaire', 'id_utilisateur');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_categorie', 'id_categorie');
    }

    public function prets(): HasMany
    {
        return $this->hasMany(Pret::class, 'id_outil', 'id_outil');
    }

    public function pretActif(): ?Pret
    {
        return $this->prets()
            ->whereIn('statut', [
                StatutPret::DEMANDE->value,
                StatutPret::APPROUVE->value,
                StatutPret::EMPRUNTE->value,
                StatutPret::EN_RETARD->value,
                StatutPret::LITIGE->value,
            ])
            ->first();
    }
}
