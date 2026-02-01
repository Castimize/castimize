<?php

declare(strict_types=1);

namespace Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelsApiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_stores_model(): void
    {
        //        $response = $this->postJson(route('api.api.models.store-from-upload'), [
        //            'name' => 'Test Model',
        //        ]);
        //        dd($response->getContent());
    }
}
