<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationAction extends Model
{
    protected $table = 'moderation_action';

    protected $primaryKey = 'id_moderation_action';

    protected $fillable = [
        'id_signalement',
        'id_moderateur',
        'action',
        'note',
    ];

    public function signalement(): BelongsTo
    {
        return $this->belongsTo(Signalement::class, 'id_signalement', 'id_signalement');
    }

    public function moderateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_moderateur', 'id_utilisateur');
    }
}
