<?php

namespace App\Providers;

use App\Services\Traduction\Traducteur;
use App\Services\Traduction\TraducteurFactice;
use App\Services\Traduction\TraducteurLibreTranslate;
use App\Services\Traduction\TraducteurOff;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Traducteur::class, function () {
            return match (config('traduction.driver')) {
                'factice' => new TraducteurFactice,
                'libretranslate' => new TraducteurLibreTranslate(
                    config('traduction.libretranslate.url'),
                    config('traduction.libretranslate.api_key'),
                ),
                default => new TraducteurOff,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
