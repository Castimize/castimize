<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Woocommerce;

use App\Enums\Woocommerce\WcOrderStatesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WcOrderStatesEnumTest extends TestCase
{
    #[Test]
    public function it_has_pending_case(): void
    {
        $this->assertEquals('pending', WcOrderStatesEnum::Pending->value);
        $this->assertEquals('Pending', WcOrderStatesEnum::Pending->name);
    }

    #[Test]
    public function it_has_processing_case(): void
    {
        $this->assertEquals('processing', WcOrderStatesEnum::Processing->value);
    }

    #[Test]
    public function it_has_on_hold_case(): void
    {
        $this->assertEquals('on-hold', WcOrderStatesEnum::OnHold->value);
    }

    #[Test]
    public function it_has_completed_case(): void
    {
        $this->assertEquals('completed', WcOrderStatesEnum::Completed->value);
    }

    #[Test]
    public function it_has_cancelled_case(): void
    {
        $this->assertEquals('cancelled', WcOrderStatesEnum::Cancelled->value);
    }

    #[Test]
    public function it_has_refunded_case(): void
    {
        $this->assertEquals('refunded', WcOrderStatesEnum::Refunded->value);
    }

    #[Test]
    public function it_has_failed_case(): void
    {
        $this->assertEquals('failed', WcOrderStatesEnum::Failed->value);
    }

    #[Test]
    public function it_has_trash_case(): void
    {
        $this->assertEquals('trash', WcOrderStatesEnum::Trash->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = WcOrderStatesEnum::cases();

        $this->assertCount(8, $cases);
        $this->assertContains(WcOrderStatesEnum::Pending, $cases);
        $this->assertContains(WcOrderStatesEnum::Processing, $cases);
        $this->assertContains(WcOrderStatesEnum::OnHold, $cases);
        $this->assertContains(WcOrderStatesEnum::Completed, $cases);
        $this->assertContains(WcOrderStatesEnum::Cancelled, $cases);
        $this->assertContains(WcOrderStatesEnum::Refunded, $cases);
        $this->assertContains(WcOrderStatesEnum::Failed, $cases);
        $this->assertContains(WcOrderStatesEnum::Trash, $cases);
    }
}
