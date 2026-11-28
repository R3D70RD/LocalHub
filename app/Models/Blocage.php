<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blocage extends Model
{
    protected $table = 'blocage';

    protected $primaryKey = 'id_blocage';

    protected $fillable = [
        'id_bloqueur',
        'id_bloque',
    ];

    public function bloqueur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_bloqueur', 'id_utilisateur');
    }

    public function bloque(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_bloque', 'id_utilisateur');
    }
}
