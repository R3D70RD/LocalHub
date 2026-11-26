<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quartier extends Model
{
    protected $table = 'quartier';

    protected $primaryKey = 'id_quartier';

    protected $fillable = [
        'id_ville',
        'nom_fr',
        'nom_en',
        'centroid_lat',
        'centroid_lng',
        'geohash',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'centroid_lat' => 'float',
            'centroid_lng' => 'float',
            'actif' => 'boolean',
        ];
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville', 'id_ville');
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class, 'id_quartier', 'id_quartier');
    }
}
