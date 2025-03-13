<?php

namespace App\Providers;

use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Services\Exact\LaravelExactOnline;
use Picqer\Financials\Exact\Connection;

class LaravelExactOnlineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias(LaravelExactOnline::class, 'laravel-exact-online');

        $this->app->singleton('Exact\Connection', function () {
            if (app()->environment() === 'production') {
                $config = LaravelExactOnline::loadConfig();

                $connection = new Connection();
                $connection->setRedirectUrl(config('exactonline.callback_url'));
                $connection->setExactClientId(config('exactonline.exact_client_id'));
                $connection->setExactClientSecret(config('exactonline.exact_client_secret'));
                $connection->setBaseUrl('https://start.exactonline.' . config('exactonline.exact_country_code'));

                if (config('exactonline.exact_division') !== '') {
                    $connection->setDivision(config('exactonline.exact_division'));
                }

                if (isset($config->exact_authorisationCode)) {
                    $connection->setAuthorizationCode($config->exact_authorisationCode);
                }

                if (isset($config->exact_accessToken)) {
                    $connection->setAccessToken(unserialize($config->exact_accessToken));
                }

                if (isset($config->exact_refreshToken)) {
                    $connection->setRefreshToken($config->exact_refreshToken);
                }

                if (isset($config->exact_tokenExpires)) {
                    $connection->setTokenExpires($config->exact_tokenExpires);
                }

                $connection->setTokenUpdateCallback('\App\Services\Exact\LaravelExactOnline::tokenUpdateCallback');

                try {
                    if (isset($config->exact_authorisationCode)) {
                        $connection->connect();
                    }
                } catch (RequestException $e) {
                    $connection->setAccessToken(null);
                    $connection->setRefreshToken(null);
                    $connection->connect();
                } catch (Exception $e) {
                    Log::error('Could not connect to Exact: ' . $e->getMessage());
                    return null;
                }

                $config->exact_accessToken = serialize($connection->getAccessToken());
                $config->exact_refreshToken = $connection->getRefreshToken();
                $config->exact_tokenExpires = $connection->getTokenExpires();

                LaravelExactOnline::storeConfig($config);

                return $connection;
            }

            return null;
        });
    }
}
