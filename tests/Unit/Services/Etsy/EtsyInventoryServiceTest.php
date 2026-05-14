<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Etsy;

use App\DTO\Shops\Etsy\ListingDTO;
use App\DTO\Shops\Etsy\ListingInventoryDTO;
use App\Models\Shop;
use App\Services\Etsy\EtsyInventoryService;
use App\Services\Etsy\EtsyService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\DataCollection;
use Tests\TestCase;

class EtsyInventoryServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_skips_inventory_update_when_listing_has_more_than_one_variation_property(): void
    {
        $shop = new Shop([
            'shop_oauth' => [
                'shop_id' => 12345,
                'client_id' => 'test-client-id',
                'access_token' => 'test-token',
                'refresh_token' => 'test-refresh',
            ],
        ]);

        $existingInventory = [
            'products' => [
                [
                    'sku' => 'SKU-001',
                    'property_values' => [
                        ['property_name' => 'Material', 'values' => ['Steel']],
                        ['property_name' => 'Color', 'values' => ['Silver']],
                    ],
                    'offerings' => [
                        ['price' => ['amount' => 1000, 'divisor' => 100, 'currency_code' => 'EUR'], 'quantity' => 5, 'is_enabled' => true],
                    ],
                ],
            ],
        ];

        $inventoryService = Mockery::mock(EtsyInventoryService::class);
        $inventoryService->shouldNotReceive('updateInventory');

        $service = Mockery::mock(EtsyService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('makeInventoryService')->andReturn($inventoryService);

        $listingDTO = new ListingDTO(
            shopId: 12345,
            listingId: 99999,
            state: null,
            quantity: 1,
            title: 'Test',
            description: 'Test',
            price: 10.0,
            whoMade: null,
            whenMade: null,
            taxonomyId: 1,
            shippingProfileId: 1,
            returnPolicyId: 1,
            materials: null,
            itemWeight: null,
            itemLength: null,
            itemWidth: null,
            itemHeight: null,
            itemWeightUnit: null,
            itemDimensionsUnit: null,
            processingMin: null,
            processingMax: null,
            listingImages: null,
            listingInventory: new DataCollection(ListingInventoryDTO::class, []),
        );

        $service->updateListingInventory($shop, $listingDTO, $existingInventory);

        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }

    #[Test]
    public function it_includes_max_variations_supported_2_in_update_inventory_payload(): void
    {
        $shop = new Shop([
            'shop_oauth' => [
                'shop_id' => 12345,
                'client_id' => 'test-client-id',
                'access_token' => 'test-token',
                'refresh_token' => 'test-refresh',
            ],
        ]);

        $requestHistory = [];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['products' => []])),
        ]);

        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push(Middleware::history($requestHistory));

        $mockClient = new Client(['handler' => $handlerStack]);

        $inventoryService = new EtsyInventoryService(shop: $shop, client: $mockClient);

        $inventoryService->updateInventory(
            listingId: 99999,
            products: [
                [
                    'sku' => 'SKU-001',
                    'material' => 'Steel',
                    'price' => 10.00,
                    'quantity' => 5,
                    'is_enabled' => true,
                ],
            ],
        );

        $this->assertCount(1, $requestHistory);

        $requestBody = json_decode($requestHistory[0]['request']->getBody()->getContents(), true);

        $this->assertArrayHasKey('max_variations_supported', $requestBody);
        $this->assertSame(2, $requestBody['max_variations_supported']);
    }
}
