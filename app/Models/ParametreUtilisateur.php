<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParametreUtilisateur extends Model
{
    protected $table = 'parametre_utilisateur';

    protected $primaryKey = 'id_parametre_utilisateur';

    protected $fillable = [
        'id_utilisateur',
        'portee_defaut',
        'afficher_position',
        'notifications_actives',
        'langue',
    ];

    protected function casts(): array
    {
        return [
            'notifications_actives' => 'boolean',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
