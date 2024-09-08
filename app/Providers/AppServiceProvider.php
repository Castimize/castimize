<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shippo;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Shippo::setApiKey($this->app['config']['services.shippo.key']);
    }
}
