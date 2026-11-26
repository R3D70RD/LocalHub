<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pays extends Model
{
    protected $table = 'pays';

    protected $primaryKey = 'id_pays';

    protected $fillable = [
        'code',
        'nom_fr',
        'nom_en',
        'devise',
        'indicatif_tel',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class, 'id_pays', 'id_pays');
    }
}
