<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ModelHasAddress;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelHasAddressTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $modelHasAddress = new ModelHasAddress;
        $fillable = $modelHasAddress->getFillable();

        $this->assertContains('address_id', $fillable);
        $this->assertContains('model_type', $fillable);
        $this->assertContains('model_id', $fillable);
        $this->assertContains('default_billing', $fillable);
        $this->assertContains('default_shipping', $fillable);
        $this->assertContains('company', $fillable);
        $this->assertContains('contact_name', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('email', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $modelHasAddress = new ModelHasAddress;
        $casts = $modelHasAddress->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_is_not_incrementing(): void
    {
        $modelHasAddress = new ModelHasAddress;

        $this->assertFalse($modelHasAddress->incrementing);
    }
}
