<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\InvoiceExactSalesEntry;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceExactSalesEntryTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $entry = new InvoiceExactSalesEntry;
        $fillable = $entry->getFillable();

        $this->assertContains('invoice_id', $fillable);
        $this->assertContains('exact_online_guid', $fillable);
        $this->assertContains('diary', $fillable);
        $this->assertContains('exact_data', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $entry = new InvoiceExactSalesEntry;
        $casts = $entry->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_exact_data_as_array(): void
    {
        $entry = new InvoiceExactSalesEntry;
        $casts = $entry->getCasts();

        $this->assertEquals('array', $casts['exact_data']);
    }

    #[Test]
    public function it_belongs_to_invoice(): void
    {
        $entry = new InvoiceExactSalesEntry;

        $this->assertInstanceOf(BelongsTo::class, $entry->invoice());
        $this->assertEquals(Invoice::class, $entry->invoice()->getRelated()::class);
    }
}
