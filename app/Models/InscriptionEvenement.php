<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscriptionEvenement extends Model
{
    protected $table = 'inscription_evenement';

    protected $primaryKey = 'id_inscription_evenement';

    protected $fillable = [
        'id_evenement',
        'id_utilisateur',
        'statut',
    ];

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'id_evenement', 'id_evenement');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
