<?php

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    protected function migrateFreshUsing()
    {
        return [
            '--schema-path' => 'database/schema/mysql-schema.sql',
        ];
    }
}
