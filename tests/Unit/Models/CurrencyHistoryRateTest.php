<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CurrencyHistoryRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurrencyHistoryRateTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $currencyHistoryRate = new CurrencyHistoryRate;
        $fillable = $currencyHistoryRate->getFillable();

        $this->assertContains('base_currency', $fillable);
        $this->assertContains('convert_currency', $fillable);
        $this->assertContains('rate', $fillable);
        $this->assertContains('historical_date', $fillable);
        $this->assertContains('exact_online_guid', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $currencyHistoryRate = new CurrencyHistoryRate;
        $casts = $currencyHistoryRate->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['historical_date']);
    }
}
