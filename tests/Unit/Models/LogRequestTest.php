<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\LogRequest;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogRequestTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $logRequest = new LogRequest;
        $fillable = $logRequest->getFillable();

        $this->assertContains('type', $fillable);
        $this->assertContains('path_info', $fillable);
        $this->assertContains('request_uri', $fillable);
        $this->assertContains('method', $fillable);
        $this->assertContains('remote_address', $fillable);
        $this->assertContains('user_agent', $fillable);
        $this->assertContains('server', $fillable);
        $this->assertContains('headers', $fillable);
        $this->assertContains('request', $fillable);
        $this->assertContains('response', $fillable);
        $this->assertContains('http_code', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $logRequest = new LogRequest;
        $casts = $logRequest->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_server_as_array_object(): void
    {
        $logRequest = new LogRequest;
        $casts = $logRequest->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['server']);
    }

    #[Test]
    public function it_casts_headers_as_array_object(): void
    {
        $logRequest = new LogRequest;
        $casts = $logRequest->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['headers']);
    }

    #[Test]
    public function it_casts_request_as_array_object(): void
    {
        $logRequest = new LogRequest;
        $casts = $logRequest->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['request']);
    }

    #[Test]
    public function it_casts_response_as_json(): void
    {
        $logRequest = new LogRequest;
        $casts = $logRequest->getCasts();

        $this->assertEquals('json', $casts['response']);
    }
}
