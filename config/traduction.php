<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Moteur de traduction à la demande
    |--------------------------------------------------------------------------
    |
    | Le contenu est affiché dans la langue de l'auteur. Un lecteur peut
    | demander une traduction ponctuelle dans la langue de l'interface.
    |
    | Drivers disponibles :
    | - « off »           : traduction désactivée (bouton masqué)
    | - « factice »       : traduction simulée (tests, démonstration)
    | - « libretranslate » : service HTTP auto-hébergeable
    |
    */

    'driver' => env('TRADUCTION_DRIVER', 'off'),

    'libretranslate' => [
        'url' => env('LIBRETRANSLATE_URL'),
        'api_key' => env('LIBRETRANSLATE_API_KEY'),
    ],

];
