<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservation';
    protected $primaryKey = 'id_reservation';

    protected $fillable = [
        'id_publication',
        'id_demandeur',
        'id_proprietaire',
        'date_debut',
        'date_fin',
        'statut',
        'message',
    ];

    protected function casts(): array {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_demande' => 'datetime',
        ];
    }

    //Remplacement des JOIN
    public function publication(): BelongsTo {
        return $this->belongsTo(Publication::class, 'id_publication', 'id_publication');
    }

    public function demandeur(): BelongsTo {
        return $this->belongsTo(User::class, 'id_demandeur', 'id_utilisateur');
    }

    public function proprietaire(): BelongsTo {
        return $this->belongsTo(User::class, 'id_proprietaire', 'id_utilisateur');
    }
}