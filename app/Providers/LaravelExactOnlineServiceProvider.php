<?php

namespace App\Providers;

use App\Services\Exact\LaravelExactOnline;
use Illuminate\Support\ServiceProvider;
use Picqer\Financials\Exact\Connection;

class LaravelExactOnlineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void {}

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->alias(LaravelExactOnline::class, 'laravel-exact-online');

        $this->app->singleton('Exact\Connection', static function () {
            if (app()->environment() === 'production') {
                $config = LaravelExactOnline::loadConfig();

                $connection = new Connection;
                $connection->setRedirectUrl(route('exact.callback'));
                $connection->setExactClientId(config('exactonline.exact_client_id'));
                $connection->setExactClientSecret(config('exactonline.exact_client_secret'));
                $connection->setBaseUrl('https://start.exactonline.'.config('exactonline.exact_country_code'));

                if (config('exactonline.exact_division') !== '') {
                    $connection->setDivision(config('exactonline.exact_division'));
                }

                if (isset($config->exact_authorisationCode)) {
                    $connection->setAuthorizationCode($config->exact_authorisationCode);
                }

                // Init connection items (just as when the token is refreshed)
                LaravelExactOnline::tokenRefreshCallback($connection);

                $connection->setTokenUpdateCallback([LaravelExactOnline::class, 'tokenUpdateCallback']);
                $connection->setRefreshAccessTokenCallback([LaravelExactOnline::class, 'tokenRefreshCallback']);
                $connection->setAcquireAccessTokenLockCallback([LaravelExactOnline::class, 'acquireLock']);
                $connection->setAcquireAccessTokenUnlockCallback([LaravelExactOnline::class, 'releaseLock']);

                $connection->connect();

                return $connection;
            }

            return null;
        });
    }
}
