<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Model;
use App\Models\Shop;
use App\Models\ShopListingModel;
use App\Models\ShopOwner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopListingModelTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $shopListingModel = new ShopListingModel;
        $fillable = $shopListingModel->getFillable();

        $this->assertContains('shop_owner_id', $fillable);
        $this->assertContains('shop_id', $fillable);
        $this->assertContains('model_id', $fillable);
        $this->assertContains('taxonomy_id', $fillable);
        $this->assertContains('shop_listing_id', $fillable);
        $this->assertContains('shop_listing_image_id', $fillable);
        $this->assertContains('state', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $shopListingModel = new ShopListingModel;
        $casts = $shopListingModel->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_belongs_to_shop_owner(): void
    {
        $shopListingModel = new ShopListingModel;

        $this->assertInstanceOf(BelongsTo::class, $shopListingModel->shopOwner());
        $this->assertEquals(ShopOwner::class, $shopListingModel->shopOwner()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_shop(): void
    {
        $shopListingModel = new ShopListingModel;

        $this->assertInstanceOf(BelongsTo::class, $shopListingModel->shop());
        $this->assertEquals(Shop::class, $shopListingModel->shop()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_model(): void
    {
        $shopListingModel = new ShopListingModel;

        $this->assertInstanceOf(BelongsTo::class, $shopListingModel->model());
        $this->assertEquals(Model::class, $shopListingModel->model()->getRelated()::class);
    }
}
