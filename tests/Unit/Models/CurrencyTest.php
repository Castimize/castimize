<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $currency = new Currency;
        $fillable = $currency->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('symbol', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $currency = new Currency;
        $casts = $currency->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_can_be_instantiated_with_attributes(): void
    {
        $currency = new Currency;
        $currency->name = 'Test Dollar';
        $currency->code = 'TSD';
        $currency->symbol = '$';

        $this->assertEquals('Test Dollar', $currency->name);
        $this->assertEquals('TSD', $currency->code);
        $this->assertEquals('$', $currency->symbol);
    }
}
