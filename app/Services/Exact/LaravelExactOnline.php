<?php

namespace App\Services\Exact;

use Exception;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Picqer\Financials\Exact\Connection;
use RuntimeException;
use Throwable;

class LaravelExactOnline
{
    /**
     * @var null|Lock
     */
    public static $lock = null;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
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
     * @throws RuntimeException
     */
    public static function tokenUpdateCallback(Connection $connection): void
    {
        try {
            $config = self::loadConfig();

            $config->exact_accessToken = serialize($connection->getAccessToken());
            $config->exact_refreshToken = $connection->getRefreshToken();
            $config->exact_tokenExpires = $connection->getTokenExpires();

            self::storeConfig($config);

            Log::info('Exact Online tokens updated successfully');
        } catch (Throwable $e) {
            Log::error('Failed to update Exact Online tokens: '.$e->getMessage());
            throw new RuntimeException('Failed to store Exact Online tokens: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Function to handle the token refresh call from picqer.
     * Called before token refresh to reload latest tokens from storage.
     *
     * @param  Connection  $connection  Connection instance.
     */
    public static function tokenRefreshCallback(Connection $connection): void
    {
        Log::debug('Exact Online: Reloading tokens from storage before refresh');

        $config = self::loadConfig();

        if (isset($config->exact_accessToken)) {
            $connection->setAccessToken(unserialize($config->exact_accessToken));
        }
        if (isset($config->exact_refreshToken)) {
            $connection->setRefreshToken($config->exact_refreshToken);
        }
        if (isset($config->exact_tokenExpires)) {
            $connection->setTokenExpires($config->exact_tokenExpires);
            Log::debug('Exact Online: Token expires at '.date('Y-m-d H:i:s', $config->exact_tokenExpires));
        }
    }

    /**
     * Acquire refresh lock to avoid duplicate calls to exact.
     *
     * @throws RuntimeException
     */
    public static function acquireLock(): bool
    {
        /** @var Repository $cache */
        $cache = app()->make(Repository::class);
        $store = $cache->getStore();

        if (! $store instanceof LockProvider) {
            Log::error('Exact Online: Cache store does not support locking. This can cause token refresh race conditions.');
            throw new RuntimeException('Cache store does not support atomic locks. Configure Redis or database cache for Exact Online integration.');
        }

        Log::debug('Exact Online: Attempting to acquire token refresh lock');

        self::$lock = $store->lock(self::$lockKey, 60);
        $acquired = self::$lock->block(30);

        if (! $acquired) {
            Log::warning('Exact Online: Failed to acquire token refresh lock within 30 seconds');
        } else {
            Log::debug('Exact Online: Token refresh lock acquired');
        }

        return $acquired;
    }

    /**
     * Release lock that was set.
     */
    public static function releaseLock(): ?bool
    {
        $released = optional(self::$lock)->release();

        if ($released) {
            Log::debug('Exact Online: Token refresh lock released');
        }

        return $released;
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
