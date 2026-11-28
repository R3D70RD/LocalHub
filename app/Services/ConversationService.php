<?php

namespace App\Services;

use App\Exceptions\ActionNonAutoriseeException;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Messagerie privée 1-à-1, indépendante des prêts et des annonces.
 */
class ConversationService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function demarrer(User $initiateur, User $destinataire): Conversation
    {
        if ((int) $initiateur->id_utilisateur === (int) $destinataire->id_utilisateur) {
            throw ValidationException::withMessages([
                'destinataire' => __('interface.conversation_self'),
            ]);
        }

        $cle = $this->cle($initiateur, $destinataire);

        return DB::transaction(function () use ($initiateur, $destinataire, $cle) {
            $conversation = Conversation::firstOrCreate(['cle' => $cle]);

            if ($conversation->wasRecentlyCreated) {
                $conversation->membres()->createMany([
                    ['id_utilisateur' => $initiateur->id_utilisateur],
                    ['id_utilisateur' => $destinataire->id_utilisateur],
                ]);
            }

            return $conversation;
        });
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function listePour(User $utilisateur): Collection
    {
        return Conversation::query()
            ->whereHas('membres', fn ($q) => $q->where('id_utilisateur', $utilisateur->id_utilisateur))
            ->with(['membres.utilisateur', 'messages.expediteur'])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function envoyer(Conversation $conversation, User $expediteur, string $contenu): Message
    {
        $membre = $conversation->membreDe($expediteur->id_utilisateur);

        if ($membre === null) {
            throw new ActionNonAutoriseeException('Vous ne participez pas à cette conversation.');
        }

        $message = $conversation->messages()->create([
            'id_expediteur' => $expediteur->id_utilisateur,
            'contenu' => $contenu,
        ]);

        $conversation->touch();
        $membre->forceFill(['dernier_lu_at' => now()])->save();

        foreach ($conversation->membres()->where('id_utilisateur', '!=', $expediteur->id_utilisateur)->with('utilisateur')->get() as $autre) {
            if ($autre->utilisateur !== null) {
                $this->notifications->envoyer($autre->utilisateur, 'message.nouveau', [
                    'id_conversation' => $conversation->id_conversation,
                    'apercu' => mb_substr($contenu, 0, 120),
                ]);
            }
        }

        return $message;
    }

    public function marquerLu(Conversation $conversation, User $utilisateur): void
    {
        $conversation->membres()
            ->where('id_utilisateur', $utilisateur->id_utilisateur)
            ->update(['dernier_lu_at' => now()]);
    }

    public function nonLus(Conversation $conversation, User $utilisateur): int
    {
        $membre = $conversation->membreDe($utilisateur->id_utilisateur);

        if ($membre === null) {
            return 0;
        }

        return $conversation->messages()
            ->where('id_expediteur', '!=', $utilisateur->id_utilisateur)
            ->when($membre->dernier_lu_at, fn ($q, $date) => $q->where('created_at', '>', $date))
            ->count();
    }

    private function cle(User $a, User $b): string
    {
        $ids = [(int) $a->id_utilisateur, (int) $b->id_utilisateur];
        sort($ids);

        return $ids[0].':'.$ids[1];
    }
}
