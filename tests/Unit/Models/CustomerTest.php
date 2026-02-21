<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Complaint;
use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerShipment;
use App\Models\Model;
use App\Models\Order;
use App\Models\ShopOwner;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $customer = new Customer;
        $fillable = $customer->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('country_id', $fillable);
        $this->assertContains('wp_id', $fillable);
        $this->assertContains('first_name', $fillable);
        $this->assertContains('last_name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('phone', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $customer = new Customer;
        $casts = $customer->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['last_active']);
    }

    #[Test]
    public function it_casts_stripe_data_as_array(): void
    {
        $customer = new Customer;
        $casts = $customer->getCasts();

        $this->assertEquals('array', $casts['stripe_data']);
    }

    #[Test]
    public function it_computes_name_attribute(): void
    {
        $customer = new Customer;
        $customer->first_name = 'John';
        $customer->last_name = 'Doe';

        $this->assertEquals('John Doe', $customer->name);
    }

    #[Test]
    public function it_belongs_to_user(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(BelongsTo::class, $customer->user());
        $this->assertEquals(User::class, $customer->user()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_country(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(BelongsTo::class, $customer->country());
        $this->assertEquals(Country::class, $customer->country()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_uploads(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(HasMany::class, $customer->uploads());
        $this->assertEquals(Upload::class, $customer->uploads()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_orders(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(HasMany::class, $customer->orders());
        $this->assertEquals(Order::class, $customer->orders()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_models(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(HasMany::class, $customer->models());
        $this->assertEquals(Model::class, $customer->models()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_shipments(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(HasMany::class, $customer->shipments());
        $this->assertEquals(CustomerShipment::class, $customer->shipments()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_complaints(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(HasMany::class, $customer->complaints());
        $this->assertEquals(Complaint::class, $customer->complaints()->getRelated()::class);
    }

    #[Test]
    public function it_has_one_shop_owner(): void
    {
        $customer = new Customer;

        $this->assertInstanceOf(HasOne::class, $customer->shopOwner());
        $this->assertEquals(ShopOwner::class, $customer->shopOwner()->getRelated()::class);
    }
}
