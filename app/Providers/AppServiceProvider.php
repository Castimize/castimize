<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        if ($this->app->environment('local')) {
            Mail::alwaysTo('matthijs.bon1@gmail.com');
        }

        if ($this->app->environment('staging')) {
            Mail::alwaysTo('test@castimize.com');
        }
    }
}
