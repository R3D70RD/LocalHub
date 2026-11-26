<?php

namespace App\Models;

use App\Enums\TypeReaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $table = 'reaction';

    protected $primaryKey = 'id_reaction';

    protected $fillable = [
        'id_publication',
        'id_utilisateur',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeReaction::class,
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'id_publication', 'id_publication');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
