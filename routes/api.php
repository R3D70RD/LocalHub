<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentaireController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OutilController;
use App\Http\Controllers\Api\PretController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\ReactionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'statut' => 'ok',
    'version' => 'v1',
    'application' => config('app.name'),
]));

Route::post('/auth/token', [AuthController::class, 'store'])
    ->middleware('throttle:6,1');

// Lecture publique du fil et de la bibliothèque d'outils.
Route::get('/publications', [PublicationController::class, 'index']);
Route::get('/publications/{publication}', [PublicationController::class, 'show']);
Route::get('/outils', [OutilController::class, 'index']);
Route::get('/outils/{outil}', [OutilController::class, 'show']);

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

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'toutLire']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'lire']);
});
