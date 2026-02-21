<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopOwner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopOrderTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $shopOrder = new ShopOrder;
        $fillable = $shopOrder->getFillable();

        $this->assertContains('shop_owner_id', $fillable);
        $this->assertContains('shop_id', $fillable);
        $this->assertContains('order_number', $fillable);
        $this->assertContains('shop_receipt_id', $fillable);
        $this->assertContains('state', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $shopOrder = new ShopOrder;
        $casts = $shopOrder->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_belongs_to_shop_owner(): void
    {
        $shopOrder = new ShopOrder;

        $this->assertInstanceOf(BelongsTo::class, $shopOrder->shopOwner());
        $this->assertEquals(ShopOwner::class, $shopOrder->shopOwner()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_shop(): void
    {
        $shopOrder = new ShopOrder;

        $this->assertInstanceOf(BelongsTo::class, $shopOrder->shop());
        $this->assertEquals(Shop::class, $shopOrder->shop()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_shop_with_trashed(): void
    {
        $shopOrder = new ShopOrder;

        $this->assertInstanceOf(BelongsTo::class, $shopOrder->shopWithTrashed());
        $this->assertEquals(Shop::class, $shopOrder->shopWithTrashed()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order(): void
    {
        $shopOrder = new ShopOrder;

        $this->assertInstanceOf(BelongsTo::class, $shopOrder->order());
        $this->assertEquals(Order::class, $shopOrder->order()->getRelated()::class);
    }
}
