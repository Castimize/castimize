<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ReprintCulprit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReprintCulpritTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $reprintCulprit = new ReprintCulprit;
        $fillable = $reprintCulprit->getFillable();

        $this->assertContains('culprit', $fillable);
        $this->assertContains('bill_manufacturer', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $reprintCulprit = new ReprintCulprit;
        $casts = $reprintCulprit->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_bill_manufacturer_as_boolean(): void
    {
        $reprintCulprit = new ReprintCulprit;
        $casts = $reprintCulprit->getCasts();

        $this->assertEquals('boolean', $casts['bill_manufacturer']);
    }
}
