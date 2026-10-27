<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Demande extends Model
{
    protected $table = 'demande';
    protected $primaryKey = 'id_demande';
    const CREATED_AT = 'date_demande';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_publication',
        'id_demandeur',
        'message',
        'statut'
    ];

    public function publication(): BelongsTo {
        return $this->belongsTo(Publication::class, 'id_publication', 'id_publication');
    }

    public function demandeur(): BelongsTo {
        return $this->belongsTo(User::class, 'id_demandeur', 'id_utilisateur');
    }
}