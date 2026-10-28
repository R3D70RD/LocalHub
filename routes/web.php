<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\RecherchePublicationController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('publications', PublicationController::class);

Route::get('/recherche/publications', RecherchePublicationController::class)
    ->name('recherche.publications');
