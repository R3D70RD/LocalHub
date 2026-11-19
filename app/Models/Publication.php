<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use HasFactory;

    protected $table = 'publication';
    protected $primaryKey = 'id_publication';

    protected $fillable = [
        'titre',
        'description',
        'prix',
        'ville',
        'date_heure',
        'date_creation',
        'id_utilisateur',
        'id_categorie',
        'image_path',
        'statut',
    ];

    protected function casts(): array {
        return [
            'date_creation' => 'datetime',
            'date_heure' => 'datetime',
            'prix' => 'decimal:2',
        ];
    }

    public function utilisateur(): BelongsTo {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class, 'id_categorie', 'id_categorie');
    }

    public function scopeActive($query)
    {
        return $query->where('statut', 'active')
                     ->with(['utilisateur', 'category']) 
                     ->orderBy('date_creation', 'desc');
    }

    public function scopeInCategory($query, int $idCategorie)
    {
        return $query->where('id_categorie', $idCategorie)
                     ->where('statut', 'active')
                     ->with(['utilisateur', 'category'])
                     ->orderBy('date_creation', 'desc');
    }

    public function reservations(): HasMany {
        return $this->hasMany(Reservation::class, 'id_publication', 'id_publication');
    }
}
