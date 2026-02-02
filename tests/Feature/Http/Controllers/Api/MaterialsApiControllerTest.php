<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Currency;
use App\Models\Material;
use App\Models\MaterialGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class MaterialsApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    private Currency $currency;

    private MaterialGroup $materialGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions([]);
        $this->setUpSharedDependencies();
    }

    private function setUpSharedDependencies(): void
    {
        $this->currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );

        $this->materialGroup = MaterialGroup::firstOrCreate(
            ['name' => 'Test Material Group'],
            ['name' => 'Test Material Group']
        );
    }

    private function createMaterial(array $attributes = []): Material
    {
        return Material::factory()->create(array_merge([
            'currency_id' => $this->currency->id,
            'material_group_id' => $this->materialGroup->id,
        ], $attributes));
    }

    // ========================================
    // index() tests
    // ========================================

    #[Test]
    public function it_returns_all_materials(): void
    {
        $material1 = $this->createMaterial(['name' => 'Material A', 'wp_id' => 1001]);
        $material2 = $this->createMaterial(['name' => 'Material B', 'wp_id' => 1002]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.materials.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'wp_id',
                    'name',
                ],
            ],
        ]);
    }

    #[Test]
    public function it_returns_401_when_not_authenticated_for_index(): void
    {
        $response = $this->getJson(route('api.api.materials.index'));

        $response->assertUnauthorized();
    }

    // ========================================
    // show() tests
    // ========================================

    #[Test]
    public function it_returns_material_by_id(): void
    {
        $material = $this->createMaterial(['name' => 'Test Material', 'wp_id' => 2001]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.materials.show', ['material' => $material->id]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'wp_id',
                'name',
            ],
        ]);
        $response->assertJsonPath('data.id', $material->id);
        $response->assertJsonPath('data.name', 'Test Material');
        $response->assertJsonPath('data.wp_id', 2001);
    }

    #[Test]
    public function it_returns_404_when_material_not_found(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.materials.show', ['material' => 99999]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_returns_401_when_not_authenticated_for_show(): void
    {
        $material = $this->createMaterial();

        $response = $this->getJson(route('api.api.materials.show', ['material' => $material->id]));

        $response->assertUnauthorized();
    }
}
