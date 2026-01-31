<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\TrackingStatus;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackingStatusTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $trackingStatus = new TrackingStatus;
        $fillable = $trackingStatus->getFillable();

        $this->assertContains('model_type', $fillable);
        $this->assertContains('model_id', $fillable);
        $this->assertContains('object_id', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('sub_status', $fillable);
        $this->assertContains('status_details', $fillable);
        $this->assertContains('status_date', $fillable);
        $this->assertContains('location', $fillable);
        $this->assertContains('meta_data', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $trackingStatus = new TrackingStatus;
        $casts = $trackingStatus->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['status_date']);
    }

    #[Test]
    public function it_casts_location_as_array_object(): void
    {
        $trackingStatus = new TrackingStatus;
        $casts = $trackingStatus->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['location']);
    }

    #[Test]
    public function it_casts_meta_data_as_array_object(): void
    {
        $trackingStatus = new TrackingStatus;
        $casts = $trackingStatus->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['meta_data']);
    }

    #[Test]
    public function it_has_morph_to_shipment(): void
    {
        $trackingStatus = new TrackingStatus;

        $this->assertInstanceOf(MorphTo::class, $trackingStatus->shipment());
    }
}
