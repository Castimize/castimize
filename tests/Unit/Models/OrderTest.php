<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerShipment;
use App\Models\InvoiceLine;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\Rejection;
use App\Models\Reprint;
use App\Models\Service;
use App\Models\ShopOrder;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $order = new Order;
        $fillable = $order->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('order_number', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('total', $fillable);
        $this->assertContains('currency_code', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $order = new Order;
        $casts = $order->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['paid_at']);
        $this->assertEquals('datetime', $casts['due_date']);
        $this->assertEquals('datetime', $casts['arrived_at']);
    }

    #[Test]
    public function it_casts_booleans_correctly(): void
    {
        $order = new Order;
        $casts = $order->getCasts();

        $this->assertEquals('boolean', $casts['is_paid']);
        $this->assertEquals('boolean', $casts['has_manual_refund']);
    }

    #[Test]
    public function it_casts_meta_data_as_array_object(): void
    {
        $order = new Order;
        $casts = $order->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['meta_data']);
    }

    #[Test]
    public function it_converts_service_fee_from_cents(): void
    {
        $order = new Order;
        $order->setRawAttributes(['service_fee' => 1000]);

        $this->assertEquals(10.00, $order->service_fee);
    }

    #[Test]
    public function it_converts_total_from_cents(): void
    {
        $order = new Order;
        $order->setRawAttributes(['total' => 10000]);

        $this->assertEquals(100.00, $order->total);
    }

    #[Test]
    public function it_converts_shipping_fee_from_cents(): void
    {
        $order = new Order;
        $order->setRawAttributes(['shipping_fee' => 500]);

        $this->assertEquals(5.00, $order->shipping_fee);
    }

    #[Test]
    public function it_converts_discount_fee_from_cents(): void
    {
        $order = new Order;
        $order->setRawAttributes(['discount_fee' => 250]);

        $this->assertEquals(2.50, $order->discount_fee);
    }

    #[Test]
    public function it_converts_total_tax_from_cents(): void
    {
        $order = new Order;
        $order->setRawAttributes(['total_tax' => 2100]);

        $this->assertEquals(21.00, $order->total_tax);
    }

    #[Test]
    public function it_converts_total_refund_from_cents(): void
    {
        $order = new Order;
        $order->setRawAttributes(['total_refund' => 1500]);

        $this->assertEquals(15.00, $order->total_refund);
    }

    #[Test]
    public function it_converts_production_cost_from_cents(): void
    {
        $order = new Order;
        $order->setRawAttributes(['production_cost' => 5000]);

        $this->assertEquals(50.00, $order->production_cost);
    }

    #[Test]
    public function it_computes_billing_name(): void
    {
        $order = new Order;
        $order->setRawAttributes([
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $order->billing_name);
    }

    #[Test]
    public function it_computes_shipping_name(): void
    {
        $order = new Order;
        $order->setRawAttributes([
            'shipping_first_name' => 'Jane',
            'shipping_last_name' => 'Smith',
        ]);

        $this->assertEquals('Jane Smith', $order->shipping_name);
    }

    #[Test]
    public function it_computes_billing_address(): void
    {
        $order = new Order;
        $order->setRawAttributes([
            'billing_first_name' => 'John',
            'billing_last_name' => 'Doe',
            'billing_company' => 'ACME Inc',
            'billing_address_line1' => '123 Main St',
            'billing_city' => 'Amsterdam',
            'billing_country' => 'NL',
        ]);

        $billingAddress = $order->billing_address;

        $this->assertArrayHasKey('first_name', $billingAddress);
        $this->assertArrayHasKey('city', $billingAddress);
        $this->assertArrayHasKey('country', $billingAddress);
        $this->assertEquals('John', $billingAddress['first_name']);
        $this->assertEquals('Amsterdam', $billingAddress['city']);
    }

    #[Test]
    public function it_computes_shipping_address(): void
    {
        $order = new Order;
        $order->setRawAttributes([
            'shipping_first_name' => 'Jane',
            'shipping_last_name' => 'Smith',
            'shipping_address_line1' => '456 Oak Ave',
            'shipping_city' => 'Rotterdam',
            'shipping_country' => 'NL',
        ]);

        $shippingAddress = $order->shipping_address;

        $this->assertArrayHasKey('first_name', $shippingAddress);
        $this->assertArrayHasKey('city', $shippingAddress);
        $this->assertEquals('Rotterdam', $shippingAddress['city']);
    }

    #[Test]
    public function it_belongs_to_customer(): void
    {
        $order = new Order;

        $this->assertInstanceOf(BelongsTo::class, $order->customer());
        $this->assertEquals(Customer::class, $order->customer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_country(): void
    {
        $order = new Order;

        $this->assertInstanceOf(BelongsTo::class, $order->country());
        $this->assertEquals(Country::class, $order->country()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_customer_shipment(): void
    {
        $order = new Order;

        $this->assertInstanceOf(BelongsTo::class, $order->customerShipment());
        $this->assertEquals(CustomerShipment::class, $order->customerShipment()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $order = new Order;

        $this->assertInstanceOf(BelongsTo::class, $order->currency());
        $this->assertEquals(Currency::class, $order->currency()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_service(): void
    {
        $order = new Order;

        $this->assertInstanceOf(BelongsTo::class, $order->service());
        $this->assertEquals(Service::class, $order->service()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_uploads(): void
    {
        $order = new Order;

        $this->assertInstanceOf(HasMany::class, $order->uploads());
        $this->assertEquals(Upload::class, $order->uploads()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_order_queues(): void
    {
        $order = new Order;

        $this->assertInstanceOf(HasMany::class, $order->orderQueues());
        $this->assertEquals(OrderQueue::class, $order->orderQueues()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_rejections(): void
    {
        $order = new Order;

        $this->assertInstanceOf(HasMany::class, $order->rejections());
        $this->assertEquals(Rejection::class, $order->rejections()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_reprints(): void
    {
        $order = new Order;

        $this->assertInstanceOf(HasMany::class, $order->reprints());
        $this->assertEquals(Reprint::class, $order->reprints()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_invoice_lines(): void
    {
        $order = new Order;

        $this->assertInstanceOf(HasMany::class, $order->invoiceLines());
        $this->assertEquals(InvoiceLine::class, $order->invoiceLines()->getRelated()::class);
    }

    #[Test]
    public function it_has_one_shop_order(): void
    {
        $order = new Order;

        $this->assertInstanceOf(HasOne::class, $order->shopOrder());
        $this->assertEquals(ShopOrder::class, $order->shopOrder()->getRelated()::class);
    }
}
