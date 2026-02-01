<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Model;

use App\DTO\Model\ModelDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $dto = new ModelDTO(
            wpId: '12345',
            customerId: 1,
            shopListingId: 100,
            shopTaxonomyId: 50,
            materials: [['id' => 1, 'name' => 'PLA']],
            printerId: 3,
            coatingId: 2,
            unit: 'mm',
            name: 'Test Model',
            modelName: 'Custom Name',
            fileName: 'model.stl',
            thumbName: 'model_thumb.png',
            uploadedThumb: true,
            modelVolumeCc: 15.5,
            modelXLength: 10.0,
            modelYLength: 20.0,
            modelZLength: 5.0,
            modelBoxVolume: 1000.0,
            surfaceArea: 500.0,
            modelParts: 1,
            modelScale: 1.0,
            categories: [['category' => 'Toys'], ['category' => 'Games']],
            metaData: ['key' => 'value'],
        );

        $this->assertEquals('12345', $dto->wpId);
        $this->assertEquals(1, $dto->customerId);
        $this->assertEquals(100, $dto->shopListingId);
        $this->assertEquals(50, $dto->shopTaxonomyId);
        $this->assertIsArray($dto->materials);
        $this->assertEquals(3, $dto->printerId);
        $this->assertEquals(2, $dto->coatingId);
        $this->assertEquals('mm', $dto->unit);
        $this->assertEquals('Test Model', $dto->name);
        $this->assertEquals('Custom Name', $dto->modelName);
        $this->assertEquals('model.stl', $dto->fileName);
        $this->assertEquals('model_thumb.png', $dto->thumbName);
        $this->assertTrue($dto->uploadedThumb);
        $this->assertEquals(15.5, $dto->modelVolumeCc);
        $this->assertEquals(10.0, $dto->modelXLength);
        $this->assertEquals(20.0, $dto->modelYLength);
        $this->assertEquals(5.0, $dto->modelZLength);
        $this->assertEquals(1000.0, $dto->modelBoxVolume);
        $this->assertEquals(500.0, $dto->surfaceArea);
        $this->assertEquals(1, $dto->modelParts);
        $this->assertEquals(1.0, $dto->modelScale);
        $this->assertIsArray($dto->categories);
        $this->assertCount(2, $dto->categories);
        $this->assertEquals(['key' => 'value'], $dto->metaData);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_parameters(): void
    {
        $dto = new ModelDTO(
            wpId: '99999',
            customerId: null,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: [],
            printerId: null,
            coatingId: null,
            unit: null,
            name: 'Simple Model',
            modelName: null,
            fileName: 'simple.stl',
            thumbName: null,
            uploadedThumb: false,
            modelVolumeCc: 5.0,
            modelXLength: 3.0,
            modelYLength: 3.0,
            modelZLength: 3.0,
            modelBoxVolume: 27.0,
            surfaceArea: 54.0,
            modelParts: 1,
            modelScale: null,
            categories: null,
            metaData: null,
        );

        $this->assertEquals('99999', $dto->wpId);
        $this->assertNull($dto->customerId);
        $this->assertNull($dto->shopListingId);
        $this->assertNull($dto->shopTaxonomyId);
        $this->assertEmpty($dto->materials);
        $this->assertNull($dto->printerId);
        $this->assertNull($dto->coatingId);
        $this->assertNull($dto->unit);
        $this->assertNull($dto->modelName);
        $this->assertNull($dto->thumbName);
        $this->assertFalse($dto->uploadedThumb);
        $this->assertNull($dto->modelScale);
        $this->assertNull($dto->categories);
        $this->assertNull($dto->metaData);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $dto = new ModelDTO(
            wpId: '55555',
            customerId: 10,
            shopListingId: 200,
            shopTaxonomyId: 75,
            materials: [['id' => 2, 'name' => 'ABS']],
            printerId: 5,
            coatingId: 1,
            unit: 'mm',
            name: 'Array Model',
            modelName: 'Array Test',
            fileName: 'array.stl',
            thumbName: 'array_thumb.png',
            uploadedThumb: false,
            modelVolumeCc: 25.0,
            modelXLength: 15.0,
            modelYLength: 15.0,
            modelZLength: 10.0,
            modelBoxVolume: 2250.0,
            surfaceArea: 750.0,
            modelParts: 2,
            modelScale: 1.5,
            categories: [['category' => 'Art']],
            metaData: ['version' => '1.0'],
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('55555', $array['wpId']);
        $this->assertEquals(10, $array['customerId']);
        $this->assertEquals(200, $array['shopListingId']);
        $this->assertEquals('Array Model', $array['name']);
        $this->assertEquals('array.stl', $array['fileName']);
        $this->assertEquals(25.0, $array['modelVolumeCc']);
        $this->assertEquals(2, $array['modelParts']);
        $this->assertEquals(1.5, $array['modelScale']);
    }

    #[Test]
    public function it_can_be_created_from_array(): void
    {
        $dto = ModelDTO::from([
            'wpId' => '77777',
            'customerId' => 20,
            'shopListingId' => null,
            'shopTaxonomyId' => null,
            'materials' => [],
            'printerId' => 3,
            'coatingId' => null,
            'unit' => 'mm',
            'name' => 'From Array',
            'modelName' => null,
            'fileName' => 'from_array.stl',
            'thumbName' => null,
            'uploadedThumb' => false,
            'modelVolumeCc' => 10.0,
            'modelXLength' => 5.0,
            'modelYLength' => 5.0,
            'modelZLength' => 5.0,
            'modelBoxVolume' => 125.0,
            'surfaceArea' => 150.0,
            'modelParts' => 1,
            'modelScale' => 1.0,
            'categories' => null,
            'metaData' => null,
        ]);

        $this->assertEquals('77777', $dto->wpId);
        $this->assertEquals(20, $dto->customerId);
        $this->assertEquals('From Array', $dto->name);
        $this->assertEquals('from_array.stl', $dto->fileName);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new ModelDTO(
            wpId: '88888',
            customerId: 30,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: [],
            printerId: 3,
            coatingId: null,
            unit: 'mm',
            name: 'JSON Model',
            modelName: null,
            fileName: 'json.stl',
            thumbName: null,
            uploadedThumb: false,
            modelVolumeCc: 8.0,
            modelXLength: 4.0,
            modelYLength: 4.0,
            modelZLength: 4.0,
            modelBoxVolume: 64.0,
            surfaceArea: 96.0,
            modelParts: 1,
            modelScale: 1.0,
            categories: null,
            metaData: null,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('88888', $decoded['wpId']);
        $this->assertEquals('JSON Model', $decoded['name']);
        $this->assertEquals('json.stl', $decoded['fileName']);
    }

    #[Test]
    public function it_handles_large_model_dimensions(): void
    {
        $dto = new ModelDTO(
            wpId: '11111',
            customerId: 1,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: [],
            printerId: 3,
            coatingId: null,
            unit: 'mm',
            name: 'Large Model',
            modelName: null,
            fileName: 'large.stl',
            thumbName: null,
            uploadedThumb: false,
            modelVolumeCc: 50000.0,
            modelXLength: 500.0,
            modelYLength: 500.0,
            modelZLength: 200.0,
            modelBoxVolume: 50000000.0,
            surfaceArea: 1000000.0,
            modelParts: 50,
            modelScale: 10.0,
            categories: null,
            metaData: null,
        );

        $this->assertEquals(50000.0, $dto->modelVolumeCc);
        $this->assertEquals(500.0, $dto->modelXLength);
        $this->assertEquals(500.0, $dto->modelYLength);
        $this->assertEquals(200.0, $dto->modelZLength);
        $this->assertEquals(50000000.0, $dto->modelBoxVolume);
        $this->assertEquals(50, $dto->modelParts);
    }

    #[Test]
    public function it_handles_multiple_materials(): void
    {
        $materials = [
            ['id' => 1, 'name' => 'PLA White'],
            ['id' => 2, 'name' => 'PLA Black'],
            ['id' => 3, 'name' => 'ABS Red'],
        ];

        $dto = new ModelDTO(
            wpId: '22222',
            customerId: 1,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: $materials,
            printerId: 3,
            coatingId: null,
            unit: 'mm',
            name: 'Multi-Material Model',
            modelName: null,
            fileName: 'multi.stl',
            thumbName: null,
            uploadedThumb: false,
            modelVolumeCc: 10.0,
            modelXLength: 5.0,
            modelYLength: 5.0,
            modelZLength: 5.0,
            modelBoxVolume: 125.0,
            surfaceArea: 150.0,
            modelParts: 1,
            modelScale: 1.0,
            categories: null,
            metaData: null,
        );

        $this->assertCount(3, $dto->materials);
        $this->assertEquals('PLA White', $dto->materials[0]['name']);
        $this->assertEquals('ABS Red', $dto->materials[2]['name']);
    }

    #[Test]
    public function it_handles_multiple_categories(): void
    {
        $categories = [
            ['category' => 'Toys'],
            ['category' => 'Games'],
            ['category' => 'Figurines'],
            ['category' => 'Collectibles'],
        ];

        $dto = new ModelDTO(
            wpId: '33333',
            customerId: 1,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: [],
            printerId: 3,
            coatingId: null,
            unit: 'mm',
            name: 'Multi-Category Model',
            modelName: null,
            fileName: 'categories.stl',
            thumbName: null,
            uploadedThumb: false,
            modelVolumeCc: 10.0,
            modelXLength: 5.0,
            modelYLength: 5.0,
            modelZLength: 5.0,
            modelBoxVolume: 125.0,
            surfaceArea: 150.0,
            modelParts: 1,
            modelScale: 1.0,
            categories: $categories,
            metaData: null,
        );

        $this->assertCount(4, $dto->categories);
        $this->assertEquals('Toys', $dto->categories[0]['category']);
        $this->assertEquals('Collectibles', $dto->categories[3]['category']);
    }

    #[Test]
    public function it_handles_decimal_scale_values(): void
    {
        $dto = new ModelDTO(
            wpId: '44444',
            customerId: 1,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: [],
            printerId: 3,
            coatingId: null,
            unit: 'mm',
            name: 'Scaled Model',
            modelName: null,
            fileName: 'scaled.stl',
            thumbName: null,
            uploadedThumb: false,
            modelVolumeCc: 10.0,
            modelXLength: 5.0,
            modelYLength: 5.0,
            modelZLength: 5.0,
            modelBoxVolume: 125.0,
            surfaceArea: 150.0,
            modelParts: 1,
            modelScale: 0.7532,
            categories: null,
            metaData: null,
        );

        $this->assertEquals(0.7532, $dto->modelScale);
    }
}
