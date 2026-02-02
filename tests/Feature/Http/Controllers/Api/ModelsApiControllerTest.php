<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\MaterialGroup;
use App\Models\Model;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class ModelsApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    private Currency $currency;

    private MaterialGroup $materialGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions(['viewModel']);
        $this->setUpSharedDependencies();
    }

    private function setUpSharedDependencies(): void
    {
        // Use existing currency or create one with unique code
        $this->currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );

        // Use existing material group or create one
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

    private function createModel(array $attributes = []): Model
    {
        $material = $this->createMaterial();

        return Model::factory()->create(array_merge([
            'material_id' => $material->id,
        ], $attributes));
    }

    // ========================================
    // show() tests
    // ========================================

    #[Test]
    public function it_returns_model_by_id(): void
    {
        $customer = Customer::factory()->create();
        $model = $this->createModel(['customer_id' => $customer->id]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.models.show', [
            'customerId' => $customer->wp_id,
            'model' => $model->id,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'file_name',
                'file_thumbnail',
                'model_volume_cc',
                'model_x_length',
                'model_y_length',
                'model_z_length',
            ],
        ]);
        $response->assertJsonPath('data.id', $model->id);
    }

    #[Test]
    public function it_returns_403_when_user_lacks_permission_for_show(): void
    {
        $customer = Customer::factory()->create();
        $model = $this->createModel(['customer_id' => $customer->id]);

        $userWithoutPermission = User::factory()->create();
        Sanctum::actingAs($userWithoutPermission);

        $response = $this->getJson(route('api.api.models.show', [
            'customerId' => $customer->wp_id,
            'model' => $model->id,
        ]));

        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_404_when_model_belongs_to_different_customer(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        $model = $this->createModel(['customer_id' => $customer2->id]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.models.show', [
            'customerId' => $customer1->wp_id,
            'model' => $model->id,
        ]));

        $response->assertNotFound();
    }

    // ========================================
    // showModelsWpCustomer() tests
    // ========================================

    #[Test]
    public function it_returns_models_for_wp_customer(): void
    {
        $customer = Customer::factory()->create();
        $this->createModel(['customer_id' => $customer->id]);
        $this->createModel(['customer_id' => $customer->id]);
        $this->createModel(['customer_id' => $customer->id]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.models.show-customer-wp-models', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'file_name',
                ],
            ],
        ]);
        $response->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_returns_404_when_customer_not_found(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.models.show-customer-wp-models', [
            'customerId' => 99999,
        ]));

        $response->assertNotFound();
    }

    #[Test]
    public function it_deduplicates_models_with_same_dimensions(): void
    {
        $customer = Customer::factory()->create();
        $material = $this->createMaterial();

        // Create two models with identical dimensions (should be deduplicated)
        $sharedAttributes = [
            'customer_id' => $customer->id,
            'material_id' => $material->id,
            'model_name' => 'TestModel',
            'name' => 'test.stl',
            'model_volume_cc' => 10.5,
            'model_surface_area_cm2' => 20.5,
            'model_box_volume' => 30.5,
            'model_x_length' => 5.0,
            'model_y_length' => 5.0,
            'model_z_length' => 5.0,
        ];

        Model::factory()->create($sharedAttributes);
        Model::factory()->create($sharedAttributes);

        // Create one model with different dimensions
        $this->createModel([
            'customer_id' => $customer->id,
            'model_volume_cc' => 99.9,
        ]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.models.show-customer-wp-models', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertOk();
        // Should return 2 models (2 duplicates deduplicated to 1, plus 1 unique)
        $response->assertJsonCount(2, 'data');
    }

    // ========================================
    // getCustomModelAttributes() tests
    // ========================================

    #[Test]
    public function it_returns_custom_model_attributes(): void
    {
        $customer = Customer::factory()->create();
        $material = $this->createMaterial(['wp_id' => 100]);

        $model = Model::factory()->create([
            'customer_id' => $customer->id,
            'material_id' => $material->id,
            'name' => 'original_model.stl',
            'model_name' => 'My Custom Model',
            'model_scale' => 1,
            'thumb_name' => 'thumb.png',
        ]);
        $model->materials()->attach($material->id);

        Sanctum::actingAs($this->apiUser);

        $uploads = [
            'item_1' => [
                '3dp_options' => [
                    'material_id' => $material->id,
                    'material_name' => '100. Test Material',
                    'model_name_original' => 'original_model.stl',
                    'scale' => 1,
                ],
            ],
        ];

        $response = $this->postJson(
            route('api.api.models.get-custom-model-attributes', ['customerId' => $customer->wp_id]),
            ['uploads' => json_encode($uploads)]
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'item_1' => [
                '3dp_options' => [
                    'material_id',
                    'material_name',
                    'model_name_original',
                ],
            ],
        ]);
    }

    #[Test]
    public function it_handles_multiple_uploads_with_different_materials(): void
    {
        $customer = Customer::factory()->create();
        $material1 = $this->createMaterial(['wp_id' => 101]);
        $material2 = $this->createMaterial(['wp_id' => 102]);

        Sanctum::actingAs($this->apiUser);

        $uploads = [
            'item_1' => [
                '3dp_options' => [
                    'material_id' => $material1->id,
                    'material_name' => '101. Material One',
                    'model_name_original' => 'model1.stl',
                    'scale' => 1,
                ],
            ],
            'item_2' => [
                '3dp_options' => [
                    'material_id' => $material2->id,
                    'material_name' => '102. Material Two',
                    'model_name_original' => 'model2.stl',
                    'scale' => 1,
                ],
            ],
        ];

        $response = $this->postJson(
            route('api.api.models.get-custom-model-attributes', ['customerId' => $customer->wp_id]),
            ['uploads' => json_encode($uploads)]
        );

        $response->assertOk();
        $response->assertJsonCount(2);
    }

    #[Test]
    public function it_handles_uploads_without_3dp_options(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $uploads = [
            'item_1' => [
                'some_other_data' => 'value',
            ],
            'item_2' => [
                '3dp_options' => [
                    'material_id' => 1,
                    'material_name' => '1. Test',
                    'model_name_original' => 'test.stl',
                    'scale' => 1,
                ],
            ],
        ];

        $response = $this->postJson(
            route('api.api.models.get-custom-model-attributes', ['customerId' => $customer->wp_id]),
            ['uploads' => json_encode($uploads)]
        );

        $response->assertOk();
        // Only items with 3dp_options should be in response
        $response->assertJsonCount(1);
    }

    // ========================================
    // getCustomModelName() tests
    // ========================================

    #[Test]
    public function it_returns_custom_model_name(): void
    {
        $customer = Customer::factory()->create();
        $material = $this->createMaterial(['wp_id' => 200]);

        $model = Model::factory()->create([
            'customer_id' => $customer->id,
            'material_id' => $material->id,
            'name' => 'my_model.stl',
            'model_name' => 'My Custom Model Name',
            'model_scale' => 1,
        ]);
        $model->materials()->attach($material->id);

        Sanctum::actingAs($this->apiUser);

        $upload = [
            '3dp_options' => [
                'material_name' => '200. Test Material',
                'model_name_original' => 'my_model.stl',
                'scale' => 1,
            ],
        ];

        $response = $this->postJson(
            route('api.api.models.get-custom-model-name', ['customerId' => $customer->wp_id]),
            ['upload' => json_encode($upload)]
        );

        $response->assertOk();
        $response->assertJsonPath('model_name', 'My Custom Model Name');
    }

    #[Test]
    public function it_returns_null_model_name_when_not_found(): void
    {
        $customer = Customer::factory()->create();

        Sanctum::actingAs($this->apiUser);

        $upload = [
            '3dp_options' => [
                'material_name' => '999. Nonexistent',
                'model_name_original' => 'nonexistent.stl',
                'scale' => 1,
            ],
        ];

        $response = $this->postJson(
            route('api.api.models.get-custom-model-name', ['customerId' => $customer->wp_id]),
            ['upload' => json_encode($upload)]
        );

        $response->assertOk();
        $response->assertJsonPath('model_name', null);
    }

    // ========================================
    // destroy() tests
    // ========================================

    #[Test]
    public function it_deletes_model(): void
    {
        $customer = Customer::factory()->create();
        $model = $this->createModel(['customer_id' => $customer->id]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.models.delete', [
            'customerId' => $customer->wp_id,
            'model' => $model->id,
        ]));

        $response->assertNoContent();
        $this->assertSoftDeleted('models', ['id' => $model->id]);
    }

    #[Test]
    public function it_returns_404_when_deleting_model_of_different_customer(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        $model = $this->createModel(['customer_id' => $customer2->id]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson(route('api.api.models.delete', [
            'customerId' => $customer1->wp_id,
            'model' => $model->id,
        ]));

        $response->assertNotFound();
    }

    // ========================================
    // Authentication tests
    // ========================================

    #[Test]
    public function it_returns_401_when_not_authenticated(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson(route('api.api.models.show-customer-wp-models', [
            'customerId' => $customer->wp_id,
        ]));

        $response->assertUnauthorized();
    }
}
