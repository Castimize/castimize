<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceExactSalesEntry;
use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $invoice = new Invoice;
        $fillable = $invoice->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('currency_id', $fillable);
        $this->assertContains('invoice_number', $fillable);
        $this->assertContains('invoice_date', $fillable);
        $this->assertContains('total', $fillable);
        $this->assertContains('total_tax', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('sent', $fillable);
        $this->assertContains('paid', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $invoice = new Invoice;
        $casts = $invoice->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['invoice_date']);
        $this->assertEquals('datetime', $casts['sent_at']);
        $this->assertEquals('datetime', $casts['paid_at']);
    }

    #[Test]
    public function it_casts_booleans_correctly(): void
    {
        $invoice = new Invoice;
        $casts = $invoice->getCasts();

        $this->assertEquals('boolean', $casts['sent']);
        $this->assertEquals('boolean', $casts['paid']);
    }

    #[Test]
    public function it_casts_meta_data_as_array_object(): void
    {
        $invoice = new Invoice;
        $casts = $invoice->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['meta_data']);
    }

    #[Test]
    public function it_converts_total_from_cents(): void
    {
        $invoice = new Invoice;
        $invoice->setRawAttributes(['total' => 10000]);

        $this->assertEquals(100.00, $invoice->total);
    }

    #[Test]
    public function it_converts_total_tax_from_cents(): void
    {
        $invoice = new Invoice;
        $invoice->setRawAttributes(['total_tax' => 2100]);

        $this->assertEquals(21.00, $invoice->total_tax);
    }

    #[Test]
    public function it_belongs_to_customer(): void
    {
        $invoice = new Invoice;

        $this->assertInstanceOf(BelongsTo::class, $invoice->customer());
        $this->assertEquals(Customer::class, $invoice->customer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $invoice = new Invoice;

        $this->assertInstanceOf(BelongsTo::class, $invoice->currency());
        $this->assertEquals(Currency::class, $invoice->currency()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_lines(): void
    {
        $invoice = new Invoice;

        $this->assertInstanceOf(HasMany::class, $invoice->lines());
        $this->assertEquals(InvoiceLine::class, $invoice->lines()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_exact_sales_entries(): void
    {
        $invoice = new Invoice;

        $this->assertInstanceOf(HasMany::class, $invoice->exactSalesEntries());
        $this->assertEquals(InvoiceExactSalesEntry::class, $invoice->exactSalesEntries()->getRelated()::class);
    }
}
