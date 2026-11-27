<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Idempotence extends Model
{
    protected $table = 'idempotence';

    protected $primaryKey = 'id_idempotence';

    protected $fillable = [
        'id_utilisateur',
        'cle',
        'route',
        'methode',
        'statut',
        'reponse',
    ];

    protected function casts(): array
    {
        return [
            'statut' => 'integer',
            'reponse' => 'array',
        ];
    }
}
