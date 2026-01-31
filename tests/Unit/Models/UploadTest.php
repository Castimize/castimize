<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $upload = new Upload;
        $fillable = $upload->getFillable();

        $this->assertContains('wp_id', $fillable);
        $this->assertContains('order_id', $fillable);
        $this->assertContains('material_id', $fillable);
        $this->assertContains('customer_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('file_name', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('total', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $upload = new Upload;
        $casts = $upload->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['due_date']);
        $this->assertEquals('datetime', $casts['completed_at']);
    }

    #[Test]
    public function it_casts_meta_data_as_array_object(): void
    {
        $upload = new Upload;
        $casts = $upload->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['meta_data']);
    }

    #[Test]
    public function it_converts_subtotal_from_cents(): void
    {
        $upload = new Upload;
        $upload->setRawAttributes(['subtotal' => 1000]);

        $this->assertEquals(10.00, $upload->subtotal);
    }

    #[Test]
    public function it_converts_total_from_cents(): void
    {
        $upload = new Upload;
        $upload->setRawAttributes(['total' => 2500]);

        $this->assertEquals(25.00, $upload->total);
    }

    #[Test]
    public function it_converts_total_tax_from_cents(): void
    {
        $upload = new Upload;
        $upload->setRawAttributes(['total_tax' => 525]);

        $this->assertEquals(5.25, $upload->total_tax);
    }

    #[Test]
    public function it_converts_total_refund_from_cents(): void
    {
        $upload = new Upload;
        $upload->setRawAttributes(['total_refund' => 300]);

        $this->assertEquals(3.00, $upload->total_refund);
    }

    #[Test]
    public function it_converts_manufacturer_discount_to_percentage(): void
    {
        $upload = new Upload;
        $upload->setRawAttributes(['manufacturer_discount' => 0.10]);

        $this->assertEquals(10.0, $upload->manufacturer_discount);
    }

    #[Test]
    public function it_belongs_to_order(): void
    {
        $upload = new Upload;

        $this->assertInstanceOf(BelongsTo::class, $upload->order());
        $this->assertEquals(Order::class, $upload->order()->getRelated()::class);
    }

    #[Test]
    public function it_has_one_order_queue(): void
    {
        $upload = new Upload;

        $this->assertInstanceOf(HasOne::class, $upload->orderQueue());
        $this->assertEquals(OrderQueue::class, $upload->orderQueue()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_order_queues(): void
    {
        $upload = new Upload;

        $this->assertInstanceOf(HasMany::class, $upload->orderQueues());
        $this->assertEquals(OrderQueue::class, $upload->orderQueues()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_customer(): void
    {
        $upload = new Upload;

        $this->assertInstanceOf(BelongsTo::class, $upload->customer());
        $this->assertEquals(Customer::class, $upload->customer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_material(): void
    {
        $upload = new Upload;

        $this->assertInstanceOf(BelongsTo::class, $upload->material());
        $this->assertEquals(Material::class, $upload->material()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $upload = new Upload;

        $this->assertInstanceOf(BelongsTo::class, $upload->currency());
        $this->assertEquals(Currency::class, $upload->currency()->getRelated()::class);
    }
}
