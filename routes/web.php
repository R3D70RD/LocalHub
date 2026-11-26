<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OutilController;
use App\Http\Controllers\PretController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\RecherchePublicationController;
use App\Http\Middleware\EnsureManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::resource('publications', PublicationController::class)
    ->only(['index', 'show']);

Route::resource('publications', PublicationController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy'])
    ->middleware('auth');

Route::post('/publications/{publication}/commentaires', [CommentaireController::class, 'store'])
    ->middleware('auth')->name('publications.commentaires.store');

Route::post('/publications/{publication}/reactions', [CommentaireController::class, 'toggleReaction'])
    ->middleware('auth')->name('publications.reactions.toggle');

Route::delete('/medias/{media}', [MediaController::class, 'destroy'])
    ->middleware('auth')->name('medias.destroy');

// Bibliothèque d'outils et prêts
Route::resource('outils', OutilController::class)
    ->only(['index', 'show']);

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

Route::get('/', [PublicationController::class, 'index'])->name('home');

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
