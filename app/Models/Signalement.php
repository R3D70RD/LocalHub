<?php

namespace App\Models;

use App\Support\Cibles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Signalement extends Model
{
    protected $table = 'signalement';

    protected $primaryKey = 'id_signalement';

    protected $fillable = [
        'id_utilisateur',
        'cible_type',
        'cible_id',
        'motif',
        'description',
        'statut',
        'id_moderateur',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function moderateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_moderateur', 'id_utilisateur');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ModerationAction::class, 'id_signalement', 'id_signalement');
    }

    public function cible(): ?Model
    {
        return Cibles::resoudre($this->cible_type, (int) $this->cible_id);
    }
}
