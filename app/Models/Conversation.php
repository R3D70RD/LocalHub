<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $table = 'conversation';

    protected $primaryKey = 'id_conversation';

    protected $fillable = [
        'cle',
        'titre',
    ];

    public function membres(): HasMany
    {
        return $this->hasMany(ConversationMembre::class, 'id_conversation', 'id_conversation');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_conversation', 'id_conversation')
            ->orderBy('id_message');
    }

    public function membreDe(int $idUtilisateur): ?ConversationMembre
    {
        return $this->membres()
            ->where('id_utilisateur', $idUtilisateur)
            ->first();
    }
}
