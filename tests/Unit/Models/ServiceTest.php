<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Service;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $service = new Service;
        $fillable = $service->getFillable();

        $this->assertContains('currency_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('fee', $fillable);
        $this->assertContains('currency_code', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $service = new Service;
        $casts = $service->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $service = new Service;

        $this->assertInstanceOf(BelongsTo::class, $service->currency());
        $this->assertEquals(Currency::class, $service->currency()->getRelated()::class);
    }
}
