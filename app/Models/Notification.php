<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Notification applicative stockée dans la table singulière `notification`.
 *
 * On n'utilise pas `DatabaseNotification` de Laravel car celle-ci impose la
 * table anglaise plurielle `notifications` et la clé UUID `id`.
 */
class Notification extends Model
{
    protected $table = 'notification';

    protected $primaryKey = 'id_notification';

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'lu_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'lu_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function estLue(): bool
    {
        return $this->lu_at !== null;
    }

    public function marquerCommeLue(): void
    {
        if (! $this->estLue()) {
            $this->forceFill(['lu_at' => now()])->save();
        }
    }
}
