<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Admin;

use App\Models\CurrencyHistoryRate;
use App\Services\Admin\CurrencyService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();

        // Set app currency to USD (matching config)
        config(['app.currency' => 'USD']);

        $this->currencyService = new CurrencyService;
    }

    #[Test]
    public function it_returns_same_amount_when_currencies_are_equal(): void
    {
        $result = $this->currencyService->convertCurrency('USD', 'USD', 100.00);

        $this->assertEquals(100.00, $result);
    }

    #[Test]
    public function it_converts_currency_using_exact_date_rate(): void
    {
        $date = Carbon::parse('2025-01-15');

        // Rate: 1 USD = 0.91 EUR (or 1 EUR = 1.10 USD)
        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'EUR',
            'rate' => 0.91,
            'historical_date' => $date->format('Y-m-d'),
        ]);

        // Convert 100 EUR to USD: 100 / 0.91 = ~109.89
        $result = $this->currencyService->convertCurrency('EUR', 'USD', 100.00, $date);

        $this->assertEqualsWithDelta(109.89, $result, 0.01);
    }

    #[Test]
    public function it_converts_currency_from_base_to_convert(): void
    {
        $date = Carbon::parse('2025-01-15');

        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'EUR',
            'rate' => 0.91,
            'historical_date' => $date->format('Y-m-d'),
        ]);

        // Convert 100 USD to EUR: 100 * 0.91 = 91
        $result = $this->currencyService->convertCurrency('USD', 'EUR', 100.00, $date);

        $this->assertEquals(91.00, $result);
    }

    #[Test]
    public function it_falls_back_to_most_recent_rate_when_exact_date_not_found(): void
    {
        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'EUR',
            'rate' => 0.95,
            'historical_date' => Carbon::parse('2025-01-10')->format('Y-m-d'),
        ]);

        // Convert 100 EUR to USD using fallback rate: 100 / 0.95 = ~105.26
        $result = $this->currencyService->convertCurrency('EUR', 'USD', 100.00, Carbon::parse('2025-01-15'));

        $this->assertEqualsWithDelta(105.26, $result, 0.01);
    }

    #[Test]
    public function it_returns_original_amount_when_no_rate_found(): void
    {
        $result = $this->currencyService->convertCurrency('USD', 'GBP', 100.00, Carbon::parse('2025-01-15'));

        $this->assertEquals(100.00, $result);
    }

    #[Test]
    public function it_preloads_rates_and_uses_cache(): void
    {
        $date = Carbon::parse('2025-01-15');

        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'EUR',
            'rate' => 0.91,
            'historical_date' => $date->format('Y-m-d'),
        ]);

        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'GBP',
            'rate' => 0.79,
            'historical_date' => $date->format('Y-m-d'),
        ]);

        $this->currencyService->preloadRates(['EUR', 'GBP'], $date);

        // These conversions should use cached rates (no additional queries)
        // EUR to USD: 100 / 0.91 = ~109.89
        $this->assertEqualsWithDelta(109.89, $this->currencyService->convertCurrency('EUR', 'USD', 100.00, $date), 0.01);
        // GBP to USD: 100 / 0.79 = ~126.58
        $this->assertEqualsWithDelta(126.58, $this->currencyService->convertCurrency('GBP', 'USD', 100.00, $date), 0.01);
    }

    #[Test]
    public function it_preloads_rates_for_multiple_dates(): void
    {
        $date1 = Carbon::parse('2025-01-15');
        $date2 = Carbon::parse('2025-01-16');

        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'EUR',
            'rate' => 0.91,
            'historical_date' => $date1->format('Y-m-d'),
        ]);

        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'EUR',
            'rate' => 0.89,
            'historical_date' => $date2->format('Y-m-d'),
        ]);

        $this->currencyService->preloadRates(['EUR'], null, [$date1, $date2]);

        // EUR to USD on date1: 100 / 0.91 = ~109.89
        $this->assertEqualsWithDelta(109.89, $this->currencyService->convertCurrency('EUR', 'USD', 100.00, $date1), 0.01);
        // EUR to USD on date2: 100 / 0.89 = ~112.36
        $this->assertEqualsWithDelta(112.36, $this->currencyService->convertCurrency('EUR', 'USD', 100.00, $date2), 0.01);
    }

    #[Test]
    public function it_clears_preloaded_rates(): void
    {
        $date = Carbon::parse('2025-01-15');

        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'EUR',
            'rate' => 0.91,
            'historical_date' => $date->format('Y-m-d'),
        ]);

        $this->currencyService->preloadRates(['EUR'], $date);
        $this->currencyService->clearPreloadedRates();

        // After clearing, cache should be empty (would need to query again)
        // The result should still be correct as it falls back to DB query
        $result = $this->currencyService->convertCurrency('EUR', 'USD', 100.00, $date);
        $this->assertEqualsWithDelta(109.89, $result, 0.01);
    }

    #[Test]
    public function it_reduces_queries_when_rates_are_preloaded(): void
    {
        $date = Carbon::parse('2025-01-15');

        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'EUR',
            'rate' => 0.91,
            'historical_date' => $date->format('Y-m-d'),
        ]);

        CurrencyHistoryRate::factory()->create([
            'base_currency' => 'USD',
            'convert_currency' => 'GBP',
            'rate' => 0.79,
            'historical_date' => $date->format('Y-m-d'),
        ]);

        // Preload rates first
        $this->currencyService->preloadRates(['EUR', 'GBP'], $date);

        // Count queries during conversions
        $queryCount = 0;
        \DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        // Multiple conversions should use cache (no queries)
        $this->currencyService->convertCurrency('EUR', 'USD', 100.00, $date);
        $this->currencyService->convertCurrency('GBP', 'USD', 100.00, $date);
        $this->currencyService->convertCurrency('EUR', 'USD', 200.00, $date);
        $this->currencyService->convertCurrency('GBP', 'USD', 200.00, $date);

        $this->assertEquals(0, $queryCount, 'Preloaded rates should not trigger additional queries');
    }
}
