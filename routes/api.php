<?php

use App\Http\Controllers\Api\AnnonceController;
use App\Http\Controllers\Api\AnnonceMessageController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvisPretController;
use App\Http\Controllers\Api\BlocageController;
use App\Http\Controllers\Api\CommentaireController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\DemandeAideController;
use App\Http\Controllers\Api\EvenementController;
use App\Http\Controllers\Api\FilController;
use App\Http\Controllers\Api\InscriptionEvenementController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\ModerationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OffreAideController;
use App\Http\Controllers\Api\OffreAnnonceController;
use App\Http\Controllers\Api\OutilController;
use App\Http\Controllers\Api\PretController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\ReactionController;
use App\Http\Controllers\Api\RencontreController;
use App\Http\Controllers\Api\RencontreMembreController;
use App\Http\Controllers\Api\SignalementController;
use App\Http\Controllers\Api\SuiviController;
use App\Http\Middleware\EnsureModerateur;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'statut' => 'ok',
    'version' => 'v1',
    'application' => config('app.name'),
]));

Route::post('/auth/token', [AuthController::class, 'store'])
    ->middleware('throttle:6,1');

// Lecture publique du fil et des entités.
Route::get('/fil', [FilController::class, 'index']);
Route::get('/publications', [PublicationController::class, 'index']);
Route::get('/publications/{publication}', [PublicationController::class, 'show']);
Route::get('/outils', [OutilController::class, 'index']);
Route::get('/outils/{outil}', [OutilController::class, 'show']);
Route::get('/demandes-aide', [DemandeAideController::class, 'index']);
Route::get('/demandes-aide/{demande}', [DemandeAideController::class, 'show']);
Route::get('/annonces', [AnnonceController::class, 'index']);
Route::get('/annonces/{annonce}', [AnnonceController::class, 'show']);
Route::get('/evenements', [EvenementController::class, 'index']);
Route::get('/evenements/{evenement}', [EvenementController::class, 'show']);
Route::get('/rencontres', [RencontreController::class, 'index']);
Route::get('/rencontres/{rencontre}', [RencontreController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'destroy']);

    Route::post('/publications', [PublicationController::class, 'store']);
    Route::match(['put', 'patch'], '/publications/{publication}', [PublicationController::class, 'update']);
    Route::delete('/publications/{publication}', [PublicationController::class, 'destroy']);

    Route::post('/publications/{publication}/commentaires', [CommentaireController::class, 'store']);
    Route::post('/publications/{publication}/reactions', [ReactionController::class, 'toggle']);

    Route::delete('/medias/{media}', [MediaController::class, 'destroy']);

    Route::post('/outils', [OutilController::class, 'store']);
    Route::match(['put', 'patch'], '/outils/{outil}', [OutilController::class, 'update']);
    Route::delete('/outils/{outil}', [OutilController::class, 'destroy']);

    Route::get('/prets', [PretController::class, 'index']);
    Route::post('/outils/{outil}/prets', [PretController::class, 'store']);
    Route::get('/prets/{pret}', [PretController::class, 'show']);
    Route::post('/prets/{pret}/transition', [PretController::class, 'transition']);
    Route::get('/prets/{pret}/messages', [PretController::class, 'messages']);
    Route::post('/prets/{pret}/messages', [PretController::class, 'storeMessage']);

    // Demandes d'aide
    Route::post('/demandes-aide', [DemandeAideController::class, 'store']);
    Route::match(['put', 'patch'], '/demandes-aide/{demande}', [DemandeAideController::class, 'update']);
    Route::delete('/demandes-aide/{demande}', [DemandeAideController::class, 'destroy']);
    Route::post('/demandes-aide/{demande}/offres', [OffreAideController::class, 'store']);

    // Marché
    Route::post('/annonces', [AnnonceController::class, 'store']);
    Route::match(['put', 'patch'], '/annonces/{annonce}', [AnnonceController::class, 'update']);
    Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy']);
    Route::post('/annonces/{annonce}/offres', [OffreAnnonceController::class, 'store']);
    Route::post('/annonces/{annonce}/messages', [AnnonceMessageController::class, 'store']);

    // Événements
    Route::post('/evenements', [EvenementController::class, 'store']);
    Route::match(['put', 'patch'], '/evenements/{evenement}', [EvenementController::class, 'update']);
    Route::delete('/evenements/{evenement}', [EvenementController::class, 'destroy']);
    Route::post('/evenements/{evenement}/inscriptions', [InscriptionEvenementController::class, 'store']);
    Route::delete('/evenements/{evenement}/inscriptions', [InscriptionEvenementController::class, 'destroy']);

    // Rencontres
    Route::post('/rencontres', [RencontreController::class, 'store']);
    Route::match(['put', 'patch'], '/rencontres/{rencontre}', [RencontreController::class, 'update']);
    Route::delete('/rencontres/{rencontre}', [RencontreController::class, 'destroy']);
    Route::post('/rencontres/{rencontre}/membres', [RencontreMembreController::class, 'store']);
    Route::delete('/rencontres/{rencontre}/membres', [RencontreMembreController::class, 'destroy']);

    // Confiance / sécurité
    Route::post('/signalements', [SignalementController::class, 'store']);
    Route::get('/moderation', [ModerationController::class, 'index'])->middleware(EnsureModerateur::class);
    Route::post('/moderation/{signalement}/traiter', [ModerationController::class, 'traiter'])->middleware(EnsureModerateur::class);
    Route::post('/utilisateurs/{utilisateur}/bloquer', [BlocageController::class, 'store']);
    Route::delete('/utilisateurs/{utilisateur}/bloquer', [BlocageController::class, 'destroy']);
    Route::post('/prets/{pret}/avis', [AvisPretController::class, 'store']);

    // Messagerie privée
    Route::get('/messages', [ConversationController::class, 'index']);
    Route::post('/messages', [ConversationController::class, 'demarrer']);
    Route::get('/messages/{conversation}', [ConversationController::class, 'show']);
    Route::post('/messages/{conversation}', [ConversationController::class, 'store']);

    // Suivi de publication
    Route::post('/publications/{publication}/suivre', [SuiviController::class, 'store']);
    Route::delete('/publications/{publication}/suivre', [SuiviController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'toutLire']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'lire']);
});
