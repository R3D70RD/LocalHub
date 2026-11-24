<?php

namespace App\Models;

use App\Enums\Langue;
use App\Enums\PrecisionLocalisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'utilisateur';
    protected $primaryKey = 'id_utilisateur';
    public $timestamps = false;

    public const ROLE_MEMBER = 'member';
    public const ROLE_MANAGER = 'manager';

    protected $fillable = [
        'nom',
        'prenom',
        'courriel',
        'mot_de_passe',
        'telephone',
        'ville',
        'locale',
        'id_ville',
        'id_quartier',
        'latitude',
        'longitude',
        'geohash',
        'precision_pref',
        'fiabilite',
        'role',
        'avatar_path',
        'bio',
        'presentation',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    protected function casts(): array
    {
        return [
            'mot_de_passe' => 'hashed',
            'locale' => Langue::class,
            'precision_pref' => PrecisionLocalisation::class,
            'latitude' => 'float',
            'longitude' => 'float',
            'fiabilite' => 'integer',
        ];
    }

    // Tell Laravel to use 'mot_de_passe' instead of 'password' for authentication
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    // Tell Laravel which column holds the password (used by Auth::attempt)
    public function getAuthPasswordName()
    {
        return 'mot_de_passe';
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville', 'id_ville');
    }

    public function quartier(): BelongsTo
    {
        return $this->belongsTo(Quartier::class, 'id_quartier', 'id_quartier');
    }
}
