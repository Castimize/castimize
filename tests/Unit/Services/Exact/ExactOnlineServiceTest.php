<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Exact;

use App\Enums\Admin\PaymentIssuersEnum;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Exact\ExactOnlineService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class ExactOnlineServiceTest extends TestCase
{
    use DatabaseTransactions;

    private MockInterface $connectionMock;

    private ExactOnlineService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Exact Online connection
        $this->connectionMock = Mockery::mock('Exact\Connection');

        // Bind the mock to the container
        $this->app->bind('Exact\Connection', fn () => $this->connectionMock);

        $this->service = new ExactOnlineService;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ========================================
    // findGlAccountForRevenue tests
    // ========================================

    #[Test]
    public function it_returns_gl_8000_for_nl_country(): void
    {
        $invoice = new Invoice;
        $invoice->country = 'NL';

        $result = $this->invokePrivateMethod($this->service, 'findGlAccountForRevenue', [$invoice]);

        $this->assertEquals('4de43a20-6c86-4af6-bcf5-3adec36677c9', $result);
    }

    #[Test]
    public function it_returns_gl_8100_for_eu_country_with_vat_number(): void
    {
        $invoice = new Invoice;
        $invoice->country = 'DE';
        $invoice->vat_number = 'DE123456789';

        $result = $this->invokePrivateMethod($this->service, 'findGlAccountForRevenue', [$invoice]);

        $this->assertEquals('fd7f828e-f006-4c98-ad71-5d2aaf64c474', $result);
    }

    #[Test]
    public function it_returns_gl_8120_for_eu_country_without_vat_number(): void
    {
        $invoice = new Invoice;
        $invoice->country = 'DE';
        $invoice->vat_number = null;

        $result = $this->invokePrivateMethod($this->service, 'findGlAccountForRevenue', [$invoice]);

        $this->assertEquals('07f9774b-2a55-442c-ac02-f972f7f5149f', $result);
    }

    #[Test]
    public function it_returns_gl_8110_for_non_eu_country(): void
    {
        $invoice = new Invoice;
        $invoice->country = 'US';

        $result = $this->invokePrivateMethod($this->service, 'findGlAccountForRevenue', [$invoice]);

        $this->assertEquals('744f4532-6874-4d64-86a7-1d08a305174b', $result);
    }

    #[Test]
    public function it_handles_lowercase_country_code(): void
    {
        $invoice = new Invoice;
        $invoice->country = 'nl';

        $result = $this->invokePrivateMethod($this->service, 'findGlAccountForRevenue', [$invoice]);

        $this->assertEquals('4de43a20-6c86-4af6-bcf5-3adec36677c9', $result);
    }

    // ========================================
    // findGlAccountForPaymentMethod tests
    // ========================================

    #[Test]
    public function it_returns_gl_1103_for_stripe_payment_methods(): void
    {
        $stripeMethods = PaymentIssuersEnum::getStripeMethods();

        foreach ($stripeMethods as $method) {
            $result = $this->invokePrivateMethod($this->service, 'findGlAccountForPaymentMethod', [$method]);
            $this->assertEquals('b25c4786-24aa-4db2-8c18-57bb672ccc3b', $result, "Failed for method: {$method}");
        }
    }

    #[Test]
    public function it_returns_gl_1104_for_paypal(): void
    {
        $result = $this->invokePrivateMethod($this->service, 'findGlAccountForPaymentMethod', [PaymentIssuersEnum::Paypal->value]);

        $this->assertEquals('9a56362f-2186-4d69-955a-39ee46fceb20', $result);
    }

    #[Test]
    public function it_returns_null_for_unknown_payment_method(): void
    {
        $result = $this->invokePrivateMethod($this->service, 'findGlAccountForPaymentMethod', ['unknown_method']);

        $this->assertNull($result);
    }

    // ========================================
    // getTotalInEuro tests
    // ========================================

    #[Test]
    public function it_returns_total_unchanged_when_currency_is_eur(): void
    {
        $invoice = new Invoice;
        $invoice->currency_code = 'EUR';

        $result = $this->invokePrivateMethod($this->service, 'getTotalInEuro', [
            $invoice,
            100.50,
            now(),
        ]);

        $this->assertEquals(100.50, $result);
    }

    // ========================================
    // createSalesEntryFromInvoice validation tests
    // ========================================

    #[Test]
    public function it_throws_exception_when_invoice_has_no_customer(): void
    {
        $invoice = new Invoice;
        $invoice->invoice_number = 'INV-001';
        $invoice->setRelation('customer', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invoice #INV-001 has no customer attached');

        $this->invokePrivateMethod($this->service, 'createSalesEntryFromInvoice', [
            $invoice,
            [],
            70,
            20,
            '2024-01-01',
        ]);
    }

    // ========================================
    // syncCustomer validation tests
    // ========================================

    #[Test]
    public function it_throws_exception_when_customer_has_no_wp_customer(): void
    {
        $customer = new Customer;
        $customer->id = 1;
        $customer->first_name = 'John';
        $customer->last_name = 'Doe';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Exact customer has no wpCustomer attached [1]John Doe');

        $this->service->syncCustomer($customer);
    }

    // ========================================
    // Helper methods
    // ========================================

    /**
     * Invoke a private method on an object.
     */
    private function invokePrivateMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
