<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMembre extends Model
{
    protected $table = 'conversation_membre';

    protected $primaryKey = 'id_conversation_membre';

    protected $fillable = [
        'id_conversation',
        'id_utilisateur',
        'dernier_lu_at',
    ];

    protected function casts(): array
    {
        return [
            'dernier_lu_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'id_conversation', 'id_conversation');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }
}
