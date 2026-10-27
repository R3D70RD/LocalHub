<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'utilisateur';
    protected $primaryKey = 'id_utilisateur';
    public $timestamps = false; 

    protected $fillable = [
        'nom',
        'prenom',
        'courriel',
        'mot_de_passe',
        'ville',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    // Tell Laravel to use 'mot_de_passe' instead of 'password' for authentication
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function publications(): HasMany {
        return $this->hasMany(Publication::class, 'id_utilisateur', 'id_utilisateur');
    }
}