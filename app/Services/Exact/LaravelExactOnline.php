<?php

namespace App\Services\Exact;

use Exception;
use File;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Picqer\Financials\Exact\Connection;
use RuntimeException;

class LaravelExactOnline
{
    private $connection;

    /**
     * LaravelExactOnline constructor.
     */
    public function __construct()
    {
        $this->connection = app()->make('Exact\Connection');
    }

    /**
     * Magically calls methods from Picqer Exact Online API
     *
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        if (str_starts_with($method, 'connection')) {

            $method = lcfirst(substr($method, 10));

            call_user_func([$this->connection, $method], implode(",", $arguments));

            return $this;

        }

        $classname = "\\Picqer\\Financials\\Exact\\" . $method;

        if (!class_exists($classname)) {
            throw new RuntimeException('Invalid type called');
        }

        return new $classname($this->connection);

    }

    /**
     * @param Connection $connection
     * @return void
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
     * @return object
     * @throws JsonException
     */
    public static function loadConfig()
    {
        $config = '{}';

        if (Storage::disk('s3_private')->exists('exact/credentials.json')) {
            $config = Storage::disk('s3_private')->get('exact/credentials.json');
        }

        return (object)json_decode($config, false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param $config
     * @return void
     * @throws JsonException
     */
    public static function storeConfig($config): void
    {
        Storage::disk('s3_private')->put('exact/credentials.json', json_encode($config, JSON_THROW_ON_ERROR));
    }

}
