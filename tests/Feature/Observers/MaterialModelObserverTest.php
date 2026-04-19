<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Models\Material;
use App\Models\Model;
use App\Models\Shop;
use App\Models\ShopListingModel;
use App\Models\ShopOwner;
use App\Services\Admin\ModelsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaterialModelObserverTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_syncs_etsy_listing_when_material_is_detached_from_model(): void
    {
        $model = $this->makeModelWithEtsyListing();
        $material = Material::factory()->create();
        $model->materials()->attach($material->id);

        $syncCalled = false;
        $mock = Mockery::mock(ModelsService::class);
        $mock->shouldReceive('syncModelToShop')
            ->once()
            ->withArgs(function (Model $syncedModel) use ($model, &$syncCalled): bool {
                $syncCalled = $syncedModel->id === $model->id;

                return true;
            });
        $this->app->bind(ModelsService::class, fn () => $mock);

        $model->materials()->detach($material->id);

        $this->assertTrue($syncCalled, 'syncModelToShop should have been called with the correct model');
    }

    #[Test]
    public function it_does_not_sync_when_model_has_no_etsy_listing(): void
    {
        $shopOwner = ShopOwner::factory()->create();
        $model = Model::factory()->create([
            'customer_id' => $shopOwner->customer_id,
        ]);
        Shop::factory()->etsy()->create(['shop_owner_id' => $shopOwner->id]);
        $material = Material::factory()->create();
        $model->materials()->attach($material->id);

        $mock = Mockery::mock(ModelsService::class);
        $mock->shouldNotReceive('syncModelToShop');
        $this->app->bind(ModelsService::class, fn () => $mock);

        $model->materials()->detach($material->id);
    }

    #[Test]
    public function it_does_not_sync_when_model_has_no_shop_owner(): void
    {
        $model = Model::factory()->create();
        $material = Material::factory()->create();
        $model->materials()->attach($material->id);

        $mock = Mockery::mock(ModelsService::class);
        $mock->shouldNotReceive('syncModelToShop');
        $this->app->bind(ModelsService::class, fn () => $mock);

        $model->materials()->detach($material->id);
    }

    #[Test]
    public function it_does_not_sync_when_shop_is_inactive(): void
    {
        $shopOwner = ShopOwner::factory()->create();
        $model = Model::factory()->create([
            'customer_id' => $shopOwner->customer_id,
        ]);
        $shop = Shop::factory()->etsy()->inactive()->create(['shop_owner_id' => $shopOwner->id]);
        ShopListingModel::create([
            'shop_owner_id' => $shopOwner->id,
            'shop_id' => $shop->id,
            'model_id' => $model->id,
            'shop_listing_id' => 999,
        ]);
        $material = Material::factory()->create();
        $model->materials()->attach($material->id);

        $mock = Mockery::mock(ModelsService::class);
        $mock->shouldNotReceive('syncModelToShop');
        $this->app->bind(ModelsService::class, fn () => $mock);

        $model->materials()->detach($material->id);
    }

    #[Test]
    public function it_syncs_all_listings_when_material_is_detached_via_sync(): void
    {
        $model = $this->makeModelWithEtsyListing();
        $materialA = Material::factory()->create();
        $materialB = Material::factory()->create();
        $model->materials()->attach([$materialA->id, $materialB->id]);

        $syncCount = 0;
        $mock = Mockery::mock(ModelsService::class);
        $mock->shouldReceive('syncModelToShop')
            ->once()
            ->andReturnUsing(function () use (&$syncCount): void {
                $syncCount++;
            });
        $this->app->bind(ModelsService::class, fn () => $mock);

        // sync() with only materialB detaches materialA
        $model->materials()->sync([$materialB->id]);

        $this->assertEquals(1, $syncCount);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeModelWithEtsyListing(): Model
    {
        $shopOwner = ShopOwner::factory()->create();
        $model = Model::factory()->create([
            'customer_id' => $shopOwner->customer_id,
        ]);
        $shop = Shop::factory()->etsy()->create(['shop_owner_id' => $shopOwner->id]);
        ShopListingModel::create([
            'shop_owner_id' => $shopOwner->id,
            'shop_id' => $shop->id,
            'model_id' => $model->id,
            'shop_listing_id' => fake()->numberBetween(10000, 99999),
        ]);

        return $model;
    }
}
