<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Etsy;

use App\Enums\Etsy\EtsyListingStatesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EtsyListingStatesEnumTest extends TestCase
{
    #[Test]
    public function it_has_active_case(): void
    {
        $this->assertEquals('active', EtsyListingStatesEnum::Active->value);
        $this->assertEquals('Active', EtsyListingStatesEnum::Active->name);
    }

    #[Test]
    public function it_has_inactive_case(): void
    {
        $this->assertEquals('inactive', EtsyListingStatesEnum::Inactive->value);
    }

    #[Test]
    public function it_has_sold_out_case(): void
    {
        $this->assertEquals('sold_out', EtsyListingStatesEnum::SoldOut->value);
    }

    #[Test]
    public function it_has_draft_case(): void
    {
        $this->assertEquals('draft', EtsyListingStatesEnum::Draft->value);
    }

    #[Test]
    public function it_has_expired_case(): void
    {
        $this->assertEquals('expired', EtsyListingStatesEnum::Expired->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = EtsyListingStatesEnum::cases();

        $this->assertCount(5, $cases);
        $this->assertContains(EtsyListingStatesEnum::Active, $cases);
        $this->assertContains(EtsyListingStatesEnum::Inactive, $cases);
        $this->assertContains(EtsyListingStatesEnum::SoldOut, $cases);
        $this->assertContains(EtsyListingStatesEnum::Draft, $cases);
        $this->assertContains(EtsyListingStatesEnum::Expired, $cases);
    }
}
