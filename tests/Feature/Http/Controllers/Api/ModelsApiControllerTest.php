<?php

declare(strict_types=1);

namespace Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class ModelsApiControllerTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        //        $this->seed();
    }

    public function test_it_stores_model(): void
    {
        dd(User::all()->first());
        $response = $this->postJson(route('api.api.api.models.store'), []);
        dd($response);
    }
}
