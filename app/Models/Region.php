<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $table = 'region';

    protected $primaryKey = 'id_region';

    protected $fillable = [
        'id_pays',
        'code',
        'nom_fr',
        'nom_en',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function pays(): BelongsTo
    {
        return $this->belongsTo(Pays::class, 'id_pays', 'id_pays');
    }

    public function villes(): HasMany
    {
        return $this->hasMany(Ville::class, 'id_region', 'id_region');
    }
}
