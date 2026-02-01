<?php

namespace Tests\Unit\Services\Admin;

use App\Services\Admin\AddressTransliterationService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddressTransliterationServiceTest extends TestCase
{
    private AddressTransliterationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AddressTransliterationService;
    }

    #[Test]
    public function it_transliterates_japanese_kanji_name(): void
    {
        $result = $this->service->transliterateString('田中太郎');

        $this->assertNotEquals('田中太郎', $result);
        $this->assertTrue($this->service->isAscii($result));
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_transliterates_hiragana(): void
    {
        $result = $this->service->transliterateString('たなかたろう');

        $this->assertNotEquals('たなかたろう', $result);
        $this->assertTrue($this->service->isAscii($result));
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_transliterates_katakana(): void
    {
        $result = $this->service->transliterateString('タナカタロウ');

        $this->assertNotEquals('タナカタロウ', $result);
        $this->assertTrue($this->service->isAscii($result));
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_leaves_ascii_strings_unchanged(): void
    {
        $input = 'John Doe';
        $result = $this->service->transliterateString($input);

        $this->assertEquals($input, $result);
    }

    #[Test]
    public function it_handles_empty_strings(): void
    {
        $result = $this->service->transliterateString('');

        $this->assertEquals('', $result);
    }

    #[Test]
    public function it_handles_mixed_japanese_and_ascii(): void
    {
        $result = $this->service->transliterateString('田中 John');

        $this->assertTrue($this->service->isAscii($result));
        $this->assertStringContainsString('John', $result);
    }

    #[Test]
    public function it_detects_japanese_characters(): void
    {
        $this->assertTrue($this->service->containsJapanese('田中'));
        $this->assertTrue($this->service->containsJapanese('たなか'));
        $this->assertTrue($this->service->containsJapanese('タナカ'));
        $this->assertFalse($this->service->containsJapanese('John Doe'));
        $this->assertFalse($this->service->containsJapanese('123 Main St'));
    }

    #[Test]
    public function it_transliterates_full_japanese_address(): void
    {
        $address = [
            'name' => '田中太郎',
            'company' => '株式会社テスト',
            'address_line1' => '東京都渋谷区',
            'address_line2' => '1-2-3',
            'city' => '渋谷',
            'state' => '東京都',
            'postal_code' => '150-0001',
            'country' => 'JP',
            'email' => 'tanaka@example.com',
            'phone' => '+81-90-1234-5678',
        ];

        $result = $this->service->transliterateAddress($address);

        $this->assertTrue($this->service->isAscii($result['name']));
        $this->assertTrue($this->service->isAscii($result['company']));
        $this->assertTrue($this->service->isAscii($result['address_line1']));
        $this->assertTrue($this->service->isAscii($result['city']));
        $this->assertTrue($this->service->isAscii($result['state']));

        $this->assertEquals('150-0001', $result['postal_code']);
        $this->assertEquals('JP', $result['country']);
        $this->assertEquals('tanaka@example.com', $result['email']);
        $this->assertEquals('+81-90-1234-5678', $result['phone']);
    }

    #[Test]
    public function it_does_not_modify_already_ascii_address(): void
    {
        $address = [
            'name' => 'John Doe',
            'company' => 'Test Company',
            'address_line1' => '123 Main Street',
            'address_line2' => 'Apt 4B',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'email' => 'john@example.com',
            'phone' => '+1-555-123-4567',
        ];

        $result = $this->service->transliterateAddress($address);

        $this->assertEquals($address, $result);
    }

    #[Test]
    public function it_handles_address_with_missing_optional_fields(): void
    {
        $address = [
            'name' => '田中太郎',
            'address_line1' => '東京都渋谷区',
            'city' => '渋谷',
            'postal_code' => '150-0001',
            'country' => 'JP',
        ];

        $result = $this->service->transliterateAddress($address);

        $this->assertTrue($this->service->isAscii($result['name']));
        $this->assertTrue($this->service->isAscii($result['address_line1']));
        $this->assertTrue($this->service->isAscii($result['city']));
        $this->assertArrayNotHasKey('company', $result);
        $this->assertArrayNotHasKey('state', $result);
    }
}
