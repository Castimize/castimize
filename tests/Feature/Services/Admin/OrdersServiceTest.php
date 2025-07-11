<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesOrderFromWp(): void
    {
        $json = $this->getWPOrderData();
    }

    private function getWPOrderData()
    {
        $json = json_decode(file_get_contents(storage_path('tests/wp-order.json')));
        dd($json);
    }
}
