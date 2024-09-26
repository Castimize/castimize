<?php

namespace App\Providers;

use App\Nova\City;
use App\Nova\Complaint;
use App\Nova\ComplaintReason;
use App\Nova\Country;
use App\Nova\Currency;
use App\Nova\Customer;
use App\Nova\Dashboards\Main;
use App\Nova\Language;
use App\Nova\LogisticsZone;
use App\Nova\Manufacturer;
use App\Nova\Material;
use App\Nova\MaterialGroup;
use App\Nova\Model;
use App\Nova\Order;
use App\Nova\Price;
use App\Nova\Rejection;
use App\Nova\RejectionReason;
use App\Nova\ShippingFee;
use App\Nova\State;
use App\Nova\Upload;
use App\Nova\User;
use CodencoDev\NovaGridSystem\NovaGridSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Kaiserkiwi\NovaQueueManagement\Resources\FailedJob;
use Kaiserkiwi\NovaQueueManagement\Resources\Job;
use Kaiserkiwi\NovaQueueManagement\Tool;
use Laravel\Nova\Fields\Field;
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

        Nova::withBreadcrumbs();

        Nova::mainMenu(function (Request $request) {
            if ($request->user()->hasRole('manufacturer')) {
                return [
                    MenuSection::dashboard(Main::class)
                        ->icon('chart-bar'),
                ];
            }
            return [
                MenuSection::dashboard(Main::class)
                    ->icon('chart-bar'),

                MenuSection::make(__('Customers'), [
                    MenuItem::resource(Order::class),
                    MenuItem::resource(Upload::class),
                    MenuItem::resource(Customer::class),
                    MenuItem::resource(Rejection::class),
                    MenuItem::resource(RejectionReason::class),
                ])->icon('clipboard-list')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isBackendUser();
                    }),

                MenuSection::make(__('Manufacturers'), [
                    MenuItem::resource(Manufacturer::class),
                ])->icon('office-building')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isBackendUser();
                    }),

                MenuSection::make(__('Materials'), [
                    MenuItem::resource(Material::class),
                    MenuItem::resource(Price::class),
                    MenuItem::resource(MaterialGroup::class),
                    MenuItem::resource(Model::class),
//                    MenuItem::resource(PreferredManufacturer::class),
                ])->icon('collection')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isBackendUser();
                    }),

                MenuSection::make(__('Logistics'), [
                    MenuItem::resource(LogisticsZone::class),
                    MenuItem::resource(ShippingFee::class),
                    MenuItem::resource(Country::class),
                ])->icon('truck')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isBackendUser();
                    }),

                MenuSection::make(__('Complaints'), [
                    MenuItem::resource(Complaint::class),
                    MenuItem::resource(ComplaintReason::class),
                ])->icon('exclamation-circle')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isBackendUser();
                    }),

                MenuSection::make(__('Invoices'), [
//                    MenuItem::resource(Invoice::class),
                ])->icon('document-text')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isBackendUser();
                    }),

                MenuSection::make(__('Users'), [
                    MenuItem::resource(User::class),
                ])->icon('user')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isSuperAdmin() || $request->user()->isAdmin();
                    }),

                MenuSection::make(__('Internationalization'), [
                    MenuItem::resource(Currency::class),
                    MenuItem::resource(Language::class),
                    MenuItem::resource(Country::class),
                    MenuItem::resource(State::class),
                    MenuItem::resource(City::class),
                ])->icon('globe')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isSuperAdmin() || $request->user()->isAdmin();
                    }),

                MenuSection::make(__('Roles and Permissions'), [
                    MenuItem::resource(Role::class)
                        ->canSee(function (NovaRequest $request) {
                            return $request->user()->isSuperAdmin();
                        }),
                    MenuItem::resource(Permission::class)
                        ->canSee(function (NovaRequest $request) {
                            return $request->user()->isSuperAdmin();
                        }),
                ])->icon('shield-check')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isSuperAdmin();
                    }),

                MenuSection::make(__('Queue'), [
                    MenuItem::resource(Job::class)->withBadge(fn() => \Kaiserkiwi\NovaQueueManagement\Models\Job::count()),
                    MenuItem::resource(FailedJob::class)->withBadge(fn() => \Kaiserkiwi\NovaQueueManagement\Models\FailedJob::count()),
                ])->icon('collection')
                    ->collapsable()
                    ->canSee(function (NovaRequest $request) {
                        return $request->user()->isSuperAdmin();
                    }),
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
            new NovaGridSystem(),
            new Tool(),
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
