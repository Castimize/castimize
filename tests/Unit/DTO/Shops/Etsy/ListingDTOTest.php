<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Shops\Etsy;

use App\DTO\Shops\Etsy\ListingDTO;
use App\DTO\Shops\Etsy\ListingImageDTO;
use App\DTO\Shops\Etsy\ListingInventoryDTO;
use App\Enums\Admin\CurrencyEnum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\DataCollection;
use Tests\TestCase;

class ListingDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new ListingDTO(
            shopId: 12345,
            listingId: 100,
            state: 'active',
            quantity: 100,
            title: 'Amazing 3D Print',
            description: 'A beautiful 3D printed model',
            price: 2999,
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: 500,
            shippingProfileId: 50,
            returnPolicyId: 10,
            materials: collect(['PLA', 'ABS']),
            itemWeight: 0.5,
            itemLength: 10.0,
            itemWidth: 10.0,
            itemHeight: 10.0,
            itemWeightUnit: 'g',
            itemDimensionsUnit: 'cm',
            processingMin: 5,
            processingMax: 10,
            listingImages: null,
            listingInventory: null,
        );

        $this->assertEquals(12345, $dto->shopId);
        $this->assertEquals(100, $dto->listingId);
        $this->assertEquals('active', $dto->state);
        $this->assertEquals(100, $dto->quantity);
        $this->assertEquals('Amazing 3D Print', $dto->title);
        $this->assertEquals('A beautiful 3D printed model', $dto->description);
        $this->assertEquals(2999, $dto->price);
        $this->assertEquals('i_did', $dto->whoMade);
        $this->assertEquals('made_to_order', $dto->whenMade);
        $this->assertEquals(500, $dto->taxonomyId);
        $this->assertEquals(50, $dto->shippingProfileId);
        $this->assertEquals(10, $dto->returnPolicyId);
        $this->assertCount(2, $dto->materials);
        $this->assertEquals(0.5, $dto->itemWeight);
        $this->assertEquals('g', $dto->itemWeightUnit);
        $this->assertEquals('cm', $dto->itemDimensionsUnit);
        $this->assertEquals(5, $dto->processingMin);
        $this->assertEquals(10, $dto->processingMax);
        $this->assertNull($dto->listingImages);
        $this->assertNull($dto->listingInventory);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_parameters(): void
    {
        $dto = new ListingDTO(
            shopId: 12345,
            listingId: null,
            state: null,
            quantity: 50,
            title: 'Simple Product',
            description: 'A simple description',
            price: 1999,
            whoMade: null,
            whenMade: null,
            taxonomyId: 100,
            shippingProfileId: 25,
            returnPolicyId: 5,
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
            listingInventory: null,
        );

        $this->assertEquals(12345, $dto->shopId);
        $this->assertNull($dto->listingId);
        $this->assertNull($dto->state);
        $this->assertNull($dto->whoMade);
        $this->assertNull($dto->whenMade);
        $this->assertNull($dto->materials);
        $this->assertNull($dto->itemWeight);
        $this->assertNull($dto->processingMin);
        $this->assertNull($dto->processingMax);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new ListingDTO(
            shopId: 999,
            listingId: 50,
            state: 'draft',
            quantity: 10,
            title: 'Test Product',
            description: 'Test description',
            price: 1500,
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: 200,
            shippingProfileId: 25,
            returnPolicyId: 5,
            materials: collect(['Material A']),
            itemWeight: 1.0,
            itemLength: 5.0,
            itemWidth: 5.0,
            itemHeight: 5.0,
            itemWeightUnit: 'kg',
            itemDimensionsUnit: 'cm',
            processingMin: 3,
            processingMax: 7,
            listingImages: null,
            listingInventory: null,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(999, $array['shopId']);
        $this->assertEquals(50, $array['listingId']);
        $this->assertEquals('draft', $array['state']);
        $this->assertEquals('Test Product', $array['title']);
        $this->assertEquals(1500, $array['price']);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new ListingDTO(
            shopId: 111,
            listingId: 10,
            state: 'active',
            quantity: 5,
            title: 'JSON Test',
            description: 'JSON description',
            price: 2500,
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: 300,
            shippingProfileId: 30,
            returnPolicyId: 3,
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
            listingInventory: null,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(111, $decoded['shopId']);
        $this->assertEquals('JSON Test', $decoded['title']);
    }

    #[Test]
    public function it_can_have_listing_images(): void
    {
        $images = ListingImageDTO::collect([
            new ListingImageDTO(shopId: 12345, listingId: 100, image: 'img1.jpg', listingImageId: 1, altText: 'Image 1', rank: 1),
            new ListingImageDTO(shopId: 12345, listingId: 100, image: 'img2.jpg', listingImageId: 2, altText: 'Image 2', rank: 2),
        ], DataCollection::class);

        $dto = new ListingDTO(
            shopId: 222,
            listingId: 20,
            state: 'active',
            quantity: 15,
            title: 'Multi-image Product',
            description: 'Product with multiple images',
            price: 1000,
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: 400,
            shippingProfileId: 40,
            returnPolicyId: 4,
            materials: collect(['PLA']),
            itemWeight: 2.0,
            itemLength: 15.0,
            itemWidth: 15.0,
            itemHeight: 15.0,
            itemWeightUnit: 'g',
            itemDimensionsUnit: 'cm',
            processingMin: 5,
            processingMax: 10,
            listingImages: $images,
            listingInventory: null,
        );

        $this->assertCount(2, $dto->listingImages);
        $this->assertInstanceOf(DataCollection::class, $dto->listingImages);
    }

    #[Test]
    public function it_can_have_listing_inventory(): void
    {
        $inventory = ListingInventoryDTO::collect([
            new ListingInventoryDTO(listingId: 100, sku: 'SKU-A', name: 'Material A', price: 10.00, quantity: 5, currency: CurrencyEnum::USD, isEnabled: true),
            new ListingInventoryDTO(listingId: 100, sku: 'SKU-B', name: 'Material B', price: 20.00, quantity: 10, currency: CurrencyEnum::USD, isEnabled: true),
        ], DataCollection::class);

        $dto = new ListingDTO(
            shopId: 333,
            listingId: 30,
            state: 'active',
            quantity: 15,
            title: 'Multi-variant Product',
            description: 'Product with multiple variants',
            price: 1000,
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: 500,
            shippingProfileId: 50,
            returnPolicyId: 5,
            materials: collect(['PLA', 'ABS']),
            itemWeight: 1.5,
            itemLength: 10.0,
            itemWidth: 10.0,
            itemHeight: 10.0,
            itemWeightUnit: 'g',
            itemDimensionsUnit: 'cm',
            processingMin: 3,
            processingMax: 7,
            listingImages: null,
            listingInventory: $inventory,
        );

        $this->assertCount(2, $dto->listingInventory);
        $this->assertInstanceOf(DataCollection::class, $dto->listingInventory);
    }
}
