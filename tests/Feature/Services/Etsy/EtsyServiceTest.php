<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Etsy;

use App\DTO\Shops\Etsy\ListingDTO;
use App\DTO\Shops\Etsy\ListingInventoryDTO;
use App\Enums\Admin\CurrencyEnum;
use App\Models\Shop;
use App\Services\Etsy\EtsyInventoryService;
use App\Services\Etsy\EtsyService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\DataCollection;
use Tests\TestCase;

class EtsyServiceTest extends TestCase
{
    private EtsyService $etsyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->etsyService = new EtsyService;
    }

    #[Test]
    public function it_disables_material_removed_from_model_during_inventory_update(): void
    {
        // Arrange: listing has Steel + Aluminium in Etsy, but model only has Steel now
        $shop = $this->makeShop();

        $listingDTO = $this->makeListingDTO($shop, [
            ['name' => 'Steel', 'sku' => 'CAST-STL-001', 'price' => 10.00],
        ]);

        $existingInventory = [
            'products' => [
                $this->makeEtsyProduct('Steel', 'CAST-STL-001', 10.00, true),
                $this->makeEtsyProduct('Aluminium', 'CAST-ALU-002', 12.00, true),
            ],
        ];

        $capturedProducts = [];
        $this->mockInventoryService($shop, $capturedProducts);

        // Act
        $this->etsyService->updateListingInventory($shop, $listingDTO, $existingInventory);

        // Assert: Steel is kept, Aluminium is disabled
        $this->assertCount(2, $capturedProducts);

        $steel = collect($capturedProducts)->firstWhere('material', 'Steel');
        $aluminium = collect($capturedProducts)->firstWhere('material', 'Aluminium');

        $this->assertNotNull($steel, 'Steel should still be in variations');
        $this->assertTrue($steel['is_enabled'], 'Steel should remain enabled');

        $this->assertNotNull($aluminium, 'Aluminium should still be sent to Etsy (to disable it)');
        $this->assertFalse($aluminium['is_enabled'], 'Aluminium should be disabled because it was removed from the model');
    }

    #[Test]
    public function it_keeps_existing_sku_price_and_quantity_from_etsy_for_active_materials(): void
    {
        $shop = $this->makeShop();

        $listingDTO = $this->makeListingDTO($shop, [
            ['name' => 'Steel', 'sku' => 'CAST-STL-NEW', 'price' => 5.00],
        ]);

        $existingInventory = [
            'products' => [
                $this->makeEtsyProduct('Steel', 'CAST-STL-OLD', 99.00, true, 50),
            ],
        ];

        $capturedProducts = [];
        $this->mockInventoryService($shop, $capturedProducts);

        $this->etsyService->updateListingInventory($shop, $listingDTO, $existingInventory);

        $steel = collect($capturedProducts)->firstWhere('material', 'Steel');

        $this->assertEquals('CAST-STL-OLD', $steel['sku'], 'SKU should be preserved from Etsy');
        $this->assertEquals(99.00, $steel['price'], 'Price should be preserved from Etsy');
        $this->assertEquals(50, $steel['quantity'], 'Quantity should be preserved from Etsy');
    }

    #[Test]
    public function it_adds_new_material_not_yet_in_etsy(): void
    {
        $shop = $this->makeShop();

        $listingDTO = $this->makeListingDTO($shop, [
            ['name' => 'Steel', 'sku' => 'CAST-STL-001', 'price' => 10.00],
            ['name' => 'Bronze', 'sku' => 'CAST-BRZ-003', 'price' => 15.00],
        ]);

        $existingInventory = [
            'products' => [
                $this->makeEtsyProduct('Steel', 'CAST-STL-001', 10.00, true),
            ],
        ];

        $capturedProducts = [];
        $this->mockInventoryService($shop, $capturedProducts);

        $this->etsyService->updateListingInventory($shop, $listingDTO, $existingInventory);

        $materials = array_column($capturedProducts, 'material');

        $this->assertContains('Steel', $materials);
        $this->assertContains('Bronze', $materials);
        $this->assertCount(2, $capturedProducts);
    }

    #[Test]
    public function it_disables_multiple_removed_materials(): void
    {
        $shop = $this->makeShop();

        // Only Bronze remains active
        $listingDTO = $this->makeListingDTO($shop, [
            ['name' => 'Bronze', 'sku' => 'CAST-BRZ-003', 'price' => 15.00],
        ]);

        $existingInventory = [
            'products' => [
                $this->makeEtsyProduct('Steel', 'CAST-STL-001', 10.00, true),
                $this->makeEtsyProduct('Aluminium', 'CAST-ALU-002', 12.00, true),
                $this->makeEtsyProduct('Bronze', 'CAST-BRZ-003', 15.00, true),
            ],
        ];

        $capturedProducts = [];
        $this->mockInventoryService($shop, $capturedProducts);

        $this->etsyService->updateListingInventory($shop, $listingDTO, $existingInventory);

        $this->assertCount(3, $capturedProducts);

        $steel = collect($capturedProducts)->firstWhere('material', 'Steel');
        $aluminium = collect($capturedProducts)->firstWhere('material', 'Aluminium');
        $bronze = collect($capturedProducts)->firstWhere('material', 'Bronze');

        $this->assertFalse($steel['is_enabled'], 'Steel should be disabled');
        $this->assertFalse($aluminium['is_enabled'], 'Aluminium should be disabled');
        $this->assertTrue($bronze['is_enabled'], 'Bronze (enabled in Etsy) should remain enabled');
    }

    #[Test]
    public function it_handles_empty_existing_inventory(): void
    {
        $shop = $this->makeShop();

        $listingDTO = $this->makeListingDTO($shop, [
            ['name' => 'Steel', 'sku' => 'CAST-STL-001', 'price' => 10.00],
        ]);

        $capturedProducts = [];
        $this->mockInventoryService($shop, $capturedProducts);

        $this->etsyService->updateListingInventory($shop, $listingDTO, []);

        $this->assertCount(1, $capturedProducts);
        $this->assertEquals('Steel', $capturedProducts[0]['material']);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeShop(): Shop
    {
        $shop = new Shop;
        $shop->id = 1;
        $shop->setRawAttributes([
            'shop_oauth' => json_encode([
                'access_token' => 'test-token',
                'client_id' => 'test-client',
                'shop_id' => 12345,
                'shop_currency' => 'USD',
            ]),
        ]);

        return $shop;
    }

    private function makeListingDTO(Shop $shop, array $materials): ListingDTO
    {
        $inventoryItems = collect($materials)->map(fn (array $m) => new ListingInventoryDTO(
            listingId: 999,
            sku: $m['sku'],
            name: $m['name'],
            price: $m['price'],
            quantity: 999,
            currency: CurrencyEnum::USD,
            isEnabled: false,
        ));

        return new ListingDTO(
            shopId: 12345,
            listingId: 999,
            state: 'active',
            quantity: 999,
            title: 'Test Listing',
            description: 'Test',
            price: $materials[0]['price'],
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: 1,
            shippingProfileId: 1,
            returnPolicyId: 1,
            materials: collect(array_column($materials, 'name')),
            itemWeight: 1.0,
            itemLength: 1.0,
            itemWidth: 1.0,
            itemHeight: 1.0,
            itemWeightUnit: 'g',
            itemDimensionsUnit: 'cm',
            processingMin: 1,
            processingMax: 10,
            listingImages: null,
            listingInventory: new DataCollection(
                ListingInventoryDTO::class,
                $inventoryItems->all()
            ),
        );
    }

    private function makeEtsyProduct(string $material, string $sku, float $price, bool $isEnabled, int $quantity = 999): array
    {
        return [
            'sku' => $sku,
            'property_values' => [
                [
                    'property_id' => 514,
                    'property_name' => 'Material',
                    'values' => [$material],
                ],
            ],
            'offerings' => [
                [
                    'price' => [
                        'amount' => (int) ($price * 100),
                        'divisor' => 100,
                        'currency_code' => 'USD',
                    ],
                    'quantity' => $quantity,
                    'is_enabled' => $isEnabled,
                ],
            ],
        ];
    }

    private function mockInventoryService(Shop $shop, array &$capturedProducts): void
    {
        $mock = Mockery::mock(EtsyInventoryService::class);
        $mock->shouldReceive('updateInventory')
            ->once()
            ->withArgs(function (int $listingId, array $products, mixed $readinessStateId) use (&$capturedProducts): bool {
                $capturedProducts = $products;

                return true;
            })
            ->andReturn(null);

        $this->app->bind(EtsyInventoryService::class, fn () => $mock);
    }
}
