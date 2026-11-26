<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RencontreMembre extends Model
{
    protected $table = 'rencontre_membre';

    protected $primaryKey = 'id_rencontre_membre';

    protected $fillable = [
        'id_rencontre',
        'id_utilisateur',
        'statut',
    ];

    public function rencontre(): BelongsTo
    {
        return $this->belongsTo(Rencontre::class, 'id_rencontre', 'id_rencontre');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
