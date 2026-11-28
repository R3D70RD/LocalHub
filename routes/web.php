<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\AnnonceMessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisPretController;
use App\Http\Controllers\BlocageController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DemandeAideController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\FilController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\InscriptionEvenementController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OffreAideController;
use App\Http\Controllers\OffreAnnonceController;
use App\Http\Controllers\OutilController;
use App\Http\Controllers\PretController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\RecherchePublicationController;
use App\Http\Controllers\RencontreController;
use App\Http\Controllers\RencontreMembreController;
use App\Http\Controllers\SignalementController;
use App\Http\Controllers\SuiviController;
use App\Http\Controllers\TraductionController;
use App\Http\Middleware\EnsureManager;
use App\Http\Middleware\EnsureModerateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::resource('publications', PublicationController::class)
    ->only(['index', 'show'])
    ->whereNumber('publication');

Route::resource('publications', PublicationController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
    ->middleware('auth');

Route::post('/publications/{publication}/commentaires', [CommentaireController::class, 'store'])
    ->middleware('auth')->name('publications.commentaires.store');

Route::post('/publications/{publication}/reactions', [CommentaireController::class, 'toggleReaction'])
    ->middleware('auth')->name('publications.reactions.toggle');

Route::post('/publications/{publication}/traduire', [TraductionController::class, 'traduirePublication'])
    ->middleware(['auth', 'throttle:30,1'])->name('publications.traduire');

Route::delete('/medias/{media}', [MediaController::class, 'destroy'])
    ->middleware('auth')->name('medias.destroy');

// Bibliothèque d'outils et prêts
Route::resource('outils', OutilController::class)
    ->only(['index', 'show'])
    ->whereNumber('outil');

Route::resource('outils', OutilController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
    ->middleware('auth');

Route::post('/outils/{outil}/prets', [PretController::class, 'store'])
    ->middleware('auth')->name('outils.prets.store');

Route::get('/prets', [PretController::class, 'index'])
    ->middleware('auth')->name('prets.index');

Route::get('/prets/{pret}', [PretController::class, 'show'])
    ->middleware('auth')->name('prets.show');

Route::post('/prets/{pret}/transition', [PretController::class, 'transition'])
    ->middleware('auth')->name('prets.transition');

Route::post('/prets/{pret}/messages', [PretController::class, 'storeMessage'])
    ->middleware('auth')->name('prets.messages.store');

// Demandes d'aide
Route::resource('demandes-aide', DemandeAideController::class)
    ->only(['index', 'show'])
    ->parameters(['demandes-aide' => 'demande'])
    ->whereNumber('demande');

Route::resource('demandes-aide', DemandeAideController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
    ->parameters(['demandes-aide' => 'demande'])
    ->middleware('auth');

Route::post('/demandes-aide/{demande}/offres', [OffreAideController::class, 'store'])
    ->middleware('auth')->name('demandes-aide.offres.store');

// Marché
Route::resource('annonces', AnnonceController::class)
    ->only(['index', 'show'])
    ->parameters(['annonces' => 'annonce'])
    ->whereNumber('annonce');

Route::resource('annonces', AnnonceController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
    ->parameters(['annonces' => 'annonce'])
    ->middleware('auth');

Route::post('/annonces/{annonce}/offres', [OffreAnnonceController::class, 'store'])
    ->middleware('auth')->name('annonces.offres.store');

Route::post('/annonces/{annonce}/messages', [AnnonceMessageController::class, 'store'])
    ->middleware('auth')->name('annonces.messages.store');

// Événements
Route::resource('evenements', EvenementController::class)
    ->only(['index', 'show'])
    ->parameters(['evenements' => 'evenement'])
    ->whereNumber('evenement');

Route::resource('evenements', EvenementController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
    ->parameters(['evenements' => 'evenement'])
    ->middleware('auth');

Route::post('/evenements/{evenement}/inscriptions', [InscriptionEvenementController::class, 'store'])
    ->middleware('auth')->name('evenements.inscriptions.store');

Route::delete('/evenements/{evenement}/inscriptions', [InscriptionEvenementController::class, 'destroy'])
    ->middleware('auth')->name('evenements.inscriptions.destroy');

// Rencontres
Route::resource('rencontres', RencontreController::class)
    ->only(['index', 'show'])
    ->parameters(['rencontres' => 'rencontre'])
    ->whereNumber('rencontre');

Route::resource('rencontres', RencontreController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
    ->parameters(['rencontres' => 'rencontre'])
    ->middleware('auth');

Route::post('/rencontres/{rencontre}/membres', [RencontreMembreController::class, 'store'])
    ->middleware('auth')->name('rencontres.membres.store');

Route::delete('/rencontres/{rencontre}/membres', [RencontreMembreController::class, 'destroy'])
    ->middleware('auth')->name('rencontres.membres.destroy');

// Confiance / sécurité
Route::post('/signalements', [SignalementController::class, 'store'])
    ->middleware('auth')->name('signalements.store');

Route::get('/moderation', [ModerationController::class, 'index'])
    ->middleware(['auth', EnsureModerateur::class])->name('moderation.index');

Route::post('/moderation/{signalement}/traiter', [ModerationController::class, 'traiter'])
    ->middleware(['auth', EnsureModerateur::class])->name('moderation.traiter');

Route::post('/utilisateurs/{utilisateur}/bloquer', [BlocageController::class, 'store'])
    ->middleware('auth')->name('blocages.store');

Route::delete('/utilisateurs/{utilisateur}/bloquer', [BlocageController::class, 'destroy'])
    ->middleware('auth')->name('blocages.destroy');

Route::post('/prets/{pret}/avis', [AvisPretController::class, 'store'])
    ->middleware('auth')->name('prets.avis.store');

// Messagerie privée
Route::get('/messages', [ConversationController::class, 'index'])
    ->middleware('auth')->name('conversations.index');

Route::post('/messages', [ConversationController::class, 'demarrer'])
    ->middleware('auth')->name('conversations.demarrer');

Route::get('/messages/{conversation}', [ConversationController::class, 'show'])
    ->middleware('auth')->name('conversations.show');

Route::post('/messages/{conversation}', [ConversationController::class, 'store'])
    ->middleware('auth')->name('conversations.messages.store');

// Suivi de publication
Route::post('/publications/{publication}/suivre', [SuiviController::class, 'store'])
    ->middleware('auth')->name('publications.suivre');

Route::delete('/publications/{publication}/suivre', [SuiviController::class, 'destroy'])
    ->middleware('auth')->name('publications.ne-plus-suivre');

// Notifications
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth')->name('notifications.index');

Route::post('/notifications/read-all', [NotificationController::class, 'toutLire'])
    ->middleware('auth')->name('notifications.read-all');

Route::post('/notifications/{notification}/read', [NotificationController::class, 'lire'])
    ->middleware('auth')->name('notifications.read');

Route::get('/recherche/publications', RecherchePublicationController::class)
    ->name('recherche.publications');

Route::post('/langue', function (Request $request) {
    $data = $request->validate([
        'locale' => ['required', Rule::in(['fr', 'en'])],
    ]);

    $request->session()->put('locale', $data['locale']);

    return back();
})->name('locale.update');

// Sign up
Route::get('/inscription', [InscriptionController::class, 'create'])
    ->middleware('guest')->name('register');

Route::post('/inscription', [InscriptionController::class, 'store'])
    ->middleware('guest')->name('register.store');

// Sign in
Route::get('/connexion', [AuthController::class, 'create'])
    ->middleware('guest')->name('login');

Route::post('/connexion', [AuthController::class, 'login'])
    ->middleware(['guest', 'throttle:6,1'])->name('login.store');

Route::post('/deconnexion', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

// compte

// Fil unifié (accueil)
Route::get('/', [FilController::class, 'index'])->name('home');

Route::get('/compte', [CompteController::class, 'show'])
    ->middleware('auth')->name('account.show');

Route::put('/compte/mot-de-passe', [CompteController::class, 'updatePassword'])
    ->middleware('auth')->name('account.password.update');

Route::delete('/compte', [CompteController::class, 'destroy'])
    ->middleware('auth')->name('account.destroy');

// admin
Route::middleware(['auth', EnsureManager::class])->group(function () {
    Route::get('/administration', [AdminController::class, 'index'])
        ->name('admin.index');
    Route::delete('/administration/images/{publication}', [AdminController::class, 'destroyImage'])
        ->name('admin.images.destroy');
});
