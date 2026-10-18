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

    #[Test]
    public function it_does_not_allow_sql_injection_through_the_search_value(): void
    {
        $customer = Customer::factory()->create();
        Model::factory()->create(['customer_id' => $customer->id, 'name' => 'plain', 'model_name' => 'plain']);

        $result = $this->modelsService->getModelsPaginated(
            $this->paginationRequest(['search_value' => "zzz%' OR 1=1 OR models.name LIKE '%zzz"]),
            $customer
        );

        $this->assertSame(0, $result['filtered']);
        $this->assertCount(0, $result['items']);
        $this->assertSame(1, $result['total']);
    }

    #[Test]
    public function it_finds_models_with_a_quote_in_the_search_value(): void
    {
        $customer = Customer::factory()->create();
        $model = Model::factory()->create(['customer_id' => $customer->id, 'name' => "O'Brien", 'model_name' => "O'Brien"]);
        Model::factory()->create(['customer_id' => $customer->id, 'name' => 'other', 'model_name' => 'other']);

        $result = $this->modelsService->getModelsPaginated(
            $this->paginationRequest(['search_value' => "O'Brien"]),
            $customer
        );

        $this->assertSame([$model->id], $result['items']->pluck('id')->all());
    }

    #[Test]
    public function it_treats_like_wildcards_in_the_search_value_literally(): void
    {
        $customer = Customer::factory()->create();
        Model::factory()->create(['customer_id' => $customer->id, 'name' => 'first', 'model_name' => 'first']);
        Model::factory()->create(['customer_id' => $customer->id, 'name' => 'second', 'model_name' => 'second']);

        $percent = $this->modelsService->getModelsPaginated($this->paginationRequest(['search_value' => '%']), $customer);
        $underscore = $this->modelsService->getModelsPaginated($this->paginationRequest(['search_value' => '_']), $customer);

        $this->assertSame(0, $percent['filtered']);
        $this->assertSame(0, $underscore['filtered']);
    }

    #[Test]
    public function it_ignores_an_invalid_order_direction_instead_of_injecting_it(): void
    {
        $customer = Customer::factory()->create();
        $first = Model::factory()->create(['customer_id' => $customer->id, 'name' => 'aaa', 'model_name' => 'aaa']);
        $second = Model::factory()->create(['customer_id' => $customer->id, 'name' => 'bbb', 'model_name' => 'bbb']);

        $injected = $this->modelsService->getModelsPaginated(
            $this->paginationRequest(['order_dir' => 'sideways; DROP TABLE models']),
            $customer
        );
        $descending = $this->modelsService->getModelsPaginated(
            $this->paginationRequest(['order_dir' => 'DESC']),
            $customer
        );

        $this->assertSame([$first->id, $second->id], $injected['items']->pluck('id')->all());
        $this->assertSame([$second->id, $first->id], $descending['items']->pluck('id')->all());
    }
}
