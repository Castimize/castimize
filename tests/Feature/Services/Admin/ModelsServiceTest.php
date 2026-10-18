<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Admin;

use App\Models\Customer;
use App\Models\Model;
use App\Services\Admin\ModelsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelsServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ModelsService $modelsService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->modelsService = new ModelsService;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function paginationRequest(array $overrides = []): Request
    {
        return new Request(array_merge([
            'start' => 0,
            'length' => 10,
            'order_column' => 'name',
            'order_dir' => 'asc',
            'search_value' => null,
        ], $overrides));
    }

    #[Test]
    public function it_does_not_share_cached_pages_between_customers(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();
        $modelA = Model::factory()->create(['customer_id' => $customerA->id]);
        $modelB = Model::factory()->create(['customer_id' => $customerB->id]);

        $request = $this->paginationRequest();

        $resultA = $this->modelsService->getModelsPaginated($request, $customerA);
        $resultB = $this->modelsService->getModelsPaginated($request, $customerB);

        $this->assertSame([$modelA->id], $resultA['items']->pluck('id')->all());
        $this->assertSame([$modelB->id], $resultB['items']->pluck('id')->all());
    }

    #[Test]
    public function it_caches_the_page_for_the_same_customer(): void
    {
        $customer = Customer::factory()->create();
        $model = Model::factory()->create(['customer_id' => $customer->id]);
        $request = $this->paginationRequest();

        $first = $this->modelsService->getModelsPaginated($request, $customer);
        Model::factory()->create(['customer_id' => $customer->id]);
        $second = $this->modelsService->getModelsPaginated($request, $customer);

        $this->assertSame([$model->id], $first['items']->pluck('id')->all());
        $this->assertSame([$model->id], $second['items']->pluck('id')->all());
    }

    #[Test]
    public function it_uses_a_separate_cache_entry_per_search_value(): void
    {
        $customer = Customer::factory()->create();
        $matching = Model::factory()->create(['customer_id' => $customer->id, 'name' => 'needle-model', 'model_name' => 'needle-model']);
        Model::factory()->create(['customer_id' => $customer->id, 'name' => 'other', 'model_name' => 'other']);

        $filtered = $this->modelsService->getModelsPaginated($this->paginationRequest(['search_value' => 'needle']), $customer);
        $unfiltered = $this->modelsService->getModelsPaginated($this->paginationRequest(), $customer);

        $this->assertSame([$matching->id], $filtered['items']->pluck('id')->all());
        $this->assertSame(2, $unfiltered['total']);
    }
}
