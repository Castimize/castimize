<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $user = new User;
        $fillable = $user->getFillable();

        $this->assertContains('wp_id', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('first_name', $fillable);
        $this->assertContains('last_name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    #[Test]
    public function it_has_hidden_attributes(): void
    {
        $user = new User;
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $user = new User;
        $casts = $user->getCasts();

        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    #[Test]
    public function it_casts_password_as_hashed(): void
    {
        $user = new User;
        $casts = $user->getCasts();

        $this->assertEquals('hashed', $casts['password']);
    }

    #[Test]
    public function it_has_one_customer(): void
    {
        $user = new User;

        $this->assertInstanceOf(HasOne::class, $user->customer());
        $this->assertEquals(Customer::class, $user->customer()->getRelated()::class);
    }

    #[Test]
    public function it_has_one_manufacturer(): void
    {
        $user = new User;

        $this->assertInstanceOf(HasOne::class, $user->manufacturer());
        $this->assertEquals(Manufacturer::class, $user->manufacturer()->getRelated()::class);
    }

    #[Test]
    public function it_returns_identifiable_name(): void
    {
        $user = new User;
        $user->first_name = 'John';
        $user->last_name = 'Doe';

        $this->assertEquals('John Doe', $user->identifiableName());
    }

    #[Test]
    public function it_checks_if_user_is_backend_user(): void
    {
        $user = new User;

        $this->assertIsBool($user->isBackendUser());
    }
}
