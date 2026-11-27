<?php

namespace App\Models;

use App\Enums\Langue;
use App\Enums\PrecisionLocalisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'utilisateur';

    protected $primaryKey = 'id_utilisateur';

    public $timestamps = false;

    public const ROLE_MEMBER = 'member';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_MODERATEUR = 'moderateur';

    protected $fillable = [
        'nom',
        'prenom',
        'courriel',
        'mot_de_passe',
        'telephone',
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

    public function commentaires(): HasMany
    {
        return $this->hasMany(Commentaire::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function outils(): HasMany
    {
        return $this->hasMany(Outil::class, 'id_proprietaire', 'id_utilisateur');
    }

    public function pretsEmprunteur(): HasMany
    {
        return $this->hasMany(Pret::class, 'id_emprunteur', 'id_utilisateur');
    }

    public function pretsProprietaire(): HasMany
    {
        return $this->hasMany(Pret::class, 'id_proprietaire', 'id_utilisateur');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_expediteur', 'id_utilisateur');
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville', 'id_ville');
    }

    public function quartier(): BelongsTo
    {
        return $this->belongsTo(Quartier::class, 'id_quartier', 'id_quartier');
    }

    public function parametre(): HasOne
    {
        return $this->hasOne(ParametreUtilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function readNotifications(): MorphMany
    {
        return $this->notifications()->whereNotNull('lu_at');
    }

    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()->whereNull('lu_at');
    }
}
