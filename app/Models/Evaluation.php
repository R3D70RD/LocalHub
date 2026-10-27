<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $table = 'evaluation';
    protected $primaryKey = 'id_evaluation';
    const CREATED_AT = 'date_evaluation';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_evaluateur',
        'id_evalue',
        'id_publication',
        'note',
        'commentaire'
    ];

    public function evaluateur(): BelongsTo {
        return $this->belongsTo(User::class, 'id_evaluateur', 'id_utilisateur');
    }

    public function evalue(): BelongsTo {
        return $this->belongsTo(User::class, 'id_evalue', 'id_utilisateur');
    }

    public function publication(): BelongsTo {
        return $this->belongsTo(Publication::class, 'id_publication', 'id_publication');
    }
}