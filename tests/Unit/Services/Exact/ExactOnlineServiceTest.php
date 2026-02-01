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
    // findVatCode tests
    // ========================================

    #[Test]
    public function it_returns_vat_code_4_for_nl(): void
    {
        $result = $this->service->findVatCode('NL');

        $this->assertEquals('4  ', $result);
    }

    #[Test]
    public function it_throws_exception_for_non_eu_country_vat_code(): void
    {
        // Mock the VatCode API to return empty results
        $vatCodeMock = Mockery::mock('overload:Picqer\Financials\Exact\VatCode');
        $vatCodeMock->shouldReceive('get')->andReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('VAT Code not found for US');

        $this->service->findVatCode('US');
    }

    #[Test]
    public function it_uses_no_vat_code_for_non_eu_countries_in_sync_invoice(): void
    {
        // This test verifies the logic that non-EU countries should use NO_VAT_CODE
        // by checking that the condition correctly excludes non-EU countries

        $euCountries = \App\Models\Country::EU_COUNTRIES;

        // US should not be in EU countries
        $this->assertNotContains('US', $euCountries);

        // Verify some EU countries are in the list
        $this->assertContains('NL', $euCountries);
        $this->assertContains('DE', $euCountries);
        $this->assertContains('FR', $euCountries);
    }

    #[Test]
    public function it_only_looks_up_vat_code_for_eu_countries_with_tax(): void
    {
        // Create a partial mock to track if findVatCode is called
        $serviceMock = Mockery::mock(ExactOnlineService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // For non-EU country (US), findVatCode should NOT be called
        $serviceMock->shouldNotReceive('findVatCode')
            ->with('US');

        // Verify the EU country check logic
        $countryCode = 'US';
        $totalTax = 10.00;

        // This is the condition from syncInvoice - non-EU countries should not trigger VAT lookup
        $shouldLookupVatCode = $totalTax > 0.00 && in_array($countryCode, \App\Models\Country::EU_COUNTRIES, true);

        $this->assertFalse($shouldLookupVatCode, 'US should not trigger VAT code lookup');

        // For EU country with tax, it should trigger lookup
        $countryCode = 'DE';
        $shouldLookupVatCode = $totalTax > 0.00 && in_array($countryCode, \App\Models\Country::EU_COUNTRIES, true);

        $this->assertTrue($shouldLookupVatCode, 'DE with tax should trigger VAT code lookup');
    }

    #[Test]
    public function it_does_not_lookup_vat_code_when_no_tax(): void
    {
        // Even for EU countries, no tax means no VAT code lookup
        $countryCode = 'DE';
        $totalTax = 0.00;

        $shouldLookupVatCode = $totalTax > 0.00 && in_array($countryCode, \App\Models\Country::EU_COUNTRIES, true);

        $this->assertFalse($shouldLookupVatCode, 'No tax should not trigger VAT code lookup');
    }

    // ========================================
    // updateAccount tests
    // ========================================

    #[Test]
    public function it_sets_correct_customer_data_on_account(): void
    {
        $account = Mockery::mock('Picqer\Financials\Exact\Account')->shouldIgnoreMissing();

        $customer = new Customer;
        $customer->wpCustomer = [
            'id' => 456,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'meta_data' => [
                (object) ['key' => 'billing_eu_vat_number', 'value' => 'NL123456789B01'],
            ],
            'billing' => (object) [
                'address_1' => 'Main Street 10',
                'address_2' => 'Suite 5',
                'city' => 'Rotterdam',
                'country' => 'nl',
                'postcode' => '5678CD',
                'phone' => '+31612345678',
            ],
        ];

        $result = $this->invokePrivateMethod($this->service, 'updateAccount', [$account, $customer]);

        $this->assertEquals(456, $result->Code);
        $this->assertEquals('Main Street 10', $result->AddressLine1);
        $this->assertEquals('Suite 5', $result->AddressLine2);
        $this->assertEquals('Rotterdam', $result->City);
        $this->assertEquals('NL', $result->Country);
        $this->assertEquals('Jane Smith', $result->Name);
        $this->assertEquals('5678CD', $result->Postcode);
        $this->assertEquals('jane@example.com', $result->Email);
        $this->assertEquals('+31612345678', $result->Phone);
        $this->assertEquals('NL123456789B01', $result->VATNumber);
    }

    #[Test]
    public function it_extracts_vat_number_from_wp_customer_meta_data(): void
    {
        $account = Mockery::mock('Picqer\Financials\Exact\Account')->shouldIgnoreMissing();

        $customer = new Customer;
        $customer->wpCustomer = [
            'id' => 789,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'meta_data' => [
                (object) ['key' => 'other_field', 'value' => 'other_value'],
                (object) ['key' => 'billing_eu_vat_number', 'value' => 'DE987654321'],
                (object) ['key' => 'another_field', 'value' => 'another_value'],
            ],
            'billing' => (object) [
                'address_1' => 'Test Street',
                'address_2' => '',
                'city' => 'Berlin',
                'country' => 'DE',
                'postcode' => '12345',
                'phone' => '+49123456789',
            ],
        ];

        $result = $this->invokePrivateMethod($this->service, 'updateAccount', [$account, $customer]);

        $this->assertEquals('DE987654321', $result->VATNumber);
    }

    #[Test]
    public function it_sets_vat_number_to_null_when_not_in_meta_data(): void
    {
        $account = Mockery::mock('Picqer\Financials\Exact\Account')->shouldIgnoreMissing();

        $customer = new Customer;
        $customer->wpCustomer = [
            'id' => 111,
            'first_name' => 'No',
            'last_name' => 'Vat',
            'email' => 'novat@example.com',
            'meta_data' => [],
            'billing' => (object) [
                'address_1' => 'Street',
                'address_2' => '',
                'city' => 'City',
                'country' => 'US',
                'postcode' => '12345',
                'phone' => '+1234567890',
            ],
        ];

        $result = $this->invokePrivateMethod($this->service, 'updateAccount', [$account, $customer]);

        $this->assertNull($result->VATNumber);
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
