<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Activite extends Model
{
    protected $table = 'activite';
    protected $primaryKey = 'id_activite';
    public $timestamps = false;

    protected $fillable = [
        'id_publication',
        'date_heure',
        'lieu',
        'max_participants'
    ];

    protected function casts(): array {
        return ['date_heure' => 'datetime'];
    }

    public function publication(): BelongsTo {
        return $this->belongsTo(Publication::class, 'id_publication', 'id_publication');
    }

    public function participants(): BelongsToMany {
        return $this->belongsToMany(
            User::class, 
            'participation', 
            'id_activite', 
            'id_utilisateur'
        )->withTimestamps();
    }
}