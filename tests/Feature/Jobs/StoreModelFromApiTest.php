<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\StoreModelFromApi;
use App\Services\Admin\ModelsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreModelFromApiTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        $request = new Request([
            'name' => 'test_model.stl',
            'file_name' => 'wp-content/uploads/p3d/test_model.stl',
        ]);

        StoreModelFromApi::dispatch($request);

        Queue::assertPushed(StoreModelFromApi::class);
    }

    #[Test]
    public function it_calls_models_service_to_store_model(): void
    {
        $request = new Request([
            'name' => 'test_model.stl',
            'file_name' => 'wp-content/uploads/p3d/test_model.stl',
            'model_volume_cc' => 12.5,
            'model_surface_area_cm2' => 85.5,
        ]);

        $modelsService = $this->mock(ModelsService::class);
        $modelsService->shouldReceive('storeModelFromApi')
            ->once()
            ->with(\Mockery::on(function ($req) {
                return $req->get('name') === 'test_model.stl';
            }));

        $job = new StoreModelFromApi($request);
        $job->handle($modelsService);
    }
}
