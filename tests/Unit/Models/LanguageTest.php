<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Language;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $language = new Language;
        $fillable = $language->getFillable();

        $this->assertContains('iso', $fillable);
        $this->assertContains('locale', $fillable);
        $this->assertContains('local_name', $fillable);
        $this->assertContains('en_name', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $language = new Language;
        $casts = $language->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }
}
