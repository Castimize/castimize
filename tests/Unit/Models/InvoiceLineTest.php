<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Order;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceLineTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $invoiceLine = new InvoiceLine;
        $fillable = $invoiceLine->getFillable();

        $this->assertContains('invoice_id', $fillable);
        $this->assertContains('order_id', $fillable);
        $this->assertContains('upload_id', $fillable);
        $this->assertContains('customer_id', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('total', $fillable);
        $this->assertContains('total_tax', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $invoiceLine = new InvoiceLine;
        $casts = $invoiceLine->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_meta_data_as_array_object(): void
    {
        $invoiceLine = new InvoiceLine;
        $casts = $invoiceLine->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['meta_data']);
    }

    #[Test]
    public function it_converts_total_from_cents(): void
    {
        $invoiceLine = new InvoiceLine;
        $invoiceLine->setRawAttributes(['total' => 5000]);

        $this->assertEquals(50.00, $invoiceLine->total);
    }

    #[Test]
    public function it_converts_total_tax_from_cents(): void
    {
        $invoiceLine = new InvoiceLine;
        $invoiceLine->setRawAttributes(['total_tax' => 1050]);

        $this->assertEquals(10.50, $invoiceLine->total_tax);
    }

    #[Test]
    public function it_belongs_to_invoice(): void
    {
        $invoiceLine = new InvoiceLine;

        $this->assertInstanceOf(BelongsTo::class, $invoiceLine->invoice());
        $this->assertEquals(Invoice::class, $invoiceLine->invoice()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order(): void
    {
        $invoiceLine = new InvoiceLine;

        $this->assertInstanceOf(BelongsTo::class, $invoiceLine->order());
        $this->assertEquals(Order::class, $invoiceLine->order()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_upload(): void
    {
        $invoiceLine = new InvoiceLine;

        $this->assertInstanceOf(BelongsTo::class, $invoiceLine->upload());
        $this->assertEquals(Upload::class, $invoiceLine->upload()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_customer(): void
    {
        $invoiceLine = new InvoiceLine;

        $this->assertInstanceOf(BelongsTo::class, $invoiceLine->customer());
        $this->assertEquals(Customer::class, $invoiceLine->customer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $invoiceLine = new InvoiceLine;

        $this->assertInstanceOf(BelongsTo::class, $invoiceLine->currency());
        $this->assertEquals(Currency::class, $invoiceLine->currency()->getRelated()::class);
    }
}
