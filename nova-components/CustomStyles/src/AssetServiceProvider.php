<?php

namespace Castimize\CustomStyles;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class AssetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('custom-styles', __DIR__ . '/../dist/js/asset.js');
            Nova::style('custom-styles', __DIR__ . '/../dist/css/asset.css');
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }
}
