<?php

namespace App\Models;

use App\Enums\Langue;
use App\Enums\PorteeVisibilite;
use App\Models\Concerns\HasGeoContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Annonce extends Model
{
    use HasGeoContext;

    protected $table = 'annonce';

    protected $primaryKey = 'id_annonce';

    protected $fillable = [
        'id_utilisateur',
        'id_categorie',
        'id_quartier',
        'id_ville',
        'id_pays',
        'titre',
        'description',
        'lang',
        'portee',
        'type_annonce',
        'prix',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'lang' => Langue::class,
            'portee' => PorteeVisibilite::class,
            'prix' => 'decimal:2',
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
        return $this->hasMany(OffreAnnonce::class, 'id_annonce', 'id_annonce');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_annonce', 'id_annonce');
    }
}
