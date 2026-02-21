<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shops;

use App\Enums\Shops\ShopOwnerShopsEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShopOwnerShopsEnumTest extends TestCase
{
    #[Test]
    public function it_has_etsy_case(): void
    {
        $this->assertEquals('etsy', ShopOwnerShopsEnum::Etsy->value);
        $this->assertEquals('Etsy', ShopOwnerShopsEnum::Etsy->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShopOwnerShopsEnum::cases();

        $this->assertCount(1, $cases);
        $this->assertContains(ShopOwnerShopsEnum::Etsy, $cases);
    }

    #[Test]
    public function it_returns_list_array(): void
    {
        $list = ShopOwnerShopsEnum::getList();

        $this->assertIsArray($list);
        $this->assertCount(1, $list);
        $this->assertArrayHasKey('etsy', $list);
        $this->assertEquals('Etsy', $list['etsy']);
    }
}
