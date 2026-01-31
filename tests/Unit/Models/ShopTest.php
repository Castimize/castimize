<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Shop;
use App\Models\ShopListingModel;
use App\Models\ShopOrder;
use App\Models\ShopOwner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $shop = new Shop;
        $fillable = $shop->getFillable();

        $this->assertContains('shop_owner_id', $fillable);
        $this->assertContains('shop', $fillable);
        $this->assertContains('shop_oauth', $fillable);
        $this->assertContains('active', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $shop = new Shop;
        $casts = $shop->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_shop_oauth_as_array(): void
    {
        $shop = new Shop;
        $casts = $shop->getCasts();

        $this->assertEquals('array', $casts['shop_oauth']);
    }

    #[Test]
    public function it_casts_active_as_boolean(): void
    {
        $shop = new Shop;
        $casts = $shop->getCasts();

        $this->assertEquals('boolean', $casts['active']);
    }

    #[Test]
    public function it_belongs_to_shop_owner(): void
    {
        $shop = new Shop;

        $this->assertInstanceOf(BelongsTo::class, $shop->shopOwner());
        $this->assertEquals(ShopOwner::class, $shop->shopOwner()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_shop_orders(): void
    {
        $shop = new Shop;

        $this->assertInstanceOf(HasMany::class, $shop->shopOrders());
        $this->assertEquals(ShopOrder::class, $shop->shopOrders()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_shop_listing_models(): void
    {
        $shop = new Shop;

        $this->assertInstanceOf(HasMany::class, $shop->shopListingModels());
        $this->assertEquals(ShopListingModel::class, $shop->shopListingModels()->getRelated()::class);
    }
}
