<?php

namespace App\Providers;

use App\Nova\Country;
use App\Nova\Currency;
use App\Nova\Dashboards\Main;
use App\Nova\Language;
use App\Nova\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Sereny\NovaPermissions\Nova\Permission;
use Sereny\NovaPermissions\Nova\Role;
use Sereny\NovaPermissions\NovaPermissions;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Nova::mainMenu(function (Request $request) {
            return [
                MenuSection::dashboard(Main::class)
                    ->icon('chart-bar'),

                MenuSection::make(__('Users'), [
                    MenuItem::resource(User::class),
                    MenuItem::resource(Role::class)
                        ->canSee(function (NovaRequest $request) {
                        return $request->user()->isSuperAdmin();
                    }),
                    MenuItem::resource(Permission::class)
                        ->canSee(function (NovaRequest $request) {
                        return $request->user()->isSuperAdmin();
                    }),
                ])->icon('user')
                    ->collapsable(),

                MenuSection::make(__('Internationalization'), [
                    MenuItem::resource(Currency::class),
                    MenuItem::resource(Language::class),
                    MenuItem::resource(Country::class),
                ])->icon('document-text')
                    ->collapsable(),
            ];
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes(default: true)
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            if ($user->isSuperAdmin() ||
                $user->hasRole('admin') ||
                $user->hasRole('supplier')
            ) {
                return true;
            }
            return false;
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            (new NovaPermissions())->canSee(function ($request) {
                return $request->user()->isSuperAdmin();
            }),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
