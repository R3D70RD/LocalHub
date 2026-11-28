<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvisPret extends Model
{
    protected $table = 'avis_pret';

    protected $primaryKey = 'id_avis_pret';

    protected $fillable = [
        'id_pret',
        'id_auteur',
        'id_cible',
        'note',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'note' => 'integer',
        ];
    }

    public function pret(): BelongsTo
    {
        return $this->belongsTo(Pret::class, 'id_pret', 'id_pret');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_auteur', 'id_utilisateur');
    }

    public function cible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_cible', 'id_utilisateur');
    }
}
