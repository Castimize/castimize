<?php

namespace Castimize\ColumnToggler;

use Castimize\ColumnToggler\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Field::macro('hideByDefault', function ($hiddenByDefault = true) {
            return $this->withMeta([
                'columnToggleVisible' => !$hiddenByDefault,
            ]);
        });

        Nova::serving(function (ServingNova $event) {
            Nova::provideToScript([
                'column_toggler' => config('nova.vendors.column_toggler'),
            ]);

            Nova::script('column-toggler', __DIR__ . '/../dist/js/tool.js');
            Nova::style('column-toggler', __DIR__ . '/../dist/css/tool.css');
        });
    }

    /**
     * Register the tool's routes.
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', Authenticate::class, Authorize::class], 'column-toggler')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/column-toggler')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nova.php', 'nova.vendors.column_toggler',
        );
    }
}
