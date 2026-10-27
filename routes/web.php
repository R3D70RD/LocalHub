<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controller\PublicationController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('publication', PublicationController::class);