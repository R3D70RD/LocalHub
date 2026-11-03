<?php

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\RecherchePublicationController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\AuthController;

Route::redirect('/', '/publications');

Route::resource('publications', PublicationController::class);

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
