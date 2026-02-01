<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Enums\Admin\CurrencyEnum;
use App\Jobs\SyncExchangeRateToExact;
use App\Models\CurrencyHistoryRate;
use AshAllenDesign\LaravelExchangeRates\Classes\ExchangeRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetCurrencyHistoricalRatesTest extends TestCase
{
    use DatabaseTransactions;

    private MockInterface $exchangeRateMock;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->exchangeRateMock = Mockery::mock(ExchangeRate::class);
        $this->app->instance(ExchangeRate::class, $this->exchangeRateMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_creates_new_currency_rates(): void
    {
        $this->exchangeRateMock
            ->shouldReceive('exchangeRate')
            ->once()
            ->andReturn([
                CurrencyEnum::EUR->value => 0.92,
                'GBP' => 0.79,
            ]);

        $this->artisan('castimize:get-currency-historical-rates')
            ->assertSuccessful();

        $this->assertDatabaseHas('currency_history_rates', [
            'base_currency' => config('app.currency'),
            'convert_currency' => CurrencyEnum::EUR->value,
            'rate' => 0.92,
        ]);

        $this->assertDatabaseHas('currency_history_rates', [
            'base_currency' => config('app.currency'),
            'convert_currency' => 'GBP',
            'rate' => 0.79,
        ]);
    }

    #[Test]
    public function it_creates_rates_for_historical_date(): void
    {
        $historicalDate = '2025-01-15';

        $this->exchangeRateMock
            ->shouldReceive('exchangeRate')
            ->once()
            ->andReturn([
                CurrencyEnum::EUR->value => 0.91,
            ]);

        $this->artisan('castimize:get-currency-historical-rates', [
            '--historical-date' => $historicalDate,
        ])->assertSuccessful();

        $this->assertDatabaseHas('currency_history_rates', [
            'base_currency' => config('app.currency'),
            'convert_currency' => CurrencyEnum::EUR->value,
            'rate' => 0.91,
            'historical_date' => $historicalDate,
        ]);
    }

    #[Test]
    public function it_updates_existing_rate_instead_of_creating_duplicate(): void
    {
        $historicalDate = '2025-01-15';
        $baseCurrency = config('app.currency');

        // Create an existing rate
        CurrencyHistoryRate::factory()->create([
            'base_currency' => $baseCurrency,
            'convert_currency' => CurrencyEnum::EUR->value,
            'rate' => 0.90,
            'historical_date' => $historicalDate,
        ]);

        // Verify we have 1 record
        $this->assertEquals(1, CurrencyHistoryRate::where([
            'base_currency' => $baseCurrency,
            'convert_currency' => CurrencyEnum::EUR->value,
            'historical_date' => $historicalDate,
        ])->count());

        $this->exchangeRateMock
            ->shouldReceive('exchangeRate')
            ->once()
            ->andReturn([
                CurrencyEnum::EUR->value => 0.92, // New rate
            ]);

        // Run command with same date
        $this->artisan('castimize:get-currency-historical-rates', [
            '--historical-date' => $historicalDate,
        ])->assertSuccessful();

        // Should still have only 1 record (updated, not duplicated)
        $this->assertEquals(1, CurrencyHistoryRate::where([
            'base_currency' => $baseCurrency,
            'convert_currency' => CurrencyEnum::EUR->value,
            'historical_date' => $historicalDate,
        ])->count());

        // Rate should be updated to new value
        $this->assertDatabaseHas('currency_history_rates', [
            'base_currency' => $baseCurrency,
            'convert_currency' => CurrencyEnum::EUR->value,
            'rate' => 0.92,
            'historical_date' => $historicalDate,
        ]);
    }

    #[Test]
    public function it_dispatches_sync_job_for_eur_currency(): void
    {
        $this->exchangeRateMock
            ->shouldReceive('exchangeRate')
            ->once()
            ->andReturn([
                CurrencyEnum::EUR->value => 0.92,
                'GBP' => 0.79,
            ]);

        $this->artisan('castimize:get-currency-historical-rates')
            ->assertSuccessful();

        // Should dispatch job only for EUR
        Queue::assertPushedOn('exact', SyncExchangeRateToExact::class);
        Queue::assertPushed(SyncExchangeRateToExact::class, 1);
    }

    #[Test]
    public function it_does_not_dispatch_sync_job_for_non_eur_currencies(): void
    {
        $this->exchangeRateMock
            ->shouldReceive('exchangeRate')
            ->once()
            ->andReturn([
                'GBP' => 0.79,
                'CAD' => 1.35,
            ]);

        $this->artisan('castimize:get-currency-historical-rates')
            ->assertSuccessful();

        // Should not dispatch job for non-EUR currencies
        Queue::assertNotPushed(SyncExchangeRateToExact::class);
    }

    #[Test]
    public function it_handles_multiple_currencies(): void
    {
        $historicalDate = '2025-01-20';

        $this->exchangeRateMock
            ->shouldReceive('exchangeRate')
            ->once()
            ->andReturn([
                CurrencyEnum::EUR->value => 0.92,
                'GBP' => 0.79,
                'CAD' => 1.35,
                'AUD' => 1.52,
            ]);

        $this->artisan('castimize:get-currency-historical-rates', [
            '--historical-date' => $historicalDate,
        ])->assertSuccessful();

        $this->assertEquals(4, CurrencyHistoryRate::where('historical_date', $historicalDate)->count());
    }
}
