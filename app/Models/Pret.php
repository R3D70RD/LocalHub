<?php

namespace App\Models;

use App\Enums\StatutPret;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pret extends Model
{
    protected $table = 'pret';

    protected $primaryKey = 'id_pret';

    protected $fillable = [
        'id_outil',
        'id_emprunteur',
        'id_proprietaire',
        'statut',
        'date_debut',
        'date_fin',
        'date_demande',
        'date_retour_effectif',
        'message',
        'version',
        'cle_active',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutPret::class,
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_demande' => 'datetime',
            'date_retour_effectif' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function outil(): BelongsTo
    {
        return $this->belongsTo(Outil::class, 'id_outil', 'id_outil');
    }

    public function emprunteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_emprunteur', 'id_utilisateur');
    }

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_proprietaire', 'id_utilisateur');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_pret', 'id_pret');
    }

    public function scopePourUtilisateur($query, int $idUtilisateur)
    {
        return $query->where(function ($q) use ($idUtilisateur) {
            $q->where('id_emprunteur', $idUtilisateur)
                ->orWhere('id_proprietaire', $idUtilisateur);
        });
    }
}
