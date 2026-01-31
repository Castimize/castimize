<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\ShopOwner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopOwnerTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $shopOwner = new ShopOwner;
        $fillable = $shopOwner->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('active', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $shopOwner = new ShopOwner;
        $casts = $shopOwner->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_active_as_boolean(): void
    {
        $shopOwner = new ShopOwner;
        $casts = $shopOwner->getCasts();

        $this->assertEquals('boolean', $casts['active']);
    }

    #[Test]
    public function it_belongs_to_customer(): void
    {
        $shopOwner = new ShopOwner;

        $this->assertInstanceOf(BelongsTo::class, $shopOwner->customer());
        $this->assertEquals(Customer::class, $shopOwner->customer()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_shops(): void
    {
        $shopOwner = new ShopOwner;

        $this->assertInstanceOf(HasMany::class, $shopOwner->shops());
        $this->assertEquals(Shop::class, $shopOwner->shops()->getRelated()::class);
    }
}
