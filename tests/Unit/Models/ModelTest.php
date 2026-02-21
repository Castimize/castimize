<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use App\Models\ShopListingModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $model = new Model;
        $fillable = $model->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('material_id', $fillable);
        $this->assertContains('model_name', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('file_name', $fillable);
        $this->assertContains('model_volume_cc', $fillable);
        $this->assertContains('model_parts', $fillable);
        $this->assertContains('categories', $fillable);
        $this->assertContains('meta_data', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $model = new Model;
        $casts = $model->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_categories_as_json(): void
    {
        $model = new Model;
        $casts = $model->getCasts();

        $this->assertEquals('json', $casts['categories']);
    }

    #[Test]
    public function it_casts_meta_data_as_array(): void
    {
        $model = new Model;
        $casts = $model->getCasts();

        $this->assertEquals('array', $casts['meta_data']);
    }

    #[Test]
    public function it_belongs_to_customer(): void
    {
        $model = new Model;

        $this->assertInstanceOf(BelongsTo::class, $model->customer());
        $this->assertEquals(Customer::class, $model->customer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_material(): void
    {
        $model = new Model;

        $this->assertInstanceOf(BelongsTo::class, $model->material());
        $this->assertEquals(Material::class, $model->material()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_many_materials(): void
    {
        $model = new Model;

        $this->assertInstanceOf(BelongsToMany::class, $model->materials());
        $this->assertEquals(Material::class, $model->materials()->getRelated()::class);
    }

    #[Test]
    public function it_has_one_shop_listing_model(): void
    {
        $model = new Model;

        $this->assertInstanceOf(HasOne::class, $model->shopListingModel());
        $this->assertEquals(ShopListingModel::class, $model->shopListingModel()->getRelated()::class);
    }
}
