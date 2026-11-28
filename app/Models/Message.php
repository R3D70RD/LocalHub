<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'message';

    protected $primaryKey = 'id_message';

    protected $fillable = [
        'id_pret',
        'id_annonce',
        'id_expediteur',
        'contenu',
        'lu_at',
    ];

    protected function casts(): array
    {
        return [
            'lu_at' => 'datetime',
        ];
    }

    public function pret(): BelongsTo
    {
        return $this->belongsTo(Pret::class, 'id_pret', 'id_pret');
    }

    public function annonce(): BelongsTo
    {
        return $this->belongsTo(Annonce::class, 'id_annonce', 'id_annonce');
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_expediteur', 'id_utilisateur');
    }
}
