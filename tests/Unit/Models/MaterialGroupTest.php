<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Material;
use App\Models\MaterialGroup;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaterialGroupTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $materialGroup = new MaterialGroup;
        $fillable = $materialGroup->getFillable();

        $this->assertContains('name', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $materialGroup = new MaterialGroup;
        $casts = $materialGroup->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_has_many_materials(): void
    {
        $materialGroup = new MaterialGroup;

        $this->assertInstanceOf(HasMany::class, $materialGroup->materials());
        $this->assertEquals(Material::class, $materialGroup->materials()->getRelated()::class);
    }
}
