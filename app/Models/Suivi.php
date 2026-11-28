<?php

namespace App\Models;

use App\Support\Cibles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suivi extends Model
{
    protected $table = 'suivi';

    protected $primaryKey = 'id_suivi';

    protected $fillable = [
        'id_utilisateur',
        'cible_type',
        'cible_id',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function cible(): ?Model
    {
        return Cibles::resoudre($this->cible_type, (int) $this->cible_id);
    }
}
