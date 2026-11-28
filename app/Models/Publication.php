<?php

namespace App\Models;

use App\Enums\PorteeVisibilite;
use App\Models\Concerns\HasGeoContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use HasFactory, HasGeoContext;

    protected $table = 'publication';

    protected $primaryKey = 'id_publication';

    protected $fillable = [
        'titre',
        'description',
        'prix',
        'id_quartier',
        'id_ville',
        'id_pays',
        'date_heure',
        'date_creation',
        'id_utilisateur',
        'id_categorie',
        'portee',
        'image_path',
        'statut',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'date_creation' => 'datetime',
            'date_heure' => 'datetime',
            'prix' => 'decimal:2',
            'portee' => PorteeVisibilite::class,
            'version' => 'integer',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_categorie', 'id_categorie');
    }

    public function commentaires(): HasMany
    {
        return $this->hasMany(Commentaire::class, 'id_publication', 'id_publication')
            ->latest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'id_publication', 'id_publication');
    }

    public function scopeActive($query)
    {
        return $query->where('statut', 'active')
            ->orderByDesc('date_creation');
    }

    public function scopeInCategory($query, int $idCategorie)
    {
        return $query->where('id_categorie', $idCategorie)
            ->where('statut', 'active')
            ->orderByDesc('date_creation');
    }
}
