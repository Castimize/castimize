<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\UploadToOrderQueue;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Order;
use App\Models\Upload;
use App\Services\Admin\UploadsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UploadToOrderQueueTest extends TestCase
{
    use DatabaseTransactions;

    private Upload $upload;

    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );

        $customer = Customer::factory()->create();
        $material = Material::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'currency_id' => $this->currency->id,
        ]);

        $this->upload = Upload::factory()->create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'material_id' => $material->id,
            'currency_id' => $this->currency->id,
        ]);
    }

    #[Test]
    public function it_calls_uploads_service_to_set_upload_to_order_queue(): void
    {
        $uploadsService = $this->mock(UploadsService::class);
        $uploadsService->shouldReceive('setUploadToOrderQueue')
            ->once()
            ->with(\Mockery::type(Upload::class));

        $job = new UploadToOrderQueue($this->upload);
        $job->handle($uploadsService);
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        // Reuse the upload from setUp instead of creating new one
        UploadToOrderQueue::dispatch($this->upload);

        Queue::assertPushed(UploadToOrderQueue::class);
    }
}
