<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffreAnnonce extends Model
{
    protected $table = 'offre_annonce';

    protected $primaryKey = 'id_offre_annonce';

    protected $fillable = [
        'id_annonce',
        'id_utilisateur',
        'montant',
        'message',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
        ];
    }

    public function annonce(): BelongsTo
    {
        return $this->belongsTo(Annonce::class, 'id_annonce', 'id_annonce');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
