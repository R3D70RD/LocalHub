<?php

namespace App\Models;

use App\Enums\Langue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commentaire extends Model
{
    protected $table = 'commentaire';

    protected $primaryKey = 'id_commentaire';

    protected $fillable = [
        'id_publication',
        'id_utilisateur',
        'contenu',
        'lang',
    ];

    protected function casts(): array
    {
        return [
            'lang' => Langue::class,
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
