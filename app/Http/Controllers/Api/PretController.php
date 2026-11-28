<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RepondAuxErreursPret;
use App\Http\Controllers\Controller;
use App\Http\Requests\DemanderPretRequest;
use App\Http\Requests\TransitionPretRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\PretResource;
use App\Models\Outil;
use App\Models\Pret;
use App\Services\IdempotencyService;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class PretController extends Controller
{
    use RepondAuxErreursPret;

    public function __construct(
        private readonly LoanService $prets,
        private readonly IdempotencyService $idempotence,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $prets = Pret::query()
            ->pourUtilisateur($request->user()->id_utilisateur)
            ->with(['outil', 'proprietaire', 'emprunteur'])
            ->orderByDesc('id_pret')
            ->paginate((int) $request->query('par_page', 15));

        return PretResource::collection($prets);
    }

    public function store(DemanderPretRequest $request, Outil $outil): JsonResponse
    {
        try {
            $resultat = $this->idempotence->executer(
                $request->header('Idempotency-Key'),
                $request->user(),
                'api.prets.store.'.$outil->id_outil,
                'POST',
                function () use ($request, $outil) {
                    $pret = $this->prets->demander($request->user(), $outil, $request->validated());
                    $pret->load(['outil', 'proprietaire', 'emprunteur']);

                    return [201, ['data' => (new PretResource($pret))->resolve()], $pret->id_pret];
                },
            );
        } catch (Throwable $exception) {
            return $this->reponseErreur($exception);
        }

        return response()->json($resultat['reponse'], $resultat['statut'])
            ->header('Idempotent-Replayed', $resultat['rejoue'] ? 'true' : 'false');
    }

    public function show(Pret $pret): PretResource
    {
        $this->prets->verifierParticipant($pret, request()->user());

        $pret->load(['outil', 'proprietaire', 'emprunteur', 'messages.expediteur']);

        return new PretResource($pret);
    }

    public function transition(TransitionPretRequest $request, Pret $pret): JsonResponse
    {
        try {
            $resultat = $this->idempotence->executer(
                $request->header('Idempotency-Key'),
                $request->user(),
                'api.prets.transition.'.$pret->id_pret,
                'POST',
                function () use ($request, $pret) {
                    $misAJour = $this->prets->transition(
                        $pret,
                        $request->validated('action'),
                        $request->user(),
                        $request->validated('version'),
                    );

                    $misAJour->load(['outil', 'proprietaire', 'emprunteur']);

                    return [200, ['data' => (new PretResource($misAJour))->resolve()], $misAJour->id_pret];
                },
            );
        } catch (Throwable $exception) {
            return $this->reponseErreur($exception);
        }

        return response()->json($resultat['reponse'], $resultat['statut'])
            ->header('Idempotent-Replayed', $resultat['rejoue'] ? 'true' : 'false');
    }

    public function messages(Pret $pret): AnonymousResourceCollection
    {
        $this->prets->verifierParticipant($pret, request()->user());

        return MessageResource::collection(
            $pret->messages()->with('expediteur')->orderBy('id_message')->get()
        );
    }

    public function storeMessage(Request $request, Pret $pret): JsonResponse
    {
        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $message = $this->prets->envoyerMessage(
                $pret,
                $request->user(),
                $donnees['contenu'],
            );
        } catch (Throwable $exception) {
            return $this->reponseErreur($exception);
        }

        $message->load('expediteur');

        return (new MessageResource($message))->response()->setStatusCode(201);
    }
}
