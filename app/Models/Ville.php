<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ville extends Model
{
    protected $table = 'ville';
    protected $primaryKey = 'id_ville';

    protected $fillable = [
        'id_region',
        'nom_fr',
        'nom_en',
        'prefixe_postal',
        'latitude',
        'longitude',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'actif' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'id_region', 'id_region');
    }

    public function quartiers(): HasMany
    {
        return $this->hasMany(Quartier::class, 'id_ville', 'id_ville');
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class, 'id_ville', 'id_ville');
    }
}
