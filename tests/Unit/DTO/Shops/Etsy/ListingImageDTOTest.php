<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Shops\Etsy;

use App\DTO\Shops\Etsy\ListingImageDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListingImageDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new ListingImageDTO(
            shopId: 12345,
            listingId: 100,
            image: 'https://example.com/image.jpg',
            listingImageId: 200,
            altText: 'A beautiful 3D printed model',
            rank: 1,
            overwrite: true,
            isWatermarked: true,
        );

        $this->assertEquals(12345, $dto->shopId);
        $this->assertEquals(100, $dto->listingId);
        $this->assertEquals('https://example.com/image.jpg', $dto->image);
        $this->assertEquals(200, $dto->listingImageId);
        $this->assertEquals('A beautiful 3D printed model', $dto->altText);
        $this->assertEquals(1, $dto->rank);
        $this->assertTrue($dto->overwrite);
        $this->assertTrue($dto->isWatermarked);
    }

    #[Test]
    public function it_can_be_instantiated_with_default_parameters(): void
    {
        $dto = new ListingImageDTO(
            shopId: 12345,
            listingId: null,
            image: 'https://example.com/image.png',
        );

        $this->assertEquals(12345, $dto->shopId);
        $this->assertNull($dto->listingId);
        $this->assertEquals('https://example.com/image.png', $dto->image);
        $this->assertNull($dto->listingImageId);
        $this->assertEquals('', $dto->altText);
        $this->assertEquals(1, $dto->rank);
        $this->assertFalse($dto->overwrite);
        $this->assertFalse($dto->isWatermarked);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new ListingImageDTO(
            shopId: 999,
            listingId: 50,
            image: 'https://example.com/test.jpg',
            listingImageId: 75,
            altText: 'Test image',
            rank: 3,
            overwrite: false,
            isWatermarked: false,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(999, $array['shopId']);
        $this->assertEquals(50, $array['listingId']);
        $this->assertEquals('https://example.com/test.jpg', $array['image']);
        $this->assertEquals(75, $array['listingImageId']);
        $this->assertEquals('Test image', $array['altText']);
        $this->assertEquals(3, $array['rank']);
    }

    #[Test]
    public function it_can_be_created_from_array(): void
    {
        $dto = ListingImageDTO::from([
            'shopId' => 777,
            'listingId' => 25,
            'image' => 'https://example.com/photo.jpg',
            'listingImageId' => 50,
            'altText' => 'Photo alt',
            'rank' => 5,
            'overwrite' => true,
            'isWatermarked' => false,
        ]);

        $this->assertEquals(777, $dto->shopId);
        $this->assertEquals(25, $dto->listingId);
        $this->assertEquals('https://example.com/photo.jpg', $dto->image);
        $this->assertEquals('Photo alt', $dto->altText);
        $this->assertEquals(5, $dto->rank);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new ListingImageDTO(
            shopId: 111,
            listingId: 10,
            image: 'https://example.com/webp.webp',
            listingImageId: 20,
            altText: 'WebP format',
            rank: 1,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(111, $decoded['shopId']);
        $this->assertEquals('https://example.com/webp.webp', $decoded['image']);
    }
}
