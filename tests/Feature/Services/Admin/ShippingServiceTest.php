<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Admin;

use App\Models\Country;
use App\Models\CustomerShipment;
use App\Models\LogisticsZone;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\Upload;
use App\Services\Admin\ShippingService;
use App\Services\Shippo\ShippoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Shippo_Object;
use Tests\TestCase;

class ShippingServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ShippingService $shippingService;

    private MockInterface $shippoServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shippoServiceMock = Mockery::mock(ShippoService::class);
        $this->app->instance(ShippoService::class, $this->shippoServiceMock);

        $this->shippingService = app(ShippingService::class);

        // Ensure NL country with logistics zone exists for shipping service
        $this->setUpShippingData();
    }

    private function setUpShippingData(): void
    {
        $logisticsZone = LogisticsZone::firstOrCreate(
            ['name' => 'Europe'],
            ['shipping_servicelevel_token' => 'ups_standard']
        );

        Country::firstOrCreate(
            ['alpha2' => 'NL'],
            [
                'name' => 'Netherlands',
                'alpha3' => 'NLD',
                'logistics_zone_id' => $logisticsZone->id,
            ]
        );
    }

    #[Test]
    public function it_throws_exception_when_all_selected_pos_have_null_uploads(): void
    {
        // Arrange
        $this->setupShippoMocksForAddressCreation();

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->selectedPOs = collect([
            $this->createMockOrderQueueWithNullUpload(),
            $this->createMockOrderQueueWithNullUpload(),
        ]);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create shipment: no valid order queue items with uploads found.');

        $this->shippingService->createShippoCustomerShipment($customerShipment);
    }

    #[Test]
    public function it_throws_exception_when_valid_upload_has_no_order(): void
    {
        // Arrange
        $this->setupShippoMocksForAddressCreation();

        // Create an upload mock without order
        $upload = Mockery::mock(Upload::class);
        $upload->shouldReceive('getAttribute')->with('order')->andReturn(null);
        $upload->shouldReceive('getAttribute')->with('total')->andReturn(10.00);

        $orderQueue = new OrderQueue;
        $orderQueue->id = 123;
        $orderQueue->setRelation('upload', $upload);

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->selectedPOs = collect([$orderQueue]);

        // Need to mock createCustomsItem since upload exists
        $this->shippoServiceMock->shouldReceive('createCustomsItem')->andReturnSelf();

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create shipment: no valid order queue items with uploads found.');

        $this->shippingService->createShippoCustomerShipment($customerShipment);
    }

    #[Test]
    public function it_skips_order_queue_items_with_null_uploads_and_processes_valid_ones(): void
    {
        // Arrange
        $this->setupShippoMocksForAddressCreation();

        $mockShipment = Mockery::mock(Shippo_Object::class);
        $mockShipment->shouldReceive('offsetGet')->with('rates')->andReturn([
            [
                'object_id' => 'rate-123',
                'servicelevel' => ['token' => 'ups_standard'],
            ],
        ]);
        $mockShipment->shouldReceive('offsetGet')->with('messages')->andReturn([]);
        $mockShipment->shouldReceive('offsetExists')->andReturn(true);

        $mockTransaction = Mockery::mock(Shippo_Object::class);
        $mockTransaction->shouldReceive('offsetGet')->with('status')->andReturn('SUCCESS');
        $mockTransaction->shouldReceive('offsetGet')->with('eta')->andReturn(null);
        $mockTransaction->shouldReceive('offsetGet')->with('tracking_number')->andReturn('1Z999AA10123456784');
        $mockTransaction->shouldReceive('offsetGet')->with('tracking_url_provider')->andReturn('https://tracking.ups.com/...');
        $mockTransaction->shouldReceive('offsetGet')->with('object_id')->andReturn('transaction-123');
        $mockTransaction->shouldReceive('offsetGet')->with('label_url')->andReturn('https://label.url/...');
        $mockTransaction->shouldReceive('offsetGet')->with('commercial_invoice_url')->andReturn(null);
        $mockTransaction->shouldReceive('offsetGet')->with('qr_code_url')->andReturn(null);
        $mockTransaction->shouldReceive('offsetExists')->andReturn(true);

        $this->shippoServiceMock->shouldReceive('createCustomsItem')->once()->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createCustomsDeclaration')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createShipment')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipment')->andReturn($mockShipment);
        $this->shippoServiceMock->shouldReceive('createLabel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getTransaction')->andReturn($mockTransaction);
        $this->shippoServiceMock->shouldReceive('toArray')->andReturn([
            'shipment' => ['object_id' => 'shipment-123'],
            'transaction' => [
                'status' => 'SUCCESS',
                'eta' => null,
                'tracking_number' => '1Z999AA10123456784',
                'tracking_url_provider' => 'https://tracking.ups.com/...',
                'object_id' => 'transaction-123',
                'label_url' => 'https://label.url/...',
                'commercial_invoice_url' => null,
                'qr_code_url' => null,
            ],
        ]);

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->selectedPOs = collect([
            $this->createMockOrderQueueWithUpload(),
            $this->createMockOrderQueueWithNullUpload(),
        ]);

        // Act
        $result = $this->shippingService->createShippoCustomerShipment($customerShipment);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('transaction', $result);
        $this->assertEquals('SUCCESS', $result['transaction']['status']);
    }

    private function createMockShippoAddress(): MockInterface
    {
        $mockShippoAddress = Mockery::mock(Shippo_Object::class);
        $mockShippoAddress->shouldReceive('offsetGet')->with('object_id')->andReturn('test-object-id');
        $mockShippoAddress->shouldReceive('offsetGet')->with('validation_results')->andReturn([
            'is_valid' => true,
            'messages' => [],
        ]);
        $mockShippoAddress->shouldReceive('offsetGet')->with('test')->andReturn(false);
        $mockShippoAddress->shouldReceive('offsetExists')->andReturn(true);

        return $mockShippoAddress;
    }

    private function setupShippoMocksForAddressCreation(): void
    {
        $mockShippoAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');
    }

    private function createCustomerShipmentWithAddresses(): CustomerShipment
    {
        $customerShipment = new CustomerShipment;
        $customerShipment->id = 1;
        $customerShipment->fromAddress = [
            'name' => 'Test From',
            'company' => 'Test Company',
            'address_line1' => 'Test Street 1',
            'address_line2' => '',
            'city' => 'Amsterdam',
            'state' => 'NH',
            'postal_code' => '1111AA',
            'country' => 'NL',
            'email' => 'test@example.com',
            'phone' => '+31612345678',
        ];
        $customerShipment->toAddress = [
            'name' => 'Test To',
            'company' => 'Test Company',
            'address_line1' => 'Test Street 2',
            'address_line2' => '',
            'city' => 'Rotterdam',
            'state' => 'ZH',
            'postal_code' => '2222BB',
            'country' => 'NL',
            'email' => 'recipient@example.com',
            'phone' => '+31687654321',
        ];
        $customerShipment->parcel = [
            'distance_unit' => 'cm',
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'mass_unit' => 'g',
            'weight' => 100,
        ];

        return $customerShipment;
    }

    private function createMockOrderQueueWithNullUpload(): OrderQueue
    {
        $orderQueue = new OrderQueue;
        $orderQueue->id = fake()->randomNumber(5);
        $orderQueue->setRelation('upload', null);

        return $orderQueue;
    }

    private function createMockOrderQueueWithUpload(): OrderQueue
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('order_number')->andReturn(12345);
        $order->shouldReceive('getAttribute')->with('currency_code')->andReturn('USD');
        $order->shouldReceive('getAttribute')->with('shipping_country')->andReturn('NL');

        $upload = Mockery::mock(Upload::class);
        $upload->shouldReceive('getAttribute')->with('order')->andReturn($order);
        $upload->shouldReceive('getAttribute')->with('total')->andReturn(10.00);

        $orderQueue = new OrderQueue;
        $orderQueue->id = fake()->randomNumber(5);
        $orderQueue->setRelation('upload', $upload);

        return $orderQueue;
    }

    #[Test]
    public function it_retries_with_corrected_address_when_ups_address_error_occurs(): void
    {
        // First createToAddress call: UPS address_error with corrected address fields
        $mockInvalidShippoAddress = Mockery::mock(Shippo_Object::class);
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('object_id')->andReturn('addr-invalid');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('validation_results')->andReturn([
            'is_valid' => false,
            'messages' => [[
                'source' => 'UPS',
                'code' => 'Address.InvalidAddress',
                'type' => 'address_error',
                'text' => 'Address not found.',
            ]],
        ]);
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('street1')->andReturn('123 CORRECTED ST');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('street_no')->andReturn('');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('city')->andReturn('CORRECTEDCITY');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('state')->andReturn('CA');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('zip')->andReturn('90210');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('country')->andReturn('NL');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('test')->andReturn(false);
        $mockInvalidShippoAddress->shouldReceive('offsetExists')->andReturn(true);

        // Second createToAddress call (validate=false): valid address
        $mockCorrectedShippoAddress = $this->createMockShippoAddress();

        $mockFromAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockFromAddress);
        $this->shippoServiceMock->shouldReceive('setToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')
            ->once()->with(true)->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')
            ->once()->with(false)->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')
            ->once()->andReturn($mockInvalidShippoAddress);
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')
            ->once()->andReturn($mockCorrectedShippoAddress);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');

        $mockShipment = Mockery::mock(Shippo_Object::class);
        $mockShipment->shouldReceive('offsetGet')->with('rates')->andReturn([
            ['object_id' => 'rate-123', 'servicelevel' => ['token' => 'ups_standard']],
        ]);
        $mockShipment->shouldReceive('offsetGet')->with('messages')->andReturn([]);
        $mockShipment->shouldReceive('offsetExists')->andReturn(true);

        $mockTransaction = Mockery::mock(Shippo_Object::class);
        $mockTransaction->shouldReceive('offsetGet')->with('status')->andReturn('SUCCESS');
        $mockTransaction->shouldReceive('offsetGet')->with('eta')->andReturn(null);
        $mockTransaction->shouldReceive('offsetGet')->with('tracking_number')->andReturn('1Z999AA10123456784');
        $mockTransaction->shouldReceive('offsetGet')->with('tracking_url_provider')->andReturn('https://tracking.ups.com/');
        $mockTransaction->shouldReceive('offsetGet')->with('object_id')->andReturn('transaction-123');
        $mockTransaction->shouldReceive('offsetGet')->with('label_url')->andReturn('https://label.url/');
        $mockTransaction->shouldReceive('offsetGet')->with('commercial_invoice_url')->andReturn(null);
        $mockTransaction->shouldReceive('offsetGet')->with('qr_code_url')->andReturn(null);
        $mockTransaction->shouldReceive('offsetExists')->andReturn(true);

        $this->shippoServiceMock->shouldReceive('createCustomsItem')->once()->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createCustomsDeclaration')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createShipment')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipment')->andReturn($mockShipment);
        $this->shippoServiceMock->shouldReceive('createLabel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getTransaction')->andReturn($mockTransaction);
        $this->shippoServiceMock->shouldReceive('toArray')->andReturn([
            'transaction' => ['status' => 'SUCCESS', 'eta' => null, 'tracking_number' => '1Z999AA10123456784',
                'tracking_url_provider' => 'https://tracking.ups.com/', 'object_id' => 'transaction-123',
                'label_url' => 'https://label.url/', 'commercial_invoice_url' => null, 'qr_code_url' => null],
        ]);

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->selectedPOs = collect([$this->createMockOrderQueueWithUpload()]);

        $result = $this->shippingService->createShippoCustomerShipment($customerShipment);

        $this->assertIsArray($result);
        $this->assertEquals('SUCCESS', $result['transaction']['status']);
    }

    #[Test]
    public function it_throws_original_error_when_corrected_address_retry_also_fails(): void
    {
        $mockInvalidShippoAddress = Mockery::mock(Shippo_Object::class);
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('validation_results')->andReturn([
            'is_valid' => false,
            'messages' => [[
                'source' => 'UPS',
                'code' => 'Address.InvalidAddress',
                'type' => 'address_error',
                'text' => 'Address not found.',
            ]],
        ]);
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('street1')->andReturn('123 CORRECTED ST');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('street_no')->andReturn('');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('city')->andReturn('CORRECTEDCITY');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('state')->andReturn('CA');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('zip')->andReturn('90210');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('country')->andReturn('NL');
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('test')->andReturn(false);
        $mockInvalidShippoAddress->shouldReceive('offsetExists')->andReturn(true);

        $mockFromAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockFromAddress);
        $this->shippoServiceMock->shouldReceive('setToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')->with(true)->once()->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')->with(false)->once()->andThrow(new \Exception('Shippo API unreachable'));
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')->once()->andReturn($mockInvalidShippoAddress);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->selectedPOs = collect([$this->createMockOrderQueueWithUpload()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The shipping to address is invalid with the following messages');

        $this->shippingService->createShippoCustomerShipment($customerShipment);
    }

    #[Test]
    public function it_throws_immediately_for_non_ups_address_errors(): void
    {
        $mockInvalidShippoAddress = Mockery::mock(Shippo_Object::class);
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('validation_results')->andReturn([
            'is_valid' => false,
            'messages' => [[
                'source' => 'SHIPPO',
                'code' => 'address.invalid',
                'type' => 'address_error',
                'text' => 'Invalid address.',
            ]],
        ]);
        $mockInvalidShippoAddress->shouldReceive('offsetGet')->with('test')->andReturn(false);
        $mockInvalidShippoAddress->shouldReceive('offsetExists')->andReturn(true);

        $mockFromAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockFromAddress);
        $this->shippoServiceMock->shouldReceive('setToAddress')->andReturnSelf();
        // createToAddress should only be called once (no retry)
        $this->shippoServiceMock->shouldReceive('createToAddress')->once()->with(true)->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')->once()->andReturn($mockInvalidShippoAddress);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->selectedPOs = collect([$this->createMockOrderQueueWithUpload()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The shipping to address is invalid with the following messages');

        $this->shippingService->createShippoCustomerShipment($customerShipment);
    }

    #[Test]
    public function it_does_not_retry_for_address_warning_messages(): void
    {
        $mockShippoAddressWithWarning = Mockery::mock(Shippo_Object::class);
        $mockShippoAddressWithWarning->shouldReceive('offsetGet')->with('validation_results')->andReturn([
            'is_valid' => true,
            'messages' => [[
                'source' => 'UPS',
                'code' => 'Address.Warning',
                'type' => 'address_warning',
                'text' => 'Address may be incorrect.',
            ]],
        ]);
        $mockShippoAddressWithWarning->shouldReceive('offsetGet')->with('test')->andReturn(false);
        $mockShippoAddressWithWarning->shouldReceive('offsetExists')->andReturn(true);

        $mockFromAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockFromAddress);
        $this->shippoServiceMock->shouldReceive('setToAddress')->andReturnSelf();
        // createToAddress with true called once, never with false (no retry for warnings)
        $this->shippoServiceMock->shouldReceive('createToAddress')->once()->with(true)->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')->andReturn($mockShippoAddressWithWarning);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        // Minimal selectedPOs to trigger the "no valid uploads" path without extra mocking
        $customerShipment->selectedPOs = collect([$this->createMockOrderQueueWithNullUpload()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create shipment: no valid order queue items with uploads found.');

        $this->shippingService->createShippoCustomerShipment($customerShipment);
    }

    #[Test]
    public function it_maps_null_postal_code_to_empty_string(): void
    {
        $capturedToAddress = null;

        $mockShippoAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setToAddress')
            ->withArgs(function (array $address) use (&$capturedToAddress): bool {
                $capturedToAddress = $address;

                return true;
            })
            ->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->toAddress['postal_code'] = null;

        $customerShipment->selectedPOs = collect([
            $this->createMockOrderQueueWithNullUpload(),
        ]);

        $this->shippoServiceMock->shouldReceive('createCustomsItem')->zeroOrMoreTimes()->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createCustomsDeclaration')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createShipment')->andReturnSelf();

        $mockShipment = Mockery::mock(Shippo_Object::class);
        $mockShipment->shouldReceive('offsetGet')->with('rates')->andReturn([]);
        $mockShipment->shouldReceive('offsetGet')->with('messages')->andReturn([]);
        $mockShipment->shouldReceive('offsetExists')->andReturn(true);

        $this->shippoServiceMock->shouldReceive('getShipment')->andReturn($mockShipment);

        try {
            $this->shippingService->createShippoCustomerShipment($customerShipment);
        } catch (\Throwable) {
            // We only care that setToAddress was called with zip = ''
        }

        $this->assertNotNull($capturedToAddress, 'setToAddress was not called');
        $this->assertSame('', $capturedToAddress['zip']);
    }

    #[Test]
    public function it_maps_null_phone_to_empty_string(): void
    {
        $capturedToAddress = null;

        $mockShippoAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setToAddress')
            ->withArgs(function (array $address) use (&$capturedToAddress): bool {
                $capturedToAddress = $address;

                return true;
            })
            ->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->toAddress['phone'] = null;

        $customerShipment->selectedPOs = collect([
            $this->createMockOrderQueueWithNullUpload(),
        ]);

        $this->shippoServiceMock->shouldReceive('createCustomsItem')->zeroOrMoreTimes()->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createCustomsDeclaration')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createShipment')->andReturnSelf();

        $mockShipment = Mockery::mock(Shippo_Object::class);
        $mockShipment->shouldReceive('offsetGet')->with('rates')->andReturn([]);
        $mockShipment->shouldReceive('offsetGet')->with('messages')->andReturn([]);
        $mockShipment->shouldReceive('offsetExists')->andReturn(true);

        $this->shippoServiceMock->shouldReceive('getShipment')->andReturn($mockShipment);

        try {
            $this->shippingService->createShippoCustomerShipment($customerShipment);
        } catch (\Throwable) {
            // We only care that setToAddress was called with phone = ''
        }

        $this->assertNotNull($capturedToAddress, 'setToAddress was not called');
        $this->assertSame('', $capturedToAddress['phone']);
    }

    #[Test]
    public function it_maps_empty_company_to_null(): void
    {
        $capturedToAddress = null;

        $mockShippoAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setToAddress')
            ->withArgs(function (array $address) use (&$capturedToAddress): bool {
                $capturedToAddress = $address;

                return true;
            })
            ->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->toAddress['company'] = '';

        $customerShipment->selectedPOs = collect([
            $this->createMockOrderQueueWithNullUpload(),
        ]);

        $this->shippoServiceMock->shouldReceive('createCustomsItem')->zeroOrMoreTimes()->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createCustomsDeclaration')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createShipment')->andReturnSelf();

        $mockShipment = Mockery::mock(Shippo_Object::class);
        $mockShipment->shouldReceive('offsetGet')->with('rates')->andReturn([]);
        $mockShipment->shouldReceive('offsetGet')->with('messages')->andReturn([]);
        $mockShipment->shouldReceive('offsetExists')->andReturn(true);

        $this->shippoServiceMock->shouldReceive('getShipment')->andReturn($mockShipment);

        try {
            $this->shippingService->createShippoCustomerShipment($customerShipment);
        } catch (\Throwable) {
            // We only care that setToAddress was called with company = null
        }

        $this->assertNotNull($capturedToAddress, 'setToAddress was not called');
        $this->assertNull($capturedToAddress['company']);
    }

    #[Test]
    public function it_maps_null_company_to_null(): void
    {
        $capturedToAddress = null;

        $mockShippoAddress = $this->createMockShippoAddress();

        $this->shippoServiceMock->shouldReceive('setFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setToAddress')
            ->withArgs(function (array $address) use (&$capturedToAddress): bool {
                $capturedToAddress = $address;

                return true;
            })
            ->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getShipmentFromAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('getShipmentToAddress')->andReturn($mockShippoAddress);
        $this->shippoServiceMock->shouldReceive('setShipmentFromAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('setShipmentToAddress')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createParcel')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('getCacheKey')->andReturn('test-cache-key');

        $customerShipment = $this->createCustomerShipmentWithAddresses();
        $customerShipment->toAddress['company'] = null;

        $customerShipment->selectedPOs = collect([
            $this->createMockOrderQueueWithNullUpload(),
        ]);

        $this->shippoServiceMock->shouldReceive('createCustomsItem')->zeroOrMoreTimes()->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createCustomsDeclaration')->andReturnSelf();
        $this->shippoServiceMock->shouldReceive('createShipment')->andReturnSelf();

        $mockShipment = Mockery::mock(Shippo_Object::class);
        $mockShipment->shouldReceive('offsetGet')->with('rates')->andReturn([]);
        $mockShipment->shouldReceive('offsetGet')->with('messages')->andReturn([]);
        $mockShipment->shouldReceive('offsetExists')->andReturn(true);

        $this->shippoServiceMock->shouldReceive('getShipment')->andReturn($mockShipment);

        try {
            $this->shippingService->createShippoCustomerShipment($customerShipment);
        } catch (\Throwable) {
            // We only care that setToAddress was called with company = null
        }

        $this->assertNotNull($capturedToAddress, 'setToAddress was not called');
        $this->assertNull($capturedToAddress['company']);
    }
}
