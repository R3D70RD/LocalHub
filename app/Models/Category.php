<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categorie';

    protected $primaryKey = 'id_categorie';

    public $timestamps = false;

    protected $fillable = ['nom', 'slug', 'description', 'actif'];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'id_categorie', 'id_categorie');
    }

    public function outils(): HasMany
    {
        return $this->hasMany(Outil::class, 'id_categorie', 'id_categorie');
    }

    public function annonces(): HasMany
    {
        return $this->hasMany(Annonce::class, 'id_categorie', 'id_categorie');
    }
}
