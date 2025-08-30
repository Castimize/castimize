<?php

namespace App\Services\Exact;

use Exception;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Picqer\Financials\Exact\Connection;
use RuntimeException;

class LaravelExactOnline
{
    /** @var null|Lock */
    public static $lock = null;

    /** @var Connection */
    private $connection;

    /** @var string */
    private static $lockKey = 'exactonline.refreshLock';

    /**
     * Return connection instance.
     */
    public function connection(): Connection
    {
        if (! $this->connection) {
            $this->connection = app()->make('Exact\Connection');
        }

        return $this->connection;
    }

    /**
     * Magically calls methods from Picqer Exact Online API
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        if (str_starts_with($method, 'connection')) {

            $method = lcfirst(substr($method, 10));

            call_user_func([$this->connection, $method], implode(',', $arguments));

            return $this;

        }

        $classname = '\\Picqer\\Financials\\Exact\\'.$method;

        if (! class_exists($classname)) {
            throw new RuntimeException('Invalid type called');
        }

        return new $classname($this->connection);

    }

    /**
     * @throws JsonException
     */
    public static function tokenUpdateCallback(Connection $connection): void
    {
        $config = self::loadConfig();

        $config->exact_accessToken = serialize($connection->getAccessToken());
        $config->exact_refreshToken = $connection->getRefreshToken();
        $config->exact_tokenExpires = $connection->getTokenExpires();

        self::storeConfig($config);
    }

    /**
     * Function to handle the token refresh call from picqer.
     *
     * @param  Connection  $connection  Connection instance.
     */
    public static function tokenRefreshCallback(Connection $connection): void
    {
        $config = self::loadConfig();

        if (isset($config->exact_accessToken)) {
            $connection->setAccessToken(unserialize($config->exact_accessToken));
        }
        if (isset($config->exact_refreshToken)) {
            $connection->setRefreshToken($config->exact_refreshToken);
        }
        if (isset($config->exact_tokenExpires)) {
            $connection->setTokenExpires($config->exact_tokenExpires);
        }
    }

    /**
     * Acquire refresh lock to avoid duplicate calls to exact.
     */
    public static function acquireLock(): bool
    {
        /** @var Repository $cache */
        $cache = app()->make(Repository::class);
        $store = $cache->getStore();

        if (! $store instanceof LockProvider) {
            return false;
        }

        self::$lock = $store->lock(self::$lockKey, 60);

        return self::$lock->block(30);
    }

    /**
     * Release lock that was set.
     *
     * @return bool
     */
    public static function releaseLock()
    {
        return optional(self::$lock)->release();
    }

    /**
     * @return object
     *
     * @throws JsonException
     */
    public static function loadConfig()
    {
        $config = '{}';

        if (Storage::disk('r2_private')->exists('exact/credentials.json')) {
            $config = Storage::disk('r2_private')->get('exact/credentials.json');
        }

        return (object) json_decode($config, false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public static function storeConfig($config): void
    {
        Storage::disk('r2_private')->put('exact/credentials.json', json_encode($config, JSON_THROW_ON_ERROR));
    }
}
